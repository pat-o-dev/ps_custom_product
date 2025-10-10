/*** GLOBAL PCP HELPERS ***/

// Update Quantity
function pcpChangeQty(delta) {
  const input = document.getElementById('pcp-quantity');
  if (!input) return;
  const val = Math.max(1, parseInt(input.value || 1, 10) + delta);
  input.value = val;
}

// recupere la valeur radio
function pcpGetCheckedValue(name) {
  const el = document.querySelector(`input[name="${name}"]:checked`);
  return el ? el.value : null;
}

// recupere les dimensions
function pcpGetShapeDimension(shapeCode) {
  const block = document.querySelector(`.pcp-shape-fields[data-shape="${shapeCode}"]`);
  const res = { dimensions: {}, valid: true, error: '' };
  if (!block) return { valid: false, error: 'Bloc forme introuvable', dimensions: {} };

  block.querySelectorAll('input[type="number"]').forEach(input => {
    const key = input.name || input.id || '';
    const v = Number(input.value);
    if (isNaN(v)) res.valid = false;
    res.dimensions[key] = v;
  });
  return res;
}

/*** SHAPES ***/
function pcpShowShape(val) {
  document.querySelectorAll('.pcp-shape-fields')
    .forEach(b => b.style.display = (b.dataset.shape === val) ? '' : 'none');
  document.querySelectorAll('#pcp-shapes-list label.btn')
    .forEach(l => l.classList.remove('active'));
  const input = document.querySelector(`#pcp-shapes-list input[value='"${val}'"]`);
  if (input) input.closest('label.btn')?.classList.add('active');
}

/*** MATERIALS ***/
function pcpShowMaterial(val) {
  document.querySelectorAll('.pcp-material-fields')
    .forEach(b => b.style.display = (b.dataset.material === val) ? '' : 'none');
  document.querySelectorAll('#pcp-materials-buttons label.btn')
    .forEach(l => l.classList.remove('active'));
  const input = document.querySelector(`#pcp-materials-buttons input[value="${val}"]`);
  if (input) input.closest('label.btn')?.classList.add('active');
  // default color first
  const colors = document.querySelectorAll(`.pcp-color-input[data-material="${val}"]`);
  if (colors.length && ![...colors].some(c => c.checked)) {
    colors[0].checked = true;
  }
}

/*** COMPUTE PRICE & ADD TO CART CALL ***/
async function pcpCall(action) {
  const root = document.getElementById('pcp-root');
  const id_product = parseInt(root.dataset.idProduct);
  const customProductUrl = root.dataset.customProductUrl;

  const quantity = parseInt(document.querySelector('#pcp-quantity')?.value || 1);
  const shape = pcpGetCheckedValue('PCP_SHAPE');
  const material = pcpGetCheckedValue('PCP_MATERIAL');
  const color = pcpGetCheckedValue('PCP_COLOR');
  const { dimensions, valid, error } = pcpGetShapeDimension(shape);

  if (!valid) { 
    alert(error); // #TODO Manage Error 
    return false; 
  }
  const payload = { action, id_product, quantity, shape, dimensions, material, color };
  const resp = await fetch(customProductUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  return await resp.json();
}

/*** COMPUTE PRICE BTN ***/
async function pcpQuote() {
  const priceResult = document.getElementById('price-result');
  const btnAddContainer = document.getElementById('add-to-cart-container');

  priceResult.textContent = 'Calcul en cours...';
  // Get price of customize
  const result = await pcpCall('quote');
  if (result?.success) {
    window.lastQuote = result;
    priceResult.innerHTML = `<span>Prix calculé :</span> <strong>${result.display_price}</strong>`;
    btnAddContainer.style.display = 'block';
  } else {
    priceResult.textContent = 'Erreur : ' + (result?.error || 'calcul impossible');
    btnAddContainer.style.display = 'none';
  }
}

/***  ADD TO CART BTN ***/
async function pcpAddToCart() {
  // Generate product attribute and Add to Cart
  const result = await pcpCall('add');
  if (!result?.success) {
    alert(result?.error || 'Erreur ajout panier');
    return;
  }
  try {
    // Init and try to open Modal Add To Cart, Display Only Add to Cart in pcpCall
    const url = prestashop.urls.base_url + 'module/ps_shoppingcart/ajax';
    const formData = new FormData();
    formData.append('action', 'add-to-cart');
    formData.append('id_product', result.id_product);
    formData.append('id_product_attribute', result.id_product_attribute || 0);
    formData.append('id_customization', 0);
    formData.append('quantity', result.quantity || 1);
    const res = await fetch(url, { method: 'POST', body: formData });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const json = await res.json();
    document.getElementById('blockcart-modal')?.remove();
    const temp = document.createElement('div');
    temp.innerHTML = (json.modal || '').trim();
    const modal = temp.querySelector('#blockcart-modal');
    if (!modal) {
      return;
    }
    document.body.appendChild(modal);
    if (json.preview) {
      const cartContainer = document.querySelector('#_desktop_cart');
      if (cartContainer) cartContainer.outerHTML = json.preview;
    }
    if (window.jQuery && typeof jQuery.fn.modal === 'function') {
      jQuery(modal).modal('show');
    } else {
      modal.classList.add('show');
      modal.style.display = 'block';
      document.body.classList.add('modal-open');
    }
  } catch (err) {
    // Silent Error, Modal not show
  }
}