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

class AdminPsCustomProductMaterialsController extends ModuleAdminController
{
    public const CONFIG_KEY = 'PCP_MATERIALS';

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->meta_title = $this->trans('Matières & couleurs', [], 'Modules.ps_custom_product.Admin');
    }

    private function getDefault(): array
    {
        return [
            'COT' => ['label' => 'Coton','enabled' => true,'coeff' => 1.0,'price_m2' => 5.0,'weight_m2' => 0.18,'color_group_id' => 0,'color_default_coeff' => 1.0,'position' => 10],
            'LIN' => ['label' => 'Lin','enabled' => true,'coeff' => 1.2,'price_m2' => 7.5,'weight_m2' => 0.22,'color_group_id' => 0,'color_default_coeff' => 1.0,'position' => 20],
            'MIC' => ['label' => 'Microfibre','enabled' => true,'coeff' => 0.9,'price_m2' => 3.8,'weight_m2' => 0.14,'color_group_id' => 0,'color_default_coeff' => 1.0,'position' => 30],
        ];
    }

    private function nf($v): float
    {
        if (is_string($v)) {
            $v = str_replace(',', '.', $v);
        }
        if (!is_numeric($v)) {
            return 0.0;
        }
        return round((float)$v, 3);
    }

    public function initContent()
    {
        parent::initContent();

        $stored = Configuration::get(self::CONFIG_KEY);
        $materials = $stored ? json_decode($stored, true) : $this->getDefault();
        if (!is_array($materials)) {
            $materials = [];
        }

        // convert en json valide en cas d'ancienne version de données #TODO other group
        foreach ($materials as &$m) {
            $m['enabled']            = isset($m['enabled']) ? (bool)$m['enabled'] : true;
            if (!isset($m['coeff'])) {
                $m['coeff'] = 1.0;
            }
            if (!isset($m['price_m2'])) {
                $m['price_m2'] = 0.0;
            }
            if (!isset($m['weight_m2'])) {
                $m['weight_m2'] = 0.0;
            }
            if (!isset($m['color_group_id'])) {
                $m['color_group_id'] = 0;
            }
            if (!isset($m['color_default_coeff'])) {
                $m['color_default_coeff'] = 1.0;
            }
            if (!isset($m['position'])) {
                $m['position'] = 0;
            }
        }
        unset($m);

        uasort($materials, function ($a, $b) {
            return ($a['position'] ?? 0) <=> ($b['position'] ?? 0);
        });

        $groups = Db::getInstance()->executeS('
            SELECT ag.id_attribute_group, agl.name 
            FROM '._DB_PREFIX_.'attribute_group ag 
            JOIN '._DB_PREFIX_.'attribute_group_lang agl 
              ON (agl.id_attribute_group = ag.id_attribute_group AND agl.id_lang = ' . (int) $this->context->language->id . ')
            ORDER BY agl.name ASC
        ');

        $this->context->smarty->assign([
            'materials' => $materials,
            'attribute_groups' => $groups,
            'reset_url' => self::$currentIndex.'&token=' . $this->token . '&reset_pcp_materials=1',
        ]);

        $this->content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/materials_form.tpl'
        );
        $this->context->smarty->assign('content', $this->content);
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('reset_pcp_materials')) {
            Configuration::updateValue(
                self::CONFIG_KEY,
                json_encode($this->getDefault(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );
            $this->confirmations[] = $this->trans('Configuration réinitialisée.', [], 'Modules.ps_custom_product.Admin');
            return;
        }

        if ($del = Tools::getValue('delete_material')) {
            $stored = Configuration::get(self::CONFIG_KEY);
            $materials = $stored ? json_decode($stored, true) : [];
            unset($materials[$del]);
            Configuration::updateValue(self::CONFIG_KEY, json_encode($materials, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->confirmations[] = $this->trans('Matière %s supprimée.', ['%s' => $del], 'Modules.Ps_custom_product.Admin');
            return;
        }

        if (Tools::isSubmit('submit_pcp_materials')) {
            // merge safe
            $stored = Configuration::get(self::CONFIG_KEY);
            $existing = $stored ? json_decode($stored, true) : $this->getDefault();
            if (!is_array($existing)) {
                $existing = [];
            }

            $data = Tools::getValue('MATERIALS');
            if (!is_array($data)) {
                $data = [];
            }

            foreach ($data as $key => $m) {
                if ($key === 'new') {
                    $code = Tools::strtoupper(trim($m['code'] ?? ''));
                    if ($code === '') {
                        continue;
                    }
                    $existing[$code] = [
                        'label' => trim($m['label'] ?? ''),
                        'enabled' => !empty($m['enabled']),
                        'coeff' => $this->nf($m['coeff'] ?? 1),
                        'price_m2' => $this->nf($m['price_m2'] ?? 0),
                        'weight_m2' => $this->nf($m['weight_m2'] ?? 0),
                        'color_group_id' => (int)($m['color_group_id'] ?? 0),
                        'color_default_coeff' => $this->nf($m['color_default_coeff'] ?? 1),
                        'position' => (int)($m['position'] ?? 0),
                    ];
                } else {
                    if (!isset($existing[$key])) {
                        continue;
                    }
                    $existing[$key]['label'] = trim($m['label'] ?? $existing[$key]['label']);
                    $existing[$key]['enabled'] = !empty($m['enabled']);
                    $existing[$key]['coeff'] = $this->nf($m['coeff'] ?? $existing[$key]['coeff']);
                    $existing[$key]['price_m2'] = $this->nf($m['price_m2'] ?? ($existing[$key]['price_m2'] ?? 0));
                    $existing[$key]['weight_m2'] = $this->nf($m['weight_m2'] ?? $existing[$key]['weight_m2']);
                    $existing[$key]['color_group_id'] = (int)($m['color_group_id'] ?? $existing[$key]['color_group_id']);
                    $existing[$key]['color_default_coeff'] = $this->nf($m['color_default_coeff'] ?? ($existing[$key]['color_default_coeff'] ?? 1));
                    $existing[$key]['position'] = (int)($m['position'] ?? $existing[$key]['position']);
                }
            }

            uasort($existing, fn ($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));

            Configuration::updateValue(self::CONFIG_KEY, json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->confirmations[] = $this->trans('Matières enregistrées.', [], 'Modules.ps_custom_product.Admin');
        }
    }
}
