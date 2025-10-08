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
            && $this->registerHook('displayAfterProductThumbs')
            && $this->installTab();
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTab();
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
            'AdminPsCustomProductMaterials'    => 'Matières & Couleurs',
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

    public function hookDisplayAfterProductThumbs($params) {
        $id_product = $params['product']['id'];
        if($id_product == 20) {
            return $this->fetch('module:ps_custom_product/views/templates/hook/customize_product.tpl');
        }
        return null; 
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPsCustomProduct')
        );
    }
}