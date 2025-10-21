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
 <div id="pcp-shapes-list" class="group-input mt-0 mb-0">
	<h3>1 - {l s='Choisissez la forme' mod='ps_custom_product'}</h3>

	<div class="btn-group">
		{foreach from=$shapes key=code item=shape name=shapes}
		<label for="pcp-shape-input-{$code}" class="btn btn-outline-primary m-1 {if $smarty.foreach.shapes.first}active{/if}">
			<input type="radio" id="pcp-shape-input-{$code}" class="pcp-shape-input" name="PCP_SHAPE" value="{$code}" style="display:none" {if $smarty.foreach.shapes.first}checked {/if} onchange="pcpShowShape(this.value)"/>
			{$shape.label}
		</label>
		{/foreach}
	</div>
</div>


<div id="pcp-shapes-fields" class="mt-0">
	<h3>2 - {l s='Choisissez les dimensions' mod='ps_custom_product'}</h3>

	{foreach from=$shapes key=code item=shape name=shapesDimension}
	<div id="pcp-shape-{$code}-fields" class="pcp-shape-fields border rounded px-2 mb-2" data-shape="{$code}" style="{if $smarty.foreach.shapesDimension.first}display:block;{else}display:none;{/if}">
		{if isset($shape.fields)}
		  {foreach from=$shape.fields key=fname item=field}
      <div class="form-group row mb-2" style="gap:8px;">
        <label for="pcp-{$code}-{$fname}" class="mb-0 col-sm-4 col-form-label" style="font-weight:500;">
          {$fname|escape:'htmlall':'UTF-8'|upper}
          <small class="text-muted">({$field.min} → {$field.max})</small>
        </label>
        <input class="form-control col-sm-8 text-right" type="number" name="{$fname}" id="pcp-{$code}-{$fname}" value="{$field.default}" min="{$field.min}" max="{$field.max}" step="{$field.step}" style="width:90px;"/>
      </div>
		  {/foreach}
		{/if}
	</div>
	{/foreach}
</div>
