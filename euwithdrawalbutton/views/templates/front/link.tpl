{if $euwb_position == 'account'}
  <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12 euwb-account-link" href="{$euwb_url|escape:'html':'UTF-8'}">
    <span class="link-item">{$euwb_label|escape:'html':'UTF-8'}</span>
  </a>
{else}
  <div class="euwb-link euwb-link--{$euwb_position|escape:'html':'UTF-8'}{if $euwb_sticky && $euwb_position == 'footer'} euwb-link--sticky{/if}">
    <a class="euwb-link__button" href="{$euwb_url|escape:'html':'UTF-8'}">
      {$euwb_label|escape:'html':'UTF-8'}
    </a>
  </div>
{/if}
