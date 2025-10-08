<?php
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;


class Ps_Custom_ProductGetCustomProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        // On s'assure d'un retour JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Récupération du JSON envoyé par fetch()
            $input = json_decode(Tools::file_get_contents('php://input'), true);

            $idProduct = 20;//(int)($input['id_product'] ?? 20);
            $ab = (float)($input['ab'] ?? 0);
            $bc =(float)($input['bc'] ?? 0);
            $fabricCoeff = 1;//(float)($input['fabric_coeff'] ?? 1.0);

            $pricePerM2 = 12.5; // €
            $surfaceM2 = max(0.01, ($ab / 100) * ($bc / 100));
            $priceHt = $surfaceM2 * $pricePerM2 * $fabricCoeff;
            $taxRate = 0;
            $product = new Product($idProduct);
            if (Validate::isLoadedObject($product)) {
                $taxRate = (float)Tax::getProductTaxRate($idProduct);
            }
            $priceTtc = $priceHt * (1 + $taxRate / 100);
            $formatter = new PriceFormatter();
            $displayPrice = $formatter->format($priceTtc);
            
            die(json_encode([
                'success' => true,
                'price_ht' => round($priceHt, 2),
                'price_ttc' => round($priceTtc, 2),
                'display_price' => $displayPrice,
                'surface_m2' => round($surfaceM2, 3),
            ]));
        } catch (Exception $e) {
            http_response_code(400);
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }
}