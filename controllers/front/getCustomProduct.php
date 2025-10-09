<?php
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class Ps_Custom_ProductGetCustomProductModuleFrontController extends ModuleFrontController
{
    
    
    public function initContent()
    {
        parent::initContent();
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Récupération du JSON envoyé par fetch()
            $input = json_decode(Tools::file_get_contents('php://input'), true);

            $action = (string)($input['action'] ?? 'quote');
            $id_product = (int)($input['id_product'] ?? 0);
            $color =(int)($input['color'] ?? 0);#dont need for the moment
            $materialCode =(string)($input['material'] ?? 0);
            $shape =(string)($input['shape'] ?? null);

            $dimensions = $input['dimensions'] ?? [];
            
            // Chargement des JSON
            $shapes = json_decode(Configuration::get('PCP_SHAPES'), true) ?: [];
            $materials = json_decode(Configuration::get('PCP_MATERIALS'), true) ?: [];
            $productsSetting = json_decode(Configuration::get('PCP_PRODUCT_SETTINGS'), true) ?: [];

            $shapeFactor = (float)($shapes[$shape]['factor'] ?? 1.0);
            $material = $materials[$materialCode] ?? null;
            $pricePerM2 = (float)($material['price_m2'] ?? 12.5);
            $fabricCoeff = (float)($material['coeff'] ?? 1.0);

            $productSetting = $productsSetting[$id_product] ?? [];
            $basePrice = (float)($productSetting['base_unit_price'] ?? 0);
            $margin = (float)($productSetting['rate_margin'] ?? 1.0);
      
            $surfaceM2 = $this->getSurface($shape,$dimensions);
            if ($surfaceM2 === 0) {
                die(json_encode([
                    'success' => false,
                    'error'   => $this->trans('Forme inconnue ou dimensions invalides.', [], 'Modules.ps_custom_product.Front'),
                ]));
            }

            #a refactoriser
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
                // assure un panier
                if (!$this->context->cart->id) {
                    $this->context->cart->add();
                    $this->context->cookie->id_cart = (int)$this->context->cart->id;
                }

                // MVP: pas encore de combinaison → ajout direct
                $ok = $this->context->cart->updateQty(
                    (int)($input['quantity'] ?? 1),
                    $id_product,
                    0,      // id_product_attribute (0 pour l’instant)
                    false,  // id_customization
                    'up'
                );

                if (!$ok) {
                    throw new Exception($this->trans('Impossible d’ajouter au panier.', [], 'Modules.ps_custom_product.Front'));
                }

                die(json_encode([
                    'success' => true,
                    'action'  => 'add',
                    'id_product' => $id_product,
                    'id_product_attribute' => 0
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

    public function getSurface($shape, $dimensions = []) {
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