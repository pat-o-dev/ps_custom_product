<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2025 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_Custom_ProductGetCustomProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        header('Content-Type: application/json; charset=utf-8');

        try {
            // Get data from fetch
            $input = json_decode(Tools::file_get_contents('php://input'), true);

            $action = (string) $input['action'] ?? 'quote';
            $id_product = (int) $input['id_product'] ?? 0;
            $color = (int) $input['color'] ?? 0;
            $materialCode = (string) $input['material'] ?? 0;
            $shape = (string) $input['shape'] ?? null;
            $quantity = (int) $input['quantity'] ?? 1;
            $dimensions = $input['dimensions'] ?? [];

            // Load JSON setting
            $shapes = json_decode(Configuration::get('PCP_SHAPES'), true) ?: [];
            $materials = json_decode(Configuration::get('PCP_MATERIALS'), true) ?: [];
            $productsSetting = json_decode(Configuration::get('PCP_PRODUCT_SETTINGS'), true) ?: [];

            // Init 
            $shapeFactor = (float)($shapes[$shape]['factor'] ?? 1.0);
            $material = $materials[$materialCode] ?? null;
            $pricePerM2 = (float)($material['price_m2'] ?? 0);
            $materialWeightM2 = (float)($material['weight_m2'] ?? 0);
            $fabricCoeff = (float)($material['coeff'] ?? 1.0);

            $productSetting = $productsSetting[$id_product] ?? [];
            $basePrice = (float)($productSetting['base_unit_price'] ?? 0);
            $margin = (float)($productSetting['rate_margin'] ?? 1.0);
            $id_attribute_group = (float)($productSetting['id_attribute_group'] ?? 0);
            $productTare = (float) ($productSetting['tare_weight'] ?? 1.0);

            // Compute surface
            $surfaceM2 = $this->getSurface($shape, $dimensions);
            if ($surfaceM2 === 0) {
                exit(json_encode([
                    'success' => false,
                    'error'   => $this->trans('Forme inconnue ou dimensions invalides.', [], 'Modules.ps_custom_product.Front'),
                ]));
            }

            // Compute price
            $weightKgTotal = max(0, $productTare + $surfaceM2 * $materialWeightM2);
            $priceHt = ($basePrice + ($surfaceM2 * $pricePerM2 * $fabricCoeff * $shapeFactor)) * $margin;
            $taxRate = 0;
            $product = new Product($id_product);
            if (Validate::isLoadedObject($product)) {
                $taxRate = (float)Tax::getProductTaxRate($id_product);
            }
            $priceTtc = $priceHt * (1 + $taxRate / 100);
            $formatter = new PriceFormatter();
            $displayPrice = $formatter->format($priceTtc);

            // First step Quote
            if ($action === 'quote') {
                exit(json_encode([
                    'success' => true,
                    'id_product' => $id_product,
                    'shape' => $shape,
                    'material' => $material,
                    'price_ht' => round($priceHt, 2),
                    'price_ttc' => round($priceTtc, 2),
                    'display_price' => $displayPrice,
                    'surface_m2' => round($surfaceM2, 3),
                ]));
            }
            // Need to Add in cart
            if ($action === 'add') {
                $id_shop = (int)$this->context->shop->id;

                // Generate Attribute Label
                $label = $shape;
                foreach ($dimensions as $k => $v) {
                    $label .= ' ' . strtoupper($k) . '=' . (float)$v;
                }
                if ($materialCode) {
                    $label .= ' ' . strtoupper($materialCode);
                }

                if ($color) {
                    $label .= ' ' . Tools::strtoupper(trim((string) $color));
                }

                // Get or Create Attribute
                $id_attribute = $this->getAttribute($id_attribute_group, $label, $id_shop);
                // Get or Create Product Attribute
                $id_product_attribute = $this->getProductAttribute($id_product, $id_attribute, $weightKgTotal, $priceHt, $id_shop);

                if (!$id_product_attribute) {
                    throw new Exception($this->trans('Impossible d’ajouter au panier.', [], 'Modules.ps_custom_product.Front'));
                }

                $addToCart = $this->addToCart($id_product, $id_product_attribute, $quantity);
                if (!$addToCart) {
                    throw new Exception($this->trans('Impossible d’ajouter au panier.', [], 'Modules.ps_custom_product.Front'));
                }

                exit(json_encode([
                    'success' => true,
                    'action'  => 'add',
                    'id_product' => $id_product,
                    'id_product_attribute' => $id_product_attribute,
                    'quantity' => $quantity,

                ]));
            }
        } catch (Exception $e) {
            http_response_code(400);
            exit(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }

    public function addToCart($id_product, $id_product_attribute, $quantity = 1)
    {
        // Init cart if not exist
        if (!$this->context->cart->id) {
            $this->context->cart->add();
            $this->context->cookie->id_cart = (int)$this->context->cart->id;
        }
        // Add Product Attribute to Cart
        $add = (bool) $this->context->cart->updateQty(
            (int)($quantity),
            (int)$id_product,
            (int)$id_product_attribute,
            false,
            'up',
            0,
            null,
            true
        );

        return $add;
    }

    public function getProductAttribute($id_product, $id_attribute, $weightKgTotal, $priceHt, $id_shop = 1)
    {
        // Exist ? Get
        $id_product_attribute = (int)Db::getInstance()->getValue(
            'SELECT pa.id_product_attribute
            FROM ' . _DB_PREFIX_ . 'product_attribute pa
            JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac
            ON pac.id_product_attribute = pa.id_product_attribute
            WHERE pa.id_product=' . (int)$id_product . '
            GROUP BY pa.id_product_attribute
            HAVING GROUP_CONCAT(pac.id_attribute ORDER BY pac.id_attribute SEPARATOR ",") = "' . pSQL((string)$id_attribute) . '"'
        );

        // Create
        if (!$id_product_attribute) {
            // Product_attribute
            Db::getInstance()->insert('product_attribute', [
                'id_product'     => (int)$id_product,
                'reference'      => '',        // #TODO SKU
                'price'          => 0,         // impact HT = 0 use SpecificPrice
                'weight'         => (float)$weightKgTotal,
                'default_on'     => null,
                'available_date' => null,
            ], true);

            $id_product_attribute = (int)Db::getInstance()->Insert_ID();

            // Product_attribute_shop
            Db::getInstance()->insert('product_attribute_shop', [
                'id_product'           => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute,
                'id_shop'              => (int)$id_shop,
                'price'                => 0,
                'weight'               => (float)$weightKgTotal,
                'default_on'           => null,
                'available_date'       => null,
            ], true);
            // Product_attribute_combination
            Db::getInstance()->insert('product_attribute_combination', [
                'id_attribute'         => (int)$id_attribute,
                'id_product_attribute' => (int)$id_product_attribute,
            ]);

            // Stock_available
            Db::getInstance()->insert('stock_available', [
                'id_product'           => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute,
                'id_shop'              => (int)$id_shop,
                'quantity'             => 9999,
                'out_of_stock'         => 1,
            ]);
            // Specific_price
            Db::getInstance()->delete(
                'specific_price',
                'id_product=' . (int)$id_product . ' AND id_product_attribute=' . (int)$id_product_attribute
            );

            $sp = new SpecificPrice();
            $sp->id_product           = (int)$id_product;
            $sp->id_product_attribute = (int)$id_product_attribute;
            $sp->id_shop              = (int)$id_shop;
            $sp->id_currency          = 0;
            $sp->id_country           = 0;
            $sp->id_group             = 0;
            $sp->id_customer          = 0;
            $sp->price                = Tools::ps_round($priceHt, 3); // PRIX HT FINAL
            $sp->from_quantity        = 1;
            $sp->reduction            = 0;
            $sp->reduction_type       = 'amount';
            $sp->from                 = '0000-00-00 00:00:00';
            $sp->to                   = '0000-00-00 00:00:00';
            $sp->add();

            StockAvailable::setProductOutOfStock((int)$id_product, 1);
            Product::flushPriceCache();
        }

        return $id_product_attribute;
    }

    public function getAttribute($id_attribute_group, $label, $id_shop = 1)
    {
        // Exist ??
        $id_attribute = (int)Db::getInstance()->getValue(
            'SELECT id_attribute FROM ' . _DB_PREFIX_ . 'attribute_lang 
            WHERE name = "' . pSQL($label) . '"'
        );
        if ($id_attribute > 0) {
            return $id_attribute;
        }
        // Attribute
        $result = Db::getInstance()->insert('attribute', [
            'id_attribute_group' => $id_attribute_group,
            'color'              => '',
            'position'           => 0,
        ]);

        if (!$result) {
            throw new Exception($this->trans('Impossible de créer la valeur d’attribut.', [], 'Modules.ps_custom_product.Front'));
        }
        $id_attribute = (int)Db::getInstance()->Insert_ID();

        // Attribute_lang
        foreach (Language::getLanguages(false) as $lang) {
            Db::getInstance()->insert('attribute_lang', [
                'id_attribute' => (int)$id_attribute,
                'id_lang'      => (int)$lang['id_lang'],
                'name'         => pSQL($label),
            ]);
        }

        // Attribute_shop
        Db::getInstance()->insert('attribute_shop', [
            'id_attribute' => (int)$id_attribute,
            'id_shop'      => (int)$id_shop,
        ]);

        return $id_attribute;
    }

    public function getSurface($shape, $dimensions = [])
    {
        // Get dimension
        $ab = (float)($dimensions['ab'] ?? 0);
        $bc = (float)($dimensions['bc'] ?? 0);
        $ca = (float)($dimensions['ca'] ?? 0);

        // Compute by shape
        switch ($shape) {
            case 'RECT':
                $surfaceM2 = max(0, ($ab / 100) * ($bc / 100));
                break;
            case 'SQR':
                $surfaceM2 = max(0, ($ab / 100) * ($ab / 100));
                break;
            case 'TRI':
                // Heron
                $a = $ab / 100;
                $b = $bc / 100;
                $c = $ca / 100;
                $s = ($a + $b + $c) / 2;
                $surfaceM2 = sqrt(max(0, $s * ($s - $a) * ($s - $b) * ($s - $c)));
                $surfaceM2 = max(0, $surfaceM2);
                break;
            default:
                $surfaceM2 = 0; //error
                break;
        }
        return $surfaceM2;
    }
}
