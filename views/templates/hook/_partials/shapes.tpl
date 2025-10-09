<div id="pcp-shapes-list" class="group-input mt-2 mb-0">
  <h3>1 - {l s='Choisissez la forme' mod='ps_custom_product'}</h3>

  <div class="btn-group">
    {foreach from=$shapes key=code item=shape name=shapes}
      <label for="pcp-shape-input-{$code}" class="btn btn-outline-primary m-1 {if $smarty.foreach.shapes.first}active{/if}">
        <input type="radio"  id="pcp-shape-input-{$code}" class="pcp-shape-input" name="PCP_SHAPE" value="{$code}" style="display:none" {if $smarty.foreach.shapes.first}checked{/if} />
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
          <div class="form-group">
            <label for="pcp-{$code}-{$fname}">
              {$fname|escape:'htmlall'} ({$field.min} → {$field.max})
            </label>
            <input
              class="form-control"
              type="number"
              name="{$fname}"
              id="pcp-{$code}-{$fname}"
              value="{$field.default}"
              min="{$field.min}"
              max="{$field.max}"
              step="{$field.step}"
            />
          </div>
        {/foreach}
      {/if}
    </div>
  {/foreach}
</div>