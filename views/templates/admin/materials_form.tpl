{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
<form method="post">
  <div class="panel">
    <div class="panel-heading">
      {l s='Matières et Couleurs' mod='ps_custom_product'}
    </div>

    <table class="table">
      <thead>
        <tr>
          <th style="width:90px;">{l s='Actif' mod='ps_custom_product'}</th>
          <th>{l s='Code' mod='ps_custom_product'}</th>
          <th>{l s='Nom' mod='ps_custom_product'}</th>
          <th>{l s='Coeff' mod='ps_custom_product'}</th>
          <th>{l s='Prix matière (€/m²)' mod='ps_custom_product'}</th>
          <th>{l s='Poids (kg/m²)' mod='ps_custom_product'}</th>
          <th>{l s='Groupe couleur' mod='ps_custom_product'}</th>
          <th style="width:110px;">{l s='Position' mod='ps_custom_product'}</th>
          <th style="width:80px;"></th>
        </tr>
      </thead>
      <tbody>
        {foreach $materials as $code => $m}
          <tr>
            <td class="text-center">
              <input type="hidden" name="MATERIALS[{$code}][enabled]" value="0">
              <input type="checkbox" name="MATERIALS[{$code}][enabled]" value="1" {if $m.enabled}checked{/if}>
            </td>
            <td><input type="text" class="form-control" name="MATERIALS[{$code}][code]" value="{$code}" readonly></td>
            <td><input type="text" class="form-control" name="MATERIALS[{$code}][label]" value="{$m.label|escape}"></td>
            <td><input type="text" class="form-control" name="MATERIALS[{$code}][coeff]" value="{$m.coeff}"></td>
            <td><input type="text" class="form-control" name="MATERIALS[{$code}][price_m2]" value="{$m.price_m2|default:'0'}"></td>
            <td><input type="text" class="form-control" name="MATERIALS[{$code}][weight_m2]" value="{$m.weight_m2}"></td>
            <td>
              <select name="MATERIALS[{$code}][color_group_id]" class="form-control">
                <option value="0">-</option>
                {foreach $attribute_groups as $g}
                  <option value="{$g.id_attribute_group}" {if $m.color_group_id == $g.id_attribute_group}selected{/if}>
                    {$g.name|escape}
                  </option>
                {/foreach}
              </select>
            </td>
            <td>
              <input type="number" class="form-control" name="MATERIALS[{$code}][position]" value="{$m.position|default:0}">
            </td>
            <td class="text-right">
              <a href="{$currentIndex}&token={$token}&delete_material={$code}" class="btn btn-danger btn-sm"
                 onclick="return confirm('{l s='Supprimer cette matière ?' mod='ps_custom_product'}');">
                <i class="icon-trash"></i>
              </a>
            </td>
          </tr>
        {/foreach}

        <!-- ligne d'ajout -->
        <tr class="bg-light">
          <td class="text-center">
            <input type="hidden" name="MATERIALS[new][enabled]" value="0">
            <input type="checkbox" name="MATERIALS[new][enabled]" value="1" checked>
          </td>
          <td><input type="text" class="form-control" name="MATERIALS[new][code]" placeholder="EX: COT"></td>
          <td><input type="text" class="form-control" name="MATERIALS[new][label]" placeholder="Coton"></td>
          <td><input type="text" class="form-control" name="MATERIALS[new][coeff]" placeholder="1,0"></td>
          <td><input type="text" class="form-control" name="MATERIALS[new][price_m2]" placeholder="0,00"></td>
          <td><input type="text" class="form-control" name="MATERIALS[new][weight_m2]" placeholder="0,18"></td>
          <td>
            <select name="MATERIALS[new][color_group_id]" class="form-control">
              <option value="0">-</option>
              {foreach $attribute_groups as $g}
                <option value="{$g.id_attribute_group}">{$g.name|escape}</option>
              {/foreach}
            </select>
          </td>
          <td><input type="number" class="form-control" name="MATERIALS[new][position]" placeholder="0"></td>
          <td></td>
        </tr>
      </tbody>
    </table>

    <div class="panel-footer text-right">
      <button type="submit" name="submit_pcp_materials" class="btn btn-primary">
        <i class="icon-save"></i> {l s='Enregistrer' mod='ps_custom_product'}
      </button>
      <button type="submit" name="reset_pcp_materials" class="btn btn-warning"
              onclick="return confirm('{l s='Réinitialiser les matières par défaut ?' mod='ps_custom_product'}');">
        <i class="icon-refresh"></i> {l s='Réinitialiser' mod='ps_custom_product'}
      </button>
    </div>
  </div>
</form>