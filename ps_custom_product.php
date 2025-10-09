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

class Ps_custom_product extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'ps_custom_product';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'PatrickGenitrini';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Custom Product', [], 'Modules.ps_custom_product.Admin');
        $this->description = $this->trans('Add custom product on your website', [], 'Modules.ps_custom_product.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('displayAfterProductThumbs')
            && $this->installTab()
            && Configuration::updateValue('PCP_CONFIG_PRODUCTS', '')
            && Configuration::updateValue('PCP_PRODUCT_SETTINGS', '')
            && Configuration::updateValue('PCP_MATERIALS', '')
            && Configuration::updateValue('PCP_SHAPES', '');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTab()
            && Configuration::deleteByName('PCP_CONFIG_PRODUCTS')
            && Configuration::deleteByName('PCP_PRODUCT_SETTINGS')
            && Configuration::deleteByName('PCP_MATERIALS')
            && Configuration::deleteByName('PCP_SHAPES');
    }
    
    private function installTab()
    {
        $parent = new Tab();
        $parent->active = 1;
        $parent->class_name = 'AdminPsCustomProduct';
        $parent->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $parent->name[$lang['id_lang']] = 'PS Custom Product';
        }
        $parent->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');
        $parent->module = $this->name;
        $parent->add();
        $children = [
            'AdminPsCustomProductProducts'  => 'Produits configurables',
            'AdminPsCustomProductShapes'    => 'Formes & dimensions',
            'AdminPsCustomProductMaterials' => 'Matières & Couleurs',
        ];
        foreach ($children as $class => $label) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $class;
            $tab->id_parent = (int)$parent->id;
            $tab->module = $this->name;
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $label;
            }
            $tab->add();
        }

        return true;

    }

    private function uninstallTab()
    {
        $classes = [
            'AdminPsCustomProduct',
            'AdminPsCustomProductProducts',
            'AdminPsCustomProductShapes',
            'AdminPsCustomProductMaterials',
        ];

        foreach ($classes as $class) {
            $id = (int)Tab::getIdFromClassName($class);
            if ($id) {
                $tab = new Tab($id);
                $tab->delete();
            }
        }

        return true;
    }

    public function hookHeader($params)
    {
        if ($this->context->controller && $this->context->controller->php_self === 'product') {
            // JS
           /* $this->context->controller->registerJavascript(
                'pscp-front-js',
                'modules/'.$this->name.'/views/js/ps_custom_product.js',
                [
                    'position'   => 'bottom',
                    'priority'   => 150,
                    'attributes' => 'defer',
                ]
            );*/

            // (Optionnel) CSS
            $this->context->controller->registerStylesheet(
                'pscp-front-css',
                'modules/'.$this->name.'/views/css/ps_custom_product.css',
                [
                    'media'    => 'all',
                    'priority' => 150,
                ]
            );
        }
    }

    public function hookDisplayAfterProductThumbs($params) {
        $id_product = $params['product']['id'];
        if (!$id_product) return;
        
        $ids_product = array_map('intval',explode(',', (string) Configuration::get('PCP_CONFIG_PRODUCTS')));
        if(in_array($id_product, $ids_product)) {
            $id_lang = (int) $this->context->language->id;
            
            $shapes = json_decode(Configuration::get('PCP_SHAPES', '{}'), true);
            $materials = json_decode(Configuration::get('PCP_MATERIALS', '{}'), true);
            $colors = [];
            foreach($materials as &$material) {
                $material['colors'] = $colors[$material['color_group_id']] ?? $this->getColorsByGroup($material['color_group_id'], $id_lang);   
            }

            $this->context->smarty->assign([
                'id_product' => $id_product,
                'shapes' => $shapes,
                'materials' => $materials,
            ]);
            return $this->context->smarty->fetch('module:ps_custom_product/views/templates/hook/customize_product.tpl');
        }
        return null; 
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPsCustomProduct')
        );
    }

    public function getColorsByGroup($id_group, $id_lang)
    {
        $sql = new DbQuery();
        $sql->select('a.id_attribute, al.name, a.color');
        $sql->from('attribute', 'a');
        $sql->innerJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang);
        $sql->where('a.id_attribute_group = ' . (int) $id_group);
        $sql->orderBy('al.name ASC');

        $colors = Db::getInstance()->executeS($sql);

        return $colors ?: [];
    }
}