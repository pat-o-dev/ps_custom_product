<?php
if (!defined('_PS_VERSION_')) { exit; }

class AdminPsCustomProductController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap  = true;
        $this->meta_title = $this->l('Custom Product');
    }

    public function initContent()
    {
        parent::initContent();

        // Redirige vers le premier sous-onglet
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPsCustomProductProducts')
        );
    }
}