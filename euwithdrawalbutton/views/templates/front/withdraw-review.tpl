<section class="euwb-page" aria-labelledby="euwb-review-title">
  <h1 id="euwb-review-title">{l s='Widerruf prüfen' mod='euwithdrawalbutton'}</h1>

  <div class="euwb-review" role="region" aria-label="{l s='Zusammenfassung Ihrer Widerrufserklärung' mod='euwithdrawalbutton'}">
    <dl>
      <dt>{l s='Name' mod='euwithdrawalbutton'}</dt>
      <dd>{$euwb_data.customer_name|escape:'html':'UTF-8'}</dd>

      <dt>{l s='E-Mail' mod='euwithdrawalbutton'}</dt>
      <dd>{$euwb_data.customer_email|escape:'html':'UTF-8'}</dd>

      {if $euwb_data.order_reference}
        <dt>{l s='Bestellreferenz' mod='euwithdrawalbutton'}</dt>
        <dd>{$euwb_data.order_reference|escape:'html':'UTF-8'}</dd>
      {/if}

      {if $euwb_data.invoice_number}
        <dt>{l s='Rechnungsnummer' mod='euwithdrawalbutton'}</dt>
        <dd>{$euwb_data.invoice_number|escape:'html':'UTF-8'}</dd>
      {/if}

      <dt>{l s='Vertrag / betroffener Vertragsteil' mod='euwithdrawalbutton'}</dt>
      <dd>{$euwb_data.contract_identification_text|escape:'html':'UTF-8'|nl2br nofilter}</dd>

      {if $euwb_data.customer_message}
        <dt>{l s='Weitere Angaben' mod='euwithdrawalbutton'}</dt>
        <dd>{$euwb_data.customer_message|escape:'html':'UTF-8'|nl2br nofilter}</dd>
      {/if}
    </dl>
  </div>

  <form class="euwb-form euwb-form--inline" method="post" action="{$euwb_form_action|escape:'html':'UTF-8'}">
    <input type="hidden" name="euwb_token" value="{$euwb_token|escape:'html':'UTF-8'}">
    {foreach from=$euwb_hidden_fields key=field item=value}
      <input type="hidden" name="{$field|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}">
    {/foreach}
    <button class="btn btn-primary" type="submit" name="euwb_confirm" value="1">
      {$euwb_final_label|escape:'html':'UTF-8'}
    </button>
    <button class="btn btn-secondary" type="submit" name="euwb_edit" value="1" formaction="{$euwb_form_action|escape:'html':'UTF-8'}">
      {l s='Zurück' mod='euwithdrawalbutton'}
    </button>
  </form>
</section>
