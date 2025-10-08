<div class="container">	
    <div class="col-md-12">
        <label for="AB">{l s='Dimension' mod='ps_custom_product'} AB</label>
        <input type="text" name="AB" id="AB" value="80" /><br />
    </div>
    <div class="col-md-12">
        <label for="AC">{l s='Dimension' mod='ps_custom_product'} BC</label>
        <input type="text" name="BC" id="BC" value="100" /><br />
    </div>
    <div class="add">
        <button id="get-custom-product" class="btn btn-primary" data-button-action="" type="submit">
        <i class="material-icons"></i>
        {l s='Calculer le tarif' mod='ps_custom_product'}
        </button>
    </div>
<div id="price-result" class="mt-3" style="font-size:1.4em;font-weight:bold;"></div>
<div id="add-to-cart-container" class="mt-2" style="display:none;">
  <button id="add-custom-product" class="btn btn-success">
    <i class="material-icons"></i> {l s='Ajouter au panier' mod='ps_custom_product'}
  </button>
</div>
</div>

<style>
body.custom_product_page .product-actions, 
body.custom_product_page .js-product-actions,
body.custom_product_page .product-prices,
body.custom_product_page .js-product-prices {
    display: none;
}
</style>

<script>
document.body.className += ' custom_product_page';

const btnCalc = document.querySelector('#get-custom-product');
const btnAddContainer = document.querySelector('#add-to-cart-container');
const btnAdd = document.querySelector('#add-custom-product');
const priceResult = document.querySelector('#price-result');

let lastQuote = null;

btnCalc.addEventListener('click', async (e) => {
    e.preventDefault();
    priceResult.textContent = 'Calcul en cours...';
    const data = {
        id_product: prestashop && prestashop.product ? prestashop.product.id_product : 0,
        ab: document.querySelector('#AB')?.value || 0,
        bc: document.querySelector('#BC')?.value || 0
    };
    const response = await fetch(
        '{$link->getModuleLink('ps_custom_product','getCustomProduct')|escape:'javascript'}',
        {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }
    );
    const result = await response.json();
    if (result.success) {
        lastQuote = result;
        priceResult.innerHTML = '<span>Prix calculé :</span> <strong>'+result.display_price+'</strong>';
        btnAddContainer.style.display = 'block';
    } else {
      priceResult.textContent = 'Erreur : ' + (result.error || 'calcul impossible');
      btnAddContainer.style.display = 'none';
    }
});
btnAdd.addEventListener('click', async (e) => {
  e.preventDefault();
  if (!lastQuote || !lastQuote.success) {
    alert('Veuillez d’abord calculer le tarif.');
    return;
  }

  const payload = {
    id_product: 20,
    width: document.querySelector('#AB')?.value || 0,
    height: document.querySelector('#BC')?.value || 0,
    price_ht: lastQuote.price_ht,
    price_ttc: lastQuote.price_ttc,
    quantity: 1
  };

  try {
    const resp = await fetch(
      '{$link->getModuleLink('ps_custom_product','addCustomProduct')|escape:'javascript'}',
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }
    );
    const res = await resp.json();
    if (res.success) {
      priceResult.innerHTML += `<br/><small class="text-success">Produit ajouté au panier ✅</small>`;
      prestashop.emit('updateCart', { reason: { idProduct: res.id_product, idProductAttribute: res.id_product_attribute }});
    } else {
      alert('Erreur : ' + res.error);
    }
  } catch (err) {
    alert('Erreur réseau : ' + err.message);
  }
});
</script> 