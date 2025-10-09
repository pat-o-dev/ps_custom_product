<div id="pcp-materials-list" class="group-input mt-2 mb-0">
  <h3>3 - {l s='Choisissez la matière' mod='ps_custom_product'}</h3>

   <div class="btn-group" id="pcp-materials-buttons" data-toggle="buttons">
    {foreach from=$materials key=code item=material}
      <label class="btn btn-outline-primary m-1">
        <input type="radio" class="pcp-material-input" name="PCP_MATERIAL" value="{$code}" autocomplete="off">
        {$material.label}
      </label>
    {/foreach}
  </div>
</div>

<div id="pcp-materials-fields" class="mt-0">
<h3>4 - {l s='Choisissez la couleur' mod='ps_custom_product'}</h3>
  {foreach from=$materials key=code item=material}
    <div class="pcp-material-fields border rounded px-2 mb-2" data-material="{$code}" style="display:none;">
      

      {if isset($material.colors) && $material.colors|@count > 0}
        <ul class="pcp-color-grid">
          {foreach from=$material.colors item=color}
            {assign var=idc value="pcp-color-`$code`-`$color.id_attribute`"}
            <li class="pcp-color">
              <input
                type="radio"
                class="pcp-color-input"
                name="PCP_COLOR"
                id="{$idc}"
                value="{$color.id_attribute}"
                data-material="{$code}"
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

{literal}
<style>
  .custom_product_page .pcp-color-grid{display:flex;flex-wrap:wrap;gap:8px;margin:0;padding:0;list-style:none}
  .custom_product_page .pcp-color{position:relative}
  .custom_product_page .pcp-color-input{position:absolute;opacity:0;pointer-events:none}
  .custom_product_page .pcp-color-label{display:flex;align-items:center;gap:8px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;cursor:pointer;user-select:none}
  .custom_product_page .pcp-color-input:checked + .pcp-color-label{border-color:#0d6efd;box-shadow:0 0 0 2px rgba(13,110,253,.15)}
  .custom_product_page .pcp-swatch{width:20px;height:20px;border-radius:4px;border:1px solid rgba(0,0,0,.1);display:inline-block}
</style>
{/literal}

<script>
document.addEventListener('DOMContentLoaded', function () {
  const matsContainer = document.getElementById('pcp-materials-buttons');
  const matBlocks = document.querySelectorAll('.pcp-material-fields');

  function showMaterial(code) {
    matBlocks.forEach(b => b.style.display = (b.dataset.material === code) ? 'block' : 'none');

    // si aucune couleur sélectionnée pour cette matière -> sélectionner la 1ère
    const firstColor = document.querySelector('.pcp-color-input[data-material="'+code+'"]');
    if (firstColor && !document.querySelector('.pcp-color-input[data-material="'+code+'"]:checked')) {
      firstColor.checked = true;
      firstColor.dispatchEvent(new Event('change', { bubbles:true }));
    }
  }

  function activateMaterialLabel(input) {
    matsContainer.querySelectorAll('label.btn').forEach(l => l.classList.remove('active'));
    const lbl = input.closest('label.btn');
    if (lbl) lbl.classList.add('active');
  }

  // init: 1ère matière
  const firstMat = matsContainer.querySelector('input.pcp-material-input') || null;
  if (firstMat) {
    firstMat.checked = true;
    activateMaterialLabel(firstMat);
    showMaterial(firstMat.value);
  }

  // délégation: clic/change sur matière
  matsContainer.addEventListener('click', function (e) {
    const lbl = e.target.closest('label.btn');
    if (!lbl) return;
    const input = lbl.querySelector('input.pcp-material-input');
    if (input) {
      input.checked = true;
      activateMaterialLabel(input);
      showMaterial(input.value);
    }
  });
  matsContainer.addEventListener('change', function (e) {
    if (e.target && e.target.matches('input.pcp-material-input')) {
      activateMaterialLabel(e.target);
      showMaterial(e.target.value);
    }
  });
});
</script>