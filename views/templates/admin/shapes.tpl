<div class="panel">
  <div class="panel-heading">{l s='Configuration des formes' mod='ps_custom_product'}</div>

  <form method="post">
    <div class="form-group">
      <label for="PCP_SHAPES_JSON">{l s='JSON des formes' mod='ps_custom_product'}</label>
      <textarea name="PCP_SHAPES_JSON" id="PCP_SHAPES_JSON" rows="25" class="form-control">
{$json_value|escape:'htmlall':'UTF-8'}
      </textarea>
      <p class="help-block">
        {l s='Modifiez le JSON puis cliquez sur Enregistrer. Chaque forme contient ses contraintes.' mod='ps_custom_product'}
      </p>
    </div>

    <div class="text-right">
      <button type="submit" name="submit_pcp_shapes_json" class="btn btn-primary">
        <i class="icon-save"></i> {l s='Enregistrer' mod='ps_custom_product'}
      </button>
      <a href="{$reset_url}" class="btn btn-warning" 
         onclick="return confirm('{l s='Réinitialiser les formes par défaut ?' mod='ps_custom_product'}');">
        <i class="icon-refresh"></i> {l s='Réinitialiser' mod='ps_custom_product'}
      </a>
    </div>
  </form>
</div>