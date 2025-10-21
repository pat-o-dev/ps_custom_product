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
<div class="add">

</div>
<div id="add-to-cart-container" class="mt-0" style="display:none;">
  {include file="module:ps_custom_product/views/templates/hook/_partials/summary.tpl"}

    <div class="pcp-addline">
      <div class="pcp-qty">
        <input
          type="number"
          id="pcp-quantity"
          name="qty"
          class="form-control text-center"
          min="1"
          value="1"
          aria-label="{l s='Quantité' mod='ps_custom_product'}"
        />
        <div class="pcp-qty-buttons">
          <button type="button" onclick="pcpChangeQty(1)">
            <i class="material-icons">keyboard_arrow_up</i>
          </button>
          <button type="button" onclick="pcpChangeQty(-1)">
            <i class="material-icons">keyboard_arrow_down</i>
          </button>
        </div>
      </div>
      <button id="add-custom-product"
              class="btn btn-primary text-uppercase px-4"
              onclick="pcpAddToCart()">
        <i class="material-icons align-middle"></i>
        {l s='Ajouter au panier' mod='ps_custom_product'}
      </button>
    </div>
  </div>
</div>
