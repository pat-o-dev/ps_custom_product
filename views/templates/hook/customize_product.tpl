{if $shapes}
<div id="pcp-main-content" class="container py-2">
  {include file="module:ps_custom_product/views/templates/hook/_partials/shapes.tpl"}
  {include file="module:ps_custom_product/views/templates/hook/_partials/materials.tpl"}
  {include file="module:ps_custom_product/views/templates/hook/_partials/actions.tpl"}
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

