
<div id="pcp-root"
     data-id-product="{$id_product}"
     data-custom-product-url="{$link->getModuleLink('ps_custom_product','getCustomProduct')}">
</div>
<script>
document.body.classList.add('custom_product_page');

const root = document.getElementById('pcp-root');
const id_product = parseInt(root.dataset.idProduct);
const customProductUrl = root.dataset.customProductUrl;

const getCustomProductUrl = '{$link->getModuleLink("ps_custom_product","getCustomProduct")|escape:"javascript"}';
const btnCalc = document.querySelector('#get-custom-product');
const btnAddContainer = document.querySelector('#add-to-cart-container');
const btnAdd = document.querySelector('#add-custom-product');
const priceResult = document.querySelector('#price-result');

let lastQuote = null;

/***
* ACTION
***/
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
    } else {
      alert('Erreur : ' + ((result && result.error) || 'ajout impossible'));
    }
  });
}
/***
* CALL AJAX
***/
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
    const response = await fetch(customProductUrl,
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

/***
* HELPERS & UTILS
***/
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

/***
* SHAPES
***/
  const shape_box= document.getElementById('pcp-shapes-list');
  if (shape_box) {
    shape_box.addEventListener('change', (e) => {
      if (e.target.name !== 'PCP_SHAPE') return;
      const val = e.target.value;
      shape_box.querySelectorAll('label.btn').forEach(l => l.classList.toggle('active', l.contains(e.target)));
      document.querySelectorAll('.pcp-shape-fields').forEach(b => {
        b.style.display = (b.dataset.shape === val) ? '' : 'none';
      });
    });
  }
/***
* MATERIALS
***/
const material_box = document.getElementById('pcp-materials-buttons');
if (material_box) {
  const showMaterial = (val) => {
    material_box.querySelectorAll('label.btn')
      .forEach(l => l.classList.toggle('active', l.querySelector('input')?.value === val));
    document.querySelectorAll('.pcp-material-fields')
      .forEach(b => b.style.display = (b.dataset.material === val) ? '' : 'none');
    const colors = document.querySelectorAll(`.pcp-color-input[data-material="${ val }"]`);
    if (colors.length && ![...colors].some(c => c.checked)) {
      colors[0].checked = true;
      colors[0].dispatchEvent(new Event('change', { bubbles: true }));
    }
  };
  material_box.addEventListener('change', (e) => {
    if (e.target.name === 'PCP_MATERIAL') showMaterial(e.target.value);
  });
  // ---- INIT ----
  const first = material_box.querySelector('input[name="PCP_MATERIAL"]:checked')
             || material_box.querySelector('input[name="PCP_MATERIAL"]');
  if (first) {
    first.checked = true;
    showMaterial(first.value);
  }
}
</script>