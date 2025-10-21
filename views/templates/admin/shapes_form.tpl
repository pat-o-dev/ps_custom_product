{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
 <form method="post">
  {foreach $shapes as $code => $shape}
    <div class="panel">
      <div class="panel-heading">
        <strong>{$shape.label|escape}</strong> <small>({$code})</small>
      </div>

      <div class="row" style="padding:0 15px 10px;">
        <div class="col-md-2">
          <input type="hidden" name="PCP_SHAPES[{$code}][enabled]" value="0">
          <label><input type="checkbox" name="PCP_SHAPES[{$code}][enabled]" value="1" {if $shape.enabled}checked{/if}> {l s='Activer' mod='ps_custom_product'}</label>
        </div>
        <div class="col-md-2">
          <label>{l s='Facteur' mod='ps_custom_product'}</label>
          <input class="form-control" type="text" name="PCP_SHAPES[{$code}][factor]" value="{$shape.factor}">
        </div>
      </div>

      <fieldset style="margin:0 15px 10px;">
        <legend>{l s='Dimensions' mod='ps_custom_product'}</legend>
        {foreach $shape.fields as $fname => $f}
          <div class="row" style="margin-bottom:6px;">
            <div class="col-md-2"><strong>{$fname}</strong></div>
            <div class="col-md-2">
              <input class="form-control" type="text" name="PCP_SHAPES[{$code}][fields][{$fname}][min]" value="{$f.min}">
              <small>{l s='min' mod='ps_custom_product'}</small>
            </div>
            <div class="col-md-2">
              <input class="form-control" type="text" name="PCP_SHAPES[{$code}][fields][{$fname}][max]" value="{$f.max}">
              <small>{l s='max' mod='ps_custom_product'}</small>
            </div>
            <div class="col-md-2">
              <input class="form-control" type="text" name="PCP_SHAPES[{$code}][fields][{$fname}][step]" value="{$f.step}">
              <small>{l s='pas' mod='ps_custom_product'}</small>
            </div>
            <div class="col-md-2">
              <input class="form-control" type="text" name="PCP_SHAPES[{$code}][fields][{$fname}][default]" value="{$f.default}">
              <small>{l s='default' mod='ps_custom_product'}</small>
            </div>
          </div>
        {/foreach}
      </fieldset>

      <fieldset style="margin:0 15px 15px;">
        <legend>{l s='Aire (m²)' mod='ps_custom_product'}</legend>
        <div class="row">
          <div class="col-md-3">
            <label>{l s='Min' mod='ps_custom_product'}</label>
            <input class="form-control" type="text" name="PCP_SHAPES[{$code}][air][min_m2]" value="{$shape.air.min_m2}">
          </div>
          <div class="col-md-3">
            <label>{l s='Max' mod='ps_custom_product'}</label>
            <input class="form-control" type="text" name="PCP_SHAPES[{$code}][air][max_m2]" value="{$shape.air.max_m2}">
          </div>
        </div>
      </fieldset>
    </div>
  {/foreach}

  <div class="clearfix" style="margin-bottom:10px;"></div>

  <button type="submit" name="submit_pcp_shapes" class="btn btn-primary">
    <i class="icon-save"></i> {l s='Enregistrer' mod='ps_custom_product'}
  </button>
  <button type="submit" name="reset_pcp_shapes" class="btn btn-warning"
          onclick="return confirm('{l s='Réinitialiser les valeurs par défaut ?' mod='ps_custom_product'}');">
    <i class="icon-refresh"></i> {l s='Réinitialiser' mod='ps_custom_product'}
  </button>
</form>