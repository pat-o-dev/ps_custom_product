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
 <div id="pcp-materials-list" class="group-input mt-2 mb-0">
  <h3>3 - {l s='Choisissez la matière' mod='ps_custom_product'}</h3>

  <div id="pcp-materials-buttons" class="btn-group">
    {foreach from=$materials key=code item=material name=materials}
      <label for="pcp-material-input-{$code}" 
             class="btn btn-outline-primary m-1 {if $smarty.foreach.materials.first}active{/if}">
        <input 
          type="radio"
          id="pcp-material-input-{$code}"
          class="pcp-material-input"
          name="PCP_MATERIAL"
          value="{$code}"
          style="display:none"
          {if $smarty.foreach.materials.first}checked{/if}
          onchange="pcpShowMaterial(this.value)"
        />
        {$material.label}
      </label>
    {/foreach}
  </div>
</div>

<div id="pcp-materials-fields" class="mt-0">
  <h3>4 - {l s='Choisissez la couleur' mod='ps_custom_product'}</h3>

  {foreach from=$materials key=code item=material name=materialsColor}
    <div class="pcp-material-fields border rounded px-0 mb-2"
         data-material="{$code}"
         style="{if $smarty.foreach.materialsColor.first}display:block;{else}display:none;{/if}">

      {if isset($material.colors) && $material.colors|@count > 0}
        <ul class="pcp-color-grid">
          {foreach from=$material.colors item=color name=colorLoop}
            {assign var=idc value="pcp-color-`$code`-`$color.id_attribute`"}
            <li class="pcp-color">
              <input
                type="radio"
                class="pcp-color-input"
                name="PCP_COLOR"
                id="{$idc}"
                value="{$color.id_attribute}"
                data-material="{$code}"
                {if $smarty.foreach.materialsColor.first && $smarty.foreach.colorLoop.first}checked{/if}
              />
              <label for="{$idc}" class="pcp-color-label" title="{$color.name}">
                <span class="pcp-swatch" style="background-color:{$color.color|escape:'htmlall':'UTF-8'};"></span>
                <span class="pcp-name">{$color.name}</span>
              </label>
            </li>
          {/foreach}
        </ul>
      {else}
        <em>{l s='Aucune couleur définie' mod='ps_custom_product'}</em>
      {/if}
    </div>
  {/foreach}
</div>