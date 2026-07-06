<section class="euwb-page" aria-labelledby="euwb-verify-confirmed-title">
  <h1 id="euwb-verify-confirmed-title">{l s='Verifizierung' mod='euwithdrawalbutton'}</h1>
  {if $euwb_verified}
    <div class="alert alert-success" role="status">{l s='Der Verifizierungslink ist gültig.' mod='euwithdrawalbutton'}</div>
  {else}
    <div class="alert alert-warning" role="alert">{l s='Der Verifizierungslink ist ungültig oder abgelaufen.' mod='euwithdrawalbutton'}</div>
  {/if}
  <p>
    <a class="btn btn-primary" href="{$euwb_withdrawal_url|escape:'html':'UTF-8'}">
      {l s='Zur Widerrufserklärung' mod='euwithdrawalbutton'}
    </a>
  </p>
</section>

