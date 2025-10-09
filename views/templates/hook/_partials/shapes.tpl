<div id="pcp-shapes-list" class="group-input mt-2 mb-0">
  <h3>1 - {l s='Choisissez la forme' mod='ps_custom_product'}</h3>

  <div class="btn-group" data-toggle="buttons">
    {foreach from=$shapes key=code item=shape}
      <label class="btn btn-outline-primary m-1">
        <input type="radio" class="pcp-shape-input" name="PCP_SHAPE" value="{$code}" autocomplete="off">
        {$shape.label}
      </label>
    {/foreach}
  </div>
</div>

<div id="pcp-shapes-fields" class="mt-0">
<h3>2 - {l s='Choisissez les dimensions' mod='ps_custom_product'}</h3>
  {foreach from=$shapes key=code item=shape}
    <div id="pcp-shape-{$code}-fields" class="pcp-shape-fields border rounded px-2 mb-2" data-shape="{$code}" style="display:none;">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const blocks = document.querySelectorAll('.pcp-shape-fields');

  function showShape(val) {
    blocks.forEach(b => b.style.display = (b.dataset.shape === val) ? 'block' : 'none');
  }
  function activateLabel(input) {
    document.querySelectorAll('#pcp-shapes-list label.btn').forEach(l => l.classList.remove('active'));
    const lbl = input.closest('label.btn');
    if (lbl) lbl.classList.add('active');
  }
  function selectShape(input) {
    if (!input) return;
    input.checked = true;                 
    activateLabel(input);                 
    showShape(input.value);
  }
  // check first shape
  const firstChecked = document.querySelector('input.pcp-shape-input[name="PCP_SHAPE"]:checked');
  const first        = firstChecked || document.querySelector('input.pcp-shape-input[name="PCP_SHAPE"]');
  if (first) selectShape(first);

  document.addEventListener('click', function (e) {
    const lbl = e.target.closest('#pcp-shapes-list label.btn');
    if (!lbl) return;
    const input = lbl.querySelector('input.pcp-shape-input[name="PCP_SHAPE"]');
    if (input) selectShape(input);
  });
});
</script>