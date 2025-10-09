{if $shapes}
<div id="pcp-main-content" class="container py-2">
  {include file="module:ps_custom_product/views/templates/hook/_partials/shapes.tpl"}
  {include file="module:ps_custom_product/views/templates/hook/_partials/materials.tpl"}
  {include file="module:ps_custom_product/views/templates/hook/_partials/actions.tpl"}
</div>
{else}
{/if}

{include file="module:ps_custom_product/views/templates/hook/_partials/scripts.tpl"}
