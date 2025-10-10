<div class="add">
	<button id="get-custom-product" class="btn btn-primary" type="button" onclick="pcpQuote()">
		<i class="material-icons"></i>
		{l s='Préparer votre produit personnalisé' mod='ps_custom_product'}
	</button>
</div>
<div id="add-to-cart-container" class="mt-3" style="display:none;">
  <div id="pcp-summary" class="border rounded p-3 mt-3">
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



    <div id="pcp-bloc-render" style="max-width:200px;margin:auto;">
      <svg></svg>
    </div>


    <div class="pcp-bloc-price text-center mt-2 mb-2">
      <span class="text-muted d-block">{l s='Prix unitaire' mod='ps_custom_product'}</span>
      <strong id="price-result" class="fs-4 text-primary"></strong>
    </div>

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
