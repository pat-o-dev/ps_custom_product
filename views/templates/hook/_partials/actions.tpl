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
