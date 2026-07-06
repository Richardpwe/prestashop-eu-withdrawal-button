<section class="euwb-page" aria-labelledby="euwb-verify-title">
  <h1 id="euwb-verify-title">{l s='Bestellung verifizieren' mod='euwithdrawalbutton'}</h1>
  <p>{l s='Die manuelle Widerrufserklärung ist jederzeit ohne Verifizierung möglich.' mod='euwithdrawalbutton'}</p>

  {if $euwb_requested}
    <div class="alert alert-info" role="status">
      {l s='Wenn die Angaben sicher zugeordnet werden konnten, wurde ein Verifizierungslink an die Bestell-E-Mail-Adresse gesendet.' mod='euwithdrawalbutton'}
    </div>
  {/if}

  <form class="euwb-form" method="post" action="{$euwb_form_action|escape:'html':'UTF-8'}">
    <input type="hidden" name="euwb_token" value="{$euwb_token|escape:'html':'UTF-8'}">
    <div class="form-group">
      <label for="euwb-verify-name">{l s='Name' mod='euwithdrawalbutton'}</label>
      <input id="euwb-verify-name" class="form-control" type="text" name="customer_name" autocomplete="name">
    </div>
    <div class="form-group">
      <label for="euwb-verify-email">{l s='E-Mail-Adresse' mod='euwithdrawalbutton'}</label>
      <input id="euwb-verify-email" class="form-control" type="email" name="customer_email" autocomplete="email">
    </div>
    <div class="form-group">
      <label for="euwb-verify-order">{l s='Bestellreferenz' mod='euwithdrawalbutton'}</label>
      <input id="euwb-verify-order" class="form-control" type="text" name="order_reference" autocomplete="off">
    </div>
    <button class="btn btn-outline-primary" type="submit" name="euwb_request_verification" value="1">
      {l s='Verifizierungslink anfordern' mod='euwithdrawalbutton'}
    </button>
  </form>

  <p>
    <a class="btn btn-primary" href="{$euwb_withdrawal_url|escape:'html':'UTF-8'}">
      {l s='Zur Widerrufserklärung' mod='euwithdrawalbutton'}
    </a>
  </p>
</section>
