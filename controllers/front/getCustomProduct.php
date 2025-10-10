<?php
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class Ps_Custom_ProductGetCustomProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        header('Content-Type: application/json; charset=utf-8');

        try {
            // get data from fetch
            $input = json_decode(Tools::file_get_contents('php://input'), true);

            $action = (string)($input['action'] ?? 'quote');
            $id_product = (int)($input['id_product'] ?? 0);
            $color =(int)($input['color'] ?? 0);#dont need for the moment
            $materialCode =(string)($input['material'] ?? 0);
            $shape =(string)($input['shape'] ?? null);

            $dimensions = $input['dimensions'] ?? [];
            
            // Load JSON setting
            $shapes = json_decode(Configuration::get('PCP_SHAPES'), true) ?: [];
            $materials = json_decode(Configuration::get('PCP_MATERIALS'), true) ?: [];
            $productsSetting = json_decode(Configuration::get('PCP_PRODUCT_SETTINGS'), true) ?: [];

            $shapeFactor = (float)($shapes[$shape]['factor'] ?? 1.0);
            $material = $materials[$materialCode] ?? null;
            $pricePerM2 = (float)($material['price_m2'] ?? 0);
            $materialWeightM2 = (float)($material['weight_m2'] ?? 0);
            $fabricCoeff = (float)($material['coeff'] ?? 1.0);

            $productSetting = $productsSetting[$id_product] ?? [];
            $basePrice = (float)($productSetting['base_unit_price'] ?? 0);
            $margin = (float)($productSetting['rate_margin'] ?? 1.0);
            $productTare = (float) ($productSetting['tare_weight'] ?? 1.0);
      
            // compute surface
            $surfaceM2 = $this->getSurface($shape,$dimensions);
            if ($surfaceM2 === 0) {
                die(json_encode([
                    'success' => false,
                    'error'   => $this->trans('Forme inconnue ou dimensions invalides.', [], 'Modules.ps_custom_product.Front'),
                ]));
            }

            // price
            #TODO refactoriser
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
            
            if ($action === 'quote') {
                die(json_encode([
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

            if ($action === 'add') {
                $id_attribute_group = 6; // Groupe cible (ex: "Configurations personnalisées")
                $id_shop = 1;#TODO get current shop

                $label = $shape;
                foreach ($dimensions as $k => $v) {
                    $label .= ' ' . strtoupper($k) . '=' . (float)$v;
                }
                if ($materialCode) {
                    $label .= ' ' . strtoupper($materialCode);
                }

                if ($color) {
                    $label .= ' ' . strtoupper($color);
                }

                $id_attribute = $this->getAttribute($id_attribute_group, $label );
           
                $id_product_attribute = $this->getProductAttribute($id_product, $id_attribute, $weightKgTotal);
            
                Db::getInstance()->delete(
                    'specific_price',
                    'id_product='.(int)$id_product.' AND id_product_attribute='.(int)$id_product_attribute
                );
   
                $sp = new SpecificPrice();
                $sp->id_product           = (int)$id_product;
                $sp->id_product_attribute = (int)$id_product_attribute;
                $sp->id_shop              = (int)$id_shop;
                $sp->id_currency          = 0; // toutes devises
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

                if (!$id_product_attribute) {
                    throw new Exception($this->trans('Impossible d’ajouter au panier.', [], 'Modules.ps_custom_product.Front'));
                }
                
                if (!$this->context->cart->id) {
                    $this->context->cart->add();
                    $this->context->cookie->id_cart = (int)$this->context->cart->id;
                }
                $ok = $this->context->cart->updateQty(
                    (int)($input['quantity'] ?? 1),
                    (int)$id_product,
                    (int)$id_product_attribute,
                    false,
                    'up',
                    0,
                    null,
                    true
                );
                if (!$ok) {
                    throw new Exception($this->trans('Impossible d’ajouter au panier.', [], 'Modules.ps_custom_product.Front'));
                }

                die(json_encode([
                    'success' => true,
                    'action'  => 'add',
                    'id_product' => $id_product,
                    'id_product_attribute' => $id_product_attribute,

                ]));

            }
        } catch (Exception $e) {
            http_response_code(400);
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }

    public function getProductAttribute($id_product, $id_attribute, $weightKgTotal, $id_shop = 1)
    {
        $id_product_attribute = (int)Db::getInstance()->getValue(
            'SELECT pa.id_product_attribute
            FROM '._DB_PREFIX_.'product_attribute pa
            JOIN '._DB_PREFIX_.'product_attribute_combination pac
            ON pac.id_product_attribute = pa.id_product_attribute
            WHERE pa.id_product='.(int)$id_product.'
            GROUP BY pa.id_product_attribute
            HAVING GROUP_CONCAT(pac.id_attribute ORDER BY pac.id_attribute SEPARATOR ",") = "'.pSQL((string)$id_attribute).'"'
        );


        if (!$id_product_attribute) {
            // product_attribute
            Db::getInstance()->insert('product_attribute', [
                'id_product'     => (int)$id_product,
                'reference'      => '',        // #TODO SKU
                'price'          => 0,         // impact HT = 0 use SpecificPrice
                'weight'         => (float)$weightKgTotal,
                'default_on'     => null,
                'available_date' => null,
            ], true);

            $id_product_attribute = (int)Db::getInstance()->Insert_ID();

            // product_attribute_shop
            Db::getInstance()->insert('product_attribute_shop', [
                'id_product'           => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute,
                'id_shop'              => (int)$id_shop,
                'price'                => 0,
                'weight'               => (float)$weightKgTotal,
                'default_on'           => null,
                'available_date'       => null,
            ], true);

            Db::getInstance()->insert('product_attribute_combination', [
                'id_attribute'         => (int)$id_attribute,
                'id_product_attribute' => (int)$id_product_attribute,
            ]);

            // stock_available
            Db::getInstance()->insert('stock_available', [
                'id_product'           => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute,
                'id_shop'              => (int)$id_shop,
                'quantity'             => 9999,
                'out_of_stock'         => 1,
            ]);
        }

        return $id_product_attribute;
    }

    public function getAttribute($id_attribute_group, $label, $id_shop = 1)
    {
        $id_attribute = (int)Db::getInstance()->getValue(
            'SELECT id_attribute FROM '._DB_PREFIX_.'attribute_lang 
            WHERE name = "' . pSQL($label) . '"'
        );
        if($id_attribute > 0) {
            return $id_attribute;
        }       
       
        $result = Db::getInstance()->insert('attribute', [
            'id_attribute_group' => $id_attribute_group,
            'color'              => '',
            'position'           => 0,
        ]);

        if (!$result) {
            throw new Exception($this->trans('Impossible de créer la valeur d’attribut.', [], 'Modules.ps_custom_product.Front'));
        }
        $id_attribute = (int)Db::getInstance()->Insert_ID();

        // attribute_lang
        foreach (Language::getLanguages(false) as $lang) {
            Db::getInstance()->insert('attribute_lang', [
                'id_attribute' => (int)$id_attribute,
                'id_lang'      => (int)$lang['id_lang'],
                'name'         => pSQL($label),
            ]);
        }

        // attribute_shop
        Db::getInstance()->insert('attribute_shop', [
            'id_attribute' => (int)$id_attribute,
            'id_shop'      => (int)$id_shop,
        ]); 

        return $id_attribute;
    }

    public function getSurface($shape, $dimensions = []) 
    {
        $ab = (float)($dimensions['ab'] ?? 0);
        $bc =(float)($dimensions['bc'] ?? 0);
        $ca =(float)($dimensions['ca'] ?? 0);

        switch($shape) {
            case 'RECT':
                $surfaceM2 = max(0, ($ab / 100) * ($bc / 100));
                break;
            case 'SQR':
                $surfaceM2 = max(0, ($ab / 100) * ($ab / 100));
                break;
            case 'TRI':
                // Formule Héron
                $a = $ab / 100;
                $b = $bc / 100;
                $c = $ca / 100;
                $s = ($a + $b + $c) / 2;
                $surfaceM2 = sqrt(max(0, $s * ($s - $a) * ($s - $b) * ($s - $c)));
                $surfaceM2 = max(0, $surfaceM2);
                break;
            default:
                $surfaceM2 = 0;//error
                break;
        }
        return $surfaceM2;
    }
}