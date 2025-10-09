<script>
  document.body.classList.add('custom_product_page');

  const btnCalc = document.querySelector('#get-custom-product');
  const btnAddContainer = document.querySelector('#add-to-cart-container');
  const btnAdd = document.querySelector('#add-custom-product');
  const priceResult = document.querySelector('#price-result');

  let id_product = {$id_product};
  let lastQuote = null;

function getCheckedValue(name) {
  const el = document.querySelector(`input[name="${ name }"]:checked`);
  return el ? el.value : null;
}

function getShapeDimension(shapeCode) {
  const block = document.querySelector(`.pcp-shape-fields[data-shape="${ shapeCode }"]`);
  const res = { dimensions: {}, valid: true, error: '' };
  if (!block) { res.valid = false; res.error = 'Bloc forme introuvable'; return res; }

  block.querySelectorAll('input[type="number"]').forEach(input => {
    const key = input.name || input.id || '';
    const v   = Number(input.value);
    const min = input.min !== '' ? Number(input.min) : -Infinity;
    const max = input.max !== '' ? Number(input.max) : Infinity;

    if (isNaN(v)) { res.valid = false; res.error = `Valeur invalide pour ${ key }`; return; }
    if (v < min)  { res.valid = false; res.error = `${ key }: minimum ${ min }`; return; }
    if (v > max)  { res.valid = false; res.error = `${ key }: maximum ${ max }`; return; }

    res.dimensions[key] = v;
  });
  return res;
}

async function callCustomProduct(action) {
  const shape = getCheckedValue('PCP_SHAPE');
  if (!shape) { console.warn('Aucune forme sélectionnée'); return false; }

  const material = getCheckedValue('PCP_MATERIAL');
  const color = getCheckedValue('PCP_COLOR');
  const { dimensions, valid, error } = getShapeDimension(shape);
  if (!valid) { console.warn(error); return false; }

  const data = {
    action: action,
    id_product: id_product,
    shape: shape,
    dimensions: dimensions, 
    material: material,
    color: color
  };

  try {
    const response = await fetch(
      '{$link->getModuleLink("ps_custom_product","getCustomProduct")|escape:"javascript"}',
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      }
    );

    if (!response.ok) {
      throw new Error('Erreur serveur : ' + response.status);
    }

    return await response.json();
  } catch (err) {
    console.error(err);
    priceResult.textContent = 'Erreur réseau : ' + err.message;
    return false;
  }
}

// ---- Événement calcul de prix ----
if (btnCalc) {
  btnCalc.addEventListener('click', async (e) => {
    e.preventDefault();
    priceResult.textContent = 'Calcul en cours...';
    const result = await callCustomProduct('quote');

    if (result && result.success) {
      lastQuote = result;
      priceResult.innerHTML = `
        <span>Prix calculé :</span> 
        <strong>${ result.display_price }</strong>
      `;
      btnAddContainer.style.display = 'block';
    } else {
      priceResult.textContent = 'Erreur : ' + ((result && result.error) || 'calcul impossible');
      btnAddContainer.style.display = 'none';
    }
  });
}

  if (btnAdd) {
    btnAdd.addEventListener('click', async (e) => {
      e.preventDefault();

      if (!lastQuote || !lastQuote.success) {
        alert('Veuillez d’abord calculer le tarif.');
        return;
      }

      const result = await callCustomProduct('add');

      if (result && result.success) {
        priceResult.innerHTML += `<br><small class="text-success">Produit ajouté au panier ✅</small>`;

        if (window.prestashop && typeof prestashop.emit === 'function') {
          prestashop.emit('updateCart', {
            reason: {
              idProduct: result.id_product,
              idProductAttribute: result.id_product_attribute || 0
            }
          });
        }
      } else {
        alert('Erreur : ' + ((result && result.error) || 'ajout impossible'));
      }

    });
  }
</script>