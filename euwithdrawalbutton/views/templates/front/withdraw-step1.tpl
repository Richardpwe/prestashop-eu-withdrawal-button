<section class="euwb-page" aria-labelledby="euwb-title">
  <h1 id="euwb-title">{l s='Vertrag widerrufen' mod='euwithdrawalbutton'}</h1>

  {if isset($euwb_errors.generic)}
    <div class="alert alert-danger" role="alert">{$euwb_errors.generic|escape:'html':'UTF-8'}</div>
  {/if}

  {if $euwb_logged_in && $euwb_account_orders|count}
    <section class="euwb-panel" aria-labelledby="euwb-orders-title">
      <h2 id="euwb-orders-title">{l s='Ihre letzten Bestellungen' mod='euwithdrawalbutton'}</h2>
      <p>{l s='Sie können eine Bestellreferenz übernehmen oder das Formular manuell ausfüllen.' mod='euwithdrawalbutton'}</p>
      <ul class="euwb-order-list">
        {foreach from=$euwb_account_orders item=order}
          <li>
            <button type="button" class="euwb-order-select" data-reference="{$order.reference|escape:'html':'UTF-8'}">
              {$order.reference|escape:'html':'UTF-8'} - {$order.date_add|escape:'html':'UTF-8'}
            </button>
          </li>
        {/foreach}
      </ul>
    </section>
  {/if}

  <form class="euwb-form" method="post" action="{$euwb_form_action|escape:'html':'UTF-8'}" novalidate>
    <input type="hidden" name="euwb_token" value="{$euwb_token|escape:'html':'UTF-8'}">
    <div class="euwb-honeypot" aria-hidden="true">
      <label for="euwb-website">{l s='Website' mod='euwithdrawalbutton'}</label>
      <input id="euwb-website" type="text" name="euwb_website" value="" tabindex="-1" autocomplete="off">
    </div>

    <div class="form-group">
      <label for="euwb-customer-name">{l s='Name' mod='euwithdrawalbutton'} <span aria-hidden="true">*</span></label>
      <input id="euwb-customer-name" class="form-control" type="text" name="customer_name" value="{$euwb_data.customer_name|default:''|escape:'html':'UTF-8'}" autocomplete="name" required aria-describedby="{if isset($euwb_errors.customer_name)}euwb-customer-name-error{/if}">
      {if isset($euwb_errors.customer_name)}<p id="euwb-customer-name-error" class="euwb-error">{$euwb_errors.customer_name|escape:'html':'UTF-8'}</p>{/if}
    </div>

    <div class="form-group">
      <label for="euwb-customer-email">{l s='E-Mail-Adresse für die Eingangsbestätigung' mod='euwithdrawalbutton'} <span aria-hidden="true">*</span></label>
      <input id="euwb-customer-email" class="form-control" type="email" name="customer_email" value="{$euwb_data.customer_email|default:''|escape:'html':'UTF-8'}" autocomplete="email" required aria-describedby="{if isset($euwb_errors.customer_email)}euwb-customer-email-error{/if}">
      {if isset($euwb_errors.customer_email)}<p id="euwb-customer-email-error" class="euwb-error">{$euwb_errors.customer_email|escape:'html':'UTF-8'}</p>{/if}
    </div>

    <div class="form-group">
      <label for="euwb-order-reference">{l s='Bestellreferenz oder Bestellnummer' mod='euwithdrawalbutton'}</label>
      <input id="euwb-order-reference" class="form-control" type="text" name="order_reference" value="{$euwb_data.order_reference|default:''|escape:'html':'UTF-8'}" autocomplete="off">
    </div>

    <div class="form-group">
      <label for="euwb-invoice-number">{l s='Rechnungsnummer' mod='euwithdrawalbutton'}</label>
      <input id="euwb-invoice-number" class="form-control" type="text" name="invoice_number" value="{$euwb_data.invoice_number|default:''|escape:'html':'UTF-8'}" autocomplete="off">
    </div>

    <div class="form-group">
      <label for="euwb-scope">{l s='Umfang des Widerrufs' mod='euwithdrawalbutton'}</label>
      <select id="euwb-scope" class="form-control" name="withdrawal_scope">
        <option value="unknown" {if $euwb_data.withdrawal_scope|default:'unknown' == 'unknown'}selected{/if}>{l s='Bitte anhand meiner Angaben prüfen' mod='euwithdrawalbutton'}</option>
        <option value="full_order" {if $euwb_data.withdrawal_scope|default:'' == 'full_order'}selected{/if}>{l s='Gesamte Bestellung / gesamter Vertrag' mod='euwithdrawalbutton'}</option>
        <option value="partial_order" {if $euwb_data.withdrawal_scope|default:'' == 'partial_order'}selected{/if}>{l s='Teil der Bestellung / Teil des Vertrags' mod='euwithdrawalbutton'}</option>
        <option value="free_text" {if $euwb_data.withdrawal_scope|default:'' == 'free_text'}selected{/if}>{l s='Freitextangabe' mod='euwithdrawalbutton'}</option>
      </select>
    </div>

    <div class="form-group">
      <label for="euwb-contract-identification">{l s='Angaben zum Vertrag oder betroffenen Vertragsteil' mod='euwithdrawalbutton'} <span aria-hidden="true">*</span></label>
      <textarea id="euwb-contract-identification" class="form-control" name="contract_identification_text" rows="5" required aria-describedby="{if isset($euwb_errors.contract_identification_text)}euwb-contract-identification-error{/if}">{$euwb_data.contract_identification_text|default:''|escape:'html':'UTF-8'}</textarea>
      {if isset($euwb_errors.contract_identification_text)}<p id="euwb-contract-identification-error" class="euwb-error">{$euwb_errors.contract_identification_text|escape:'html':'UTF-8'}</p>{/if}
    </div>

    <div class="form-group">
      <label for="euwb-affected-part">{l s='Betroffener Artikel / Teil des Vertrags' mod='euwithdrawalbutton'}</label>
      <textarea id="euwb-affected-part" class="form-control" name="affected_contract_part" rows="3">{$euwb_data.affected_contract_part|default:''|escape:'html':'UTF-8'}</textarea>
    </div>

    <div class="form-group">
      <label for="euwb-message">{l s='Weitere Angaben zur Widerrufserklärung' mod='euwithdrawalbutton'}</label>
      <textarea id="euwb-message" class="form-control" name="customer_message" rows="4">{$euwb_data.customer_message|default:''|escape:'html':'UTF-8'}</textarea>
    </div>

    <input type="hidden" name="items_json" value="{$euwb_data.items_json|default:''|escape:'html':'UTF-8'}">

    {if $euwb_privacy_url}
      <p class="euwb-privacy">
        <a href="{$euwb_privacy_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener noreferrer">{l s='Datenschutzhinweise öffnen' mod='euwithdrawalbutton'}</a>
      </p>
    {/if}

    <button class="btn btn-primary" type="submit" name="euwb_review" value="1">
      {l s='Angaben prüfen' mod='euwithdrawalbutton'}
    </button>
  </form>
</section>
