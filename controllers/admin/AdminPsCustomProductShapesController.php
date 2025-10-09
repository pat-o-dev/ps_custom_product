<?php
if (!defined('_PS_VERSION_')) { exit; }

class AdminPsCustomProductShapesController extends ModuleAdminController
{
    const CONFIG_KEY = 'PCP_SHAPES';

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->meta_title = $this->l('Formes & dimensions');
    }

    /** Valeurs par défaut */
    private function getDefault(): array
    {
        return [
            'RECT' => [
                'label' => 'Rectangle',
                'enabled' => true,
                'factor' => 1.0,
                'fields' => [
                    'ab'  => ['min'=>10, 'max'=>200, 'step'=>1, 'default' => 50],
                    'bc' => ['min'=>10, 'max'=>200, 'step'=>1, 'default' => 50],
                ],
                'air' => ['min_m2'=>0.1, 'max_m2'=>1.9],
            ],
            'SQR' => [
                'label' => 'Carré',
                'enabled' => true,
                'factor' => 1.0,
                'fields' => [
                    'ab' => ['min'=>10, 'max'=>150, 'step'=>1, 'default' => 50],
                ],
                'air' => ['min_m2'=>0.1, 'max_m2'=>2.0],
            ],
            'TRI' => [
                'label' => 'Triangle',
                'enabled' => false,
                'factor' => 0.5,
                'fields' => [
                    'ab' => ['min'=>10, 'max'=>200, 'step'=>1, 'default' => 50],
                    'bc' => ['min'=>10, 'max'=>200, 'step'=>1, 'default' => 50],
                    'ca' => ['min'=>10, 'max'=>200, 'step'=>1, 'default' => 50],
                ],
                'air' => ['min_m2'=>0.1, 'max_m2'=>1.9],
            ],
        ];
    }

    /** Parse nombre : "1,2" → 1.2 */
    private function nf($v): float
    {
        if (is_string($v)) $v = str_replace(',', '.', $v);
        if (!is_numeric($v)) return 0.0;
        return round((float)$v, 3);
    }

    public function initContent()
    {
        parent::initContent();

        $stored = Configuration::get(self::CONFIG_KEY);
        $shapes = $stored ? json_decode($stored, true) : $this->getDefault();

        $this->context->smarty->assign([
            'shapes'    => $shapes,
            'reset_url' => self::$currentIndex.'&token='.$this->token.'&reset_pcp_shapes=1',
        ]);

        $this->content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/shapes_form.tpl'
        );
        $this->context->smarty->assign('content', $this->content);
    }

    public function postProcess()
    {
        parent::postProcess();

        // reset
        if (Tools::isSubmit('reset_pcp_shapes')) {
            Configuration::updateValue(
                self::CONFIG_KEY,
                json_encode($this->getDefault(), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
            );
            $this->confirmations[] = $this->l('Configuration réinitialisée.');
            return;
        }

        //  sauvegarde
        if (Tools::isSubmit('submit_pcp_shapes')) {
            $input = Tools::getValue('PCP_SHAPES');
            if (!is_array($input) || empty($input)) {
                $this->errors[] = $this->l('Aucune donnée reçue.');
                return;
            }

            $stored   = Configuration::get(self::CONFIG_KEY);
            $existing = $stored ? json_decode($stored, true) : $this->getDefault();
            if (!is_array($existing)) { $existing = []; }

            $out = [];
            foreach ($input as $code => $shape) {
                $oldLabel = $existing[$code]['label'] ?? $code;
                $label    = isset($shape['label']) ? trim($shape['label']) : $oldLabel;

                $out[$code] = [
                    'label'   => $label,
                    'enabled' => !empty($shape['enabled']),
                    'factor'  => $this->nf($shape['factor'] ?? 1),
                    'fields'  => [],
                    'air'     => [
                        'min_m2' => $this->nf($shape['air']['min_m2'] ?? 0),
                        'max_m2' => $this->nf($shape['air']['max_m2'] ?? 0),
                    ],
                ];

                if (isset($shape['fields']) && is_array($shape['fields'])) {
                    foreach ($shape['fields'] as $fname => $f) {
                        $out[$code]['fields'][$fname] = [
                            'min'  => $this->nf($f['min']  ?? 0),
                            'max'  => $this->nf($f['max']  ?? 0),
                            'step' => $this->nf($f['step'] ?? 1),
                            'default' => $this->nf($f['default'] ?? 0),
                        ];
                    }
                } elseif (!empty($existing[$code]['fields']) && is_array($existing[$code]['fields'])) {
                    $out[$code]['fields'] = $existing[$code]['fields'];
                }

                if ($out[$code]['air']['min_m2'] > $out[$code]['air']['max_m2']) {
                    $this->errors[] = sprintf($this->l('%s : Aire min > Aire max'), $code);
                }
            }

            if (!empty($this->errors)) { return; }

            Configuration::updateValue(
                self::CONFIG_KEY,
                json_encode($out, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
            );

            $this->confirmations[] = $this->l('Configuration enregistrée.');
        }
    }
}