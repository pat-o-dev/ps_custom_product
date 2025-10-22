<?php

/**
 * 2007-2025 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Patrick Genitrini
 * @copyright 2007-2025 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

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
            Logger::addLog('addCustomProduct payload: ' . print_r($input, true));

            $idProduct = isset($input['id_product']) ? (int) $input['id_product'] : 0;
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
            Logger::addLog('addCustomProduct ERROR: ' . $e->getMessage(), 3);
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
            exit;
        }
    }
}
