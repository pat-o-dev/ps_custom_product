<div class="panel">
  <div class="panel-heading">
    {l s='Produits configurables' mod='ps_custom_product'}
  </div>

 <div class="alert alert-info" style="margin:10px 0;">
  <strong>{l s='Formule de prix HT' mod='ps_custom_product'} :</strong><br>
  {l s='Prix HT = (Base unitaire € + (Aire m² × Prix matière €/m² × Coeff matière × Facteur forme)) × Marge' mod='ps_custom_product'}
  <br>
  <small>
    {l s='• Base unitaire € : coût fixe par produit' mod='ps_custom_product'}<br>
    {l s='• Prix matière €/m² et Coeff matière : définis dans l’onglet Matières' mod='ps_custom_product'}<br>
    {l s='• Facteur forme : défini dans l’onglet Formes' mod='ps_custom_product'}<br>
    {l s='• Marge : définie pour chaque produit configurable' mod='ps_custom_product'}
  </small>
</div>

  {if $products|@count > 0}
    <form method="post">
      <table class="table">
        <thead>
          <tr>
            <th style="width:80px;">{l s='ID' mod='ps_custom_product'}</th>
            <th>{l s='Nom du produit' mod='ps_custom_product'}</th>
            <th style="width:160px;">{l s='Base unitaire (€)' mod='ps_custom_product'}</th>
            <th style="width:140px;">{l s='Tare (kg)' mod='ps_custom_product'}</th>
            <th style="width:140px;">{l s='Marge (Ratio)' mod='ps_custom_product'}</th>
            <th style="width:140px;">{l s='Attribute Group' mod='ps_custom_product'}</th>
            <th class="text-right" style="width:160px;">{l s='Actions' mod='ps_custom_product'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach $products as $p}
            <tr>
              <td><strong>#{$p.id}</strong></td>
              <td>{$p.name|escape:'htmlall':'UTF-8'}</td>
              <td>
                <input type="text" class="form-control"
                       name="SETTINGS[{$p.id}][base_unit_price]"
                       value="{$p.base_unit_price}">
              </td>
              <td>
                <input type="text" class="form-control"
                       name="SETTINGS[{$p.id}][tare_weight]"
                       value="{$p.tare_weight}">
              </td>
              <td>
                <input type="text" class="form-control"
                       name="SETTINGS[{$p.id}][rate_margin]"
                       value="{$p.rate_margin}">
              </td>
              <td>
                <select name="SETTINGS[{$p.id}][id_attribute_group]" class="form-control">
                  <option value="0">-</option>
                  {foreach $attribute_groups as $g}
                    <option value="{$g.id_attribute_group}" {if $p.id_attribute_group == $g.id_attribute_group}selected{/if}>
                      {$g.name|escape}
                    </option>
                  {/foreach}
                </select>
              </td>
              <td class="text-right">
                <a class="btn btn-sm btn-danger"
                   href="{$p.remove_url}"
                   onclick="return confirm('{l s='Retirer ce produit ?' mod='ps_custom_product'}')">
                  <i class="icon-remove"></i>
                  {l s='Supprimer' mod='ps_custom_product'}
                </a>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <div class="panel-footer text-right">
        <button type="submit" name="submit_save_product_settings" class="btn btn-primary">
          <i class="icon-save"></i> {l s='Enregistrer' mod='ps_custom_product'}
        </button>
        <a href="{$reset_url}" class="btn btn-warning"
           onclick="return confirm('{l s='Réinitialiser les réglages des produits ?' mod='ps_custom_product'}');">
          <i class="icon-refresh"></i> {l s='Réinitialiser' mod='ps_custom_product'}
        </a>
      </div>
    </form>
  {else}
    <div class="panel-body">
      <em>{l s='Aucun produit pour le moment.' mod='ps_custom_product'}</em>
    </div>
  {/if}
</div>