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
