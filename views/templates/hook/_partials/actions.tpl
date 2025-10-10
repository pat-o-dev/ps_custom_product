  <div class="add">
    <button
      id="get-custom-product"
      class="btn btn-primary"
      type="button"
      onclick="pcpQuote()"
    >
      <i class="material-icons"></i>
      {l s='Calculer le tarif' mod='ps_custom_product'}
    </button>
  </div>

  <div
    id="price-result"
    class="mt-3"
    style="font-size: 1.4em; font-weight: bold"
  ></div>
  <div id="add-to-cart-container" class="mt-2" style="display: none">
    <button id="add-custom-product" class="btn btn-success" onclick="pcpAddToCart()">
      <i class="material-icons"></i>
      {l s='Ajouter au panier' mod='ps_custom_product'}
    </button>
  </div>