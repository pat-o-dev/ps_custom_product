/**
 * 2007-2025 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Patrick Genitrini
 * @copyright 2007-2025 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

/*** GLOBAL PCP HELPERS ***/
let pcpInit = false;
// Switch Tabs
function pcpShowPreviewTab() {
  const tab = document.querySelector('#pcp-preview-tab');
  if (tab) tab.click();
}

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

/*** RESUME RENDER ***/
function pcpUpdateSummary(price) {

  const shapeVal = pcpGetCheckedValue('PCP_SHAPE');
  const materialVal = pcpGetCheckedValue('PCP_MATERIAL');
  const colorVal = pcpGetCheckedValue('PCP_COLOR');
  
  const shapeLabel = document.querySelector(`label[for="pcp-shape-input-${shapeVal}"]`)?.textContent.trim() || shapeVal || '—';
  const materialLabel = document.querySelector(`label[for="pcp-material-input-${materialVal}"]`)?.textContent.trim() || materialVal || '—';
  const colorLabel = document.querySelector(`label[for="pcp-color-${materialVal}-${colorVal}"] .pcp-name`)?.textContent.trim() || colorVal || '—';
  
  const { dimensions } = pcpGetShapeDimension(shapeVal);
  const dimList = document.getElementById('pcp-param-dim-list');
  dimList.innerHTML = Object.entries(dimensions)
    .map(([k, v]) => `<li>${k.toUpperCase()} = ${v}</li>`)
    .join('') || '<li><em>-</em></li>';

  document.getElementById('pcp-param-shape').textContent = shapeLabel || '—';
  document.getElementById('pcp-param-material').textContent = materialLabel || '—';
  document.getElementById('pcp-param-color').textContent = colorLabel || '—';
  document.getElementById('price-result').textContent = price || '';
  pcpRenderShape(shapeVal, dimensions);
}
/*** FAST SVG RENDER ***/
function pcpRenderShape(shape, dims = {}) {
  const svg = document.querySelector('#pcp-bloc-render svg');
  if (!svg) return;

  svg.innerHTML = '';
  svg.setAttribute('viewBox', '0 0 220 160');
  svg.style.width = '100%';
  svg.style.height = 'auto';
  svg.style.background = '#f9f9f9';
  svg.style.border = '1px solid #ccc';
  svg.style.borderRadius = '4px';

  const ns = 'http://www.w3.org/2000/svg';

  const colorInput = document.querySelector('input[name="PCP_COLOR"]:checked');
  let fillColor = '#ddd';
  if (colorInput) {
    const swatch = colorInput.nextElementSibling?.querySelector('.pcp-swatch');
    if (swatch) fillColor = window.getComputedStyle(swatch).backgroundColor;
  }

  const ab = parseFloat(dims.ab || dims.AB || 100);
  const bc = parseFloat(dims.bc || dims.BC || 100);
  const maxDim = Math.max(ab, bc);
  const scale = 120 / maxDim;

  const w = ab * scale;
  const h = bc * scale;

  const x0 = (200 - w) / 2;
  const y0 = (150 - h) / 2;

  let el;
  let points = [];

  switch (shape) {
    case 'RECT':
      el = document.createElementNS(ns, 'rect');
      el.setAttribute('x', x0);
      el.setAttribute('y', y0);
      el.setAttribute('width', w);
      el.setAttribute('height', h);
      el.setAttribute('fill', fillColor);
      el.setAttribute('stroke', '#333');
      points = [
        { x: x0, y: y0, label: 'A' },
        { x: x0 + w, y: y0, label: 'B' },
        { x: x0 + w, y: y0 + h, label: 'C' },
        { x: x0, y: y0 + h, label: 'D' },
      ];
      break;

    case 'SQR':
      el = document.createElementNS(ns, 'rect');
      el.setAttribute('x', 60);
      el.setAttribute('y', 30);
      el.setAttribute('width', 100);
      el.setAttribute('height', 100);
      el.setAttribute('fill', fillColor);
      el.setAttribute('stroke', '#333');
      points = [
        { x: 60, y: 30, label: 'A' },
        { x: 160, y: 30, label: 'B' },
        { x: 160, y: 130, label: 'C' },
        { x: 60, y: 130, label: 'D' },
      ];
      break;

    case 'TRI':
      el = document.createElementNS(ns, 'polygon');
      el.setAttribute('points', '110,20 190,130 30,130');
      el.setAttribute('fill', fillColor);
      el.setAttribute('stroke', '#333');
      points = [
        { x: 110, y: 20, label: 'A' },
        { x: 190, y: 130, label: 'B' },
        { x: 30, y: 130, label: 'C' },
      ];
      break;

    default:
      el = document.createElementNS(ns, 'text');
      el.setAttribute('x', 70);
      el.setAttribute('y', 80);
      el.textContent = 'Aperçu';
      el.setAttribute('fill', '#aaa');
      el.setAttribute('font-size', '14');
      el.setAttribute('font-family', 'sans-serif');
  }

  svg.appendChild(el);

  points.forEach(p => {
    const circle = document.createElementNS(ns, 'circle');
    circle.setAttribute('cx', p.x);
    circle.setAttribute('cy', p.y);
    circle.setAttribute('r', 2.5);
    circle.setAttribute('fill', '#333');
    svg.appendChild(circle);

    const label = document.createElementNS(ns, 'text');
    label.setAttribute('x', p.x + 4);
    label.setAttribute('y', p.y - 4);
    label.textContent = p.label;
    label.setAttribute('fill', '#333');
    label.setAttribute('font-size', '10');
    label.setAttribute('font-family', 'sans-serif');
    svg.appendChild(label);
  });
}

/*** SHAPES ***/
function pcpShowShape(val) {
  // affiche / masque les blocs de champs selon la forme choisie
  document.querySelectorAll('.pcp-shape-fields')
    .forEach(b => b.style.display = (b.dataset.shape === val) ? '' : 'none');

  // met à jour les boutons actifs
  document.querySelectorAll('#pcp-shapes-list label.btn')
    .forEach(l => l.classList.remove('active'));

  const input = document.querySelector(`#pcp-shapes-list input[value="${val}"]`);
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
    priceResult.innerHTML = result.display_price;
    btnAddContainer.style.display = 'block';
    if(pcpInit)
      pcpShowPreviewTab();
    else 
      pcpInit = true;
    pcpUpdateSummary(result.display_price);
    
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

// INIT
document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('#get-custom-product').click()
  const tabConfig = document.querySelector('#tab-config-tab');
  if (tabConfig && !tabConfig.classList.contains('active')) {
    new bootstrap.Tab(tabConfig).show();
  }

});