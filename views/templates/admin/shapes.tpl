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
 <div class="panel">
  <div class="panel-heading">{l s='Configuration des formes' mod='ps_custom_product'}</div>

  <form method="post">
    <div class="form-group">
      <label for="PCP_SHAPES_JSON">{l s='JSON des formes' mod='ps_custom_product'}</label>
      <textarea name="PCP_SHAPES_JSON" id="PCP_SHAPES_JSON" rows="25" class="form-control">
{$json_value|escape:'htmlall':'UTF-8'}
      </textarea>
      <p class="help-block">
        {l s='Modifiez le JSON puis cliquez sur Enregistrer. Chaque forme contient ses contraintes.' mod='ps_custom_product'}
      </p>
    </div>

    <div class="text-right">
      <button type="submit" name="submit_pcp_shapes_json" class="btn btn-primary">
        <i class="icon-save"></i> {l s='Enregistrer' mod='ps_custom_product'}
      </button>
      <a href="{$reset_url}" class="btn btn-warning" 
         onclick="return confirm('{l s='Réinitialiser les formes par défaut ?' mod='ps_custom_product'}');">
        <i class="icon-refresh"></i> {l s='Réinitialiser' mod='ps_custom_product'}
      </a>
    </div>
  </form>
</div>