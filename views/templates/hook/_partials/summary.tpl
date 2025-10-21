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
<div id="pcp-summary" class="border rounded p-0 mt-0">
<div id="pcp-bloc-param" class="mb-3">
    <div class="d-flex gap-3 flex-wrap">
    <div><strong>{l s='Forme' mod='ps_custom_product'} :</strong> <span id="pcp-param-shape">—</span></div>
    <div><strong>{l s='Matière' mod='ps_custom_product'} :</strong> <span id="pcp-param-material">—</span></div>
    <div><strong>{l s='Couleur' mod='ps_custom_product'} :</strong> <span id="pcp-param-color">—</span></div>
    </div>
    <ul id="pcp-bloc-param-dimension" class="list-unstyled mb-2">
    <li class="text-muted">{l s='Dimensions' mod='ps_custom_product'} :</li>
    <li id="pcp-param-dim-list"><em>{l s='À définir' mod='ps_custom_product'}</em></li>
    </ul>
</div>

<div id="pcp-bloc-render" style="max-width:350px;margin:auto;">
    <svg></svg>
</div>

<div class="pcp-bloc-price text-center mt-2 mb-2">
    <span class="text-muted d-block">{l s='Prix unitaire' mod='ps_custom_product'}</span>
    <strong id="price-result" class="fs-4 text-primary"></strong>
</div>