<?php
if (!defined('_PS_VERSION_')) { exit; }

class AdminPsCustomProductProductsController extends ModuleAdminController
{
    const CFG_KEY_IDS       = 'PCP_CONFIG_PRODUCTS';   // CSV d'IDs
    const CFG_KEY_SETTINGS  = 'PCP_PRODUCT_SETTINGS';  // JSON { id: {base_unit_price, tare_weight, rate_margin} }

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->meta_title = $this->l('Produits');
    }

    protected function getConfiguredProductIds(): array
    {
        $csv = (string)Configuration::get(self::CFG_KEY_IDS);
        if (!$csv) { return []; }
        $arr = array_map('intval', array_filter(array_map('trim', explode(',', $csv))));
        return array_values(array_unique($arr));
    }

    protected function saveConfiguredProductIds(array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        Configuration::updateValue(self::CFG_KEY_IDS, implode(',', $ids));
    }

    protected function getSettings(): array
    {
        $json = (string)Configuration::get(self::CFG_KEY_SETTINGS);
        $arr  = $json ? json_decode($json, true) : [];
        if (!is_array($arr)) $arr = [];
        foreach ($arr as $pid => &$s) {
            // defaults
            if (!isset($s['base_unit_price'])) $s['base_unit_price'] = 0.0;
            if (!isset($s['tare_weight'])) $s['tare_weight'] = 0.0;
            if (!isset($s['rate_margin'])) $s['rate_margin'] = 1.0;
            if (!isset($s['id_attribute_group'])) $s['id_attribute_group'] = 0;
        }
        unset($s);

        return $arr;
    }

    protected function saveSettings(array $settings): void
    {
        Configuration::updateValue(self::CFG_KEY_SETTINGS, json_encode($settings, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    /* ---------- Utils ---------- */

    protected function nf($v): float
    {
        if (is_string($v)) { $v = str_replace(',', '.', $v); }
        if (!is_numeric($v)) { return 0.0; }
        return round((float)$v, 3);
    }

    protected function getProductName(int $idProduct): string
    {
        $idLang = (int)$this->context->language->id;
        $row = Db::getInstance()->getRow(
            (new DbQuery())
                ->select('pl.name')
                ->from('product_lang', 'pl')
                ->where('pl.id_product='.(int)$idProduct.' AND pl.id_lang='.(int)$idLang.' AND pl.id_shop='.(int)$this->context->shop->id)
        );
        return $row['name'] ?? $this->l('(nom indisponible)');
    }

    /* ---------- Views ---------- */

    public function initContent()
    {
        parent::initContent();
        $this->content = $this->renderForm() . $this->renderList();
        $this->context->smarty->assign('content', $this->content);
    }

    public function renderForm()
    {
        $fields_form = [[
            'form' => [
                'legend' => ['title' => $this->l('Ajouter un produit configurable')],
                'input'  => [[
                    'type'  => 'text',
                    'label' => $this->l('ID produit'),
                    'name'  => 'PCP_ADD_PRODUCT_ID',
                    'desc'  => $this->l('Saisissez l’ID d’un produit existant et actif.'),
                ]],
                'submit' => ['title' => $this->l('Ajouter'), 'name' => 'submit_add_pcp_product'],
            ],
        ]];

        $helper = new HelperForm();
        $helper->module       = $this->module;
        $helper->show_toolbar = false;
        $helper->identifier   = 'id_configuration';
        $helper->table        = 'configuration';
        $helper->default_form_language = (int)$this->context->language->id;
        $helper->allow_employee_form_lang = $helper->default_form_language;
        $helper->currentIndex = self::$currentIndex;
        $helper->token        = $this->token;
        $helper->submit_action= 'submit_add_pcp_product';
        $helper->fields_value = ['PCP_ADD_PRODUCT_ID' => ''];

        return $helper->generateForm($fields_form);
    }

    public function renderList(): string
    {
        $ids = $this->getConfiguredProductIds();
        $settings = $this->getSettings();

        $fmt = function($x){ return str_replace('.', ',', (string)$x); };

        $products = [];
        foreach ($ids as $pid) {
            $s = $settings[$pid] ?? ['base_unit_price'=>0, 'tare_weight'=>0, 'rate_margin'=>1.0];
            $products[] = [
                'id' => $pid,
                'name' => $this->getProductName($pid),
                'base_unit_price'    => $fmt($s['base_unit_price']),
                'tare_weight'        => $fmt($s['tare_weight']),
                'rate_margin'        => $fmt($s['rate_margin']),
                'id_attribute_group' => $fmt($s['id_attribute_group']),

                'remove_url'       => self::$currentIndex.'&token='.$this->token.'&remove_pcp_product='.$pid,
            ];
        }

        $groups = Db::getInstance()->executeS('
            SELECT ag.id_attribute_group, agl.name 
            FROM '._DB_PREFIX_.'attribute_group ag 
            JOIN '._DB_PREFIX_.'attribute_group_lang agl 
              ON (agl.id_attribute_group = ag.id_attribute_group AND agl.id_lang = '.(int)$this->context->language->id.')
            ORDER BY agl.name ASC
        ');

        $this->context->smarty->assign([
            'products'    => $products,
            'attribute_groups' => $groups,
            'save_action' => 'submit_save_product_settings',
            'reset_url'   => self::$currentIndex.'&token='.$this->token.'&reset_pcp_product_settings=1',
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/product_list.tpl'
        );
    }

    /* ---------- Actions ---------- */

    public function postProcess()
    {
        parent::postProcess();

        // Ajouter un produit
        if (Tools::isSubmit('submit_add_pcp_product')) {
            $pid = (int)Tools::getValue('PCP_ADD_PRODUCT_ID');
            if ($pid <= 0) { $this->errors[] = $this->l('ID produit invalide.'); return; }

            $p = new Product($pid, false, (int)$this->context->language->id, (int)$this->context->shop->id);
            if (!Validate::isLoadedObject($p)) { $this->errors[] = $this->l('Produit introuvable.'); return; }

            $ids = $this->getConfiguredProductIds();
            if (!in_array($pid, $ids, true)) {
                $ids[] = $pid;
                $this->saveConfiguredProductIds($ids);

                $settings = $this->getSettings();
                if (!isset($settings[$pid])) {
                    $settings[$pid] = ['base_unit_price'=>0, 'tare_weight'=>0, 'rate_margin'=>1.0, 'id_attribute_group' => 0];
                    $this->saveSettings($settings);
                }

                $this->confirmations[] = $this->l('Produit ajouté à la liste.');
            } else {
                $this->warnings[] = $this->l('Ce produit est déjà dans la liste.');
            }
        }

        // Supprimer un produit (et ses réglages)
        if (Tools::getIsset('remove_pcp_product')) {
            $pid = (int)Tools::getValue('remove_pcp_product');

            $ids = $this->getConfiguredProductIds();
            $ids = array_values(array_diff($ids, [$pid]));
            $this->saveConfiguredProductIds($ids);

            $settings = $this->getSettings();
            if (isset($settings[$pid])) { unset($settings[$pid]); $this->saveSettings($settings); }

            $this->confirmations[] = $this->l('Produit retiré.');
        }

        // Reset des réglages produits (conserve la liste d’IDs)
        if (Tools::isSubmit('reset_pcp_product_settings') || Tools::getIsset('reset_pcp_product_settings')) {
            $ids = $this->getConfiguredProductIds();
            $settings = [];
            foreach ($ids as $pid) {
                $settings[$pid] = ['base_unit_price'=>0, 'tare_weight'=>0, 'rate_margin'=>1.0, 'id_attribute_group' => 0];
            }
            $this->saveSettings($settings);
            $this->confirmations[] = $this->l('Réglages produits réinitialisés.');
            return;
        }

        // Sauvegarder les réglages inline (merge-safe)
        if (Tools::isSubmit('submit_save_product_settings')) {
            $payload = Tools::getValue('SETTINGS'); // [id_product => [fields]]
            if (!is_array($payload)) { $payload = []; }

            $ids = $this->getConfiguredProductIds();
            $settings = $this->getSettings(); // existants (déjà migrés)

            foreach ($ids as $pid) {
                $row = $payload[$pid] ?? [];
                $baseUnit           = $settings[$pid]['base_unit_price']   ?? 0;
                $tare               = $settings[$pid]['tare_weight']       ?? 0;
                $rate               = $settings[$pid]['rate_margin']       ?? 1.0;
                $id_attribute_group = $settings[$pid]['id_attribute_group']?? 0;

                $settings[$pid] = [
                    'base_unit_price'   => array_key_exists('base_unit_price',   $row) ? $this->nf($row['base_unit_price'])   : $baseUnit,
                    'tare_weight'       => array_key_exists('tare_weight',       $row) ? $this->nf($row['tare_weight'])       : $tare,
                    'rate_margin'       => array_key_exists('rate_margin',       $row) ? $this->nf($row['rate_margin'])       : $rate,
                    'id_attribute_group'=> array_key_exists('id_attribute_group',$row) ? $this->nf($row['id_attribute_group']): $id_attribute_group,
                ];
            }

            $this->saveSettings($settings);
            $this->confirmations[] = $this->l('Paramètres produits enregistrés.');
        }
    }
}