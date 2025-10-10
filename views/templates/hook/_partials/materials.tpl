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
    <div class="pcp-material-fields border rounded px-2 mb-2"
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
                <span class="pcp-swatch" style="background-color:{$color.color|escape:'htmlall'};"></span>
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