<?php
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class Ps_Custom_ProductAddCustomProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $inputRaw = Tools::file_get_contents('php://input');
            if (!$inputRaw) {
                throw new Exception('Aucune donnée reçue.');
            }

            $input = json_decode($inputRaw, true);
            if (!is_array($input)) {
                throw new Exception('JSON invalide.');
            }

            // Debug temporaire : tout journaliser pour voir les valeurs
            Logger::addLog('addCustomProduct payload: '.print_r($input, true));

            $idProduct = (int)($input['id_product'] ?? 0);
            if (!$idProduct) {
                throw new Exception('id_product manquant.');
            }

            // Pour test, on ne fait rien de plus — juste retour JSON
            $result = [
                'success' => true,
                'message' => 'Contrôleur appelé avec succès',
                'id_product' => $idProduct,
                'payload' => $input,
            ];

            echo json_encode($result);
            exit;

        } catch (Exception $e) {
            // On logue aussi l’erreur dans Prestashop
            Logger::addLog('addCustomProduct ERROR: '.$e->getMessage(), 3);
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
            exit;
        }
    }
}