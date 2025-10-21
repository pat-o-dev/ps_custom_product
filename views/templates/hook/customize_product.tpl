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
 {if $shapes}
<div id="pcp-tabs" class="mt-4">
  <!-- Onglets -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" id="pcp-config-tab" data-toggle="tab" href="#pcp-main-content" role="tab">
        {l s='Personnalisation' mod='ps_custom_product'}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="pcp-preview-tab" data-toggle="tab" href="#pcp-preview" role="tab">
        {l s='Aperçu' mod='ps_custom_product'}
      </a>
    </li>
  </ul>
  <!-- Contenu -->
  <div class="tab-content border border-top-0 rounded-bottom bg-white">
    <div class="tab-pane fade show active in p-2" id="pcp-main-content" role="tabpanel" aria-labelledby="pcp-config-tab">
      {include file="module:ps_custom_product/views/templates/hook/_partials/shapes.tpl"}
      {include file="module:ps_custom_product/views/templates/hook/_partials/materials.tpl"}
        <button id="get-custom-product" class="btn btn-primary" type="button" onclick="pcpQuote()">
        <i class="material-icons"></i>
        {l s='Préparer votre produit' mod='ps_custom_product'}
      </button>
    </div>

    <div class="tab-pane fade p-2" id="pcp-preview" role="tabpanel">
      {include file="module:ps_custom_product/views/templates/hook/_partials/actions.tpl"}
  </div>
</div>


<div id="pcp-root"
     data-id-product="{$id_product}"
     data-custom-product-url="{$link->getModuleLink('ps_custom_product','getCustomProduct')}">
</div>
<script>
document.body.classList.add('custom-product-page');
</script>
{else}
{/if}

