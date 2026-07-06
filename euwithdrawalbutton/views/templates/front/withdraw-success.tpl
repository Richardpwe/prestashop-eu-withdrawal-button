<section class="euwb-page" aria-labelledby="euwb-success-title">
  <h1 id="euwb-success-title">{l s='Widerruf eingegangen' mod='euwithdrawalbutton'}</h1>

  <div class="alert alert-success" role="status">
    {if $euwb_duplicate}
      {l s='Diese Widerrufserklärung wurde bereits erfasst.' mod='euwithdrawalbutton'}
    {else}
      {l s='Ihre Widerrufserklärung wurde erfasst.' mod='euwithdrawalbutton'}
    {/if}
  </div>

  <p>
    {l s='Ihre interne Referenz lautet:' mod='euwithdrawalbutton'}
    <strong>{$euwb_reference|escape:'html':'UTF-8'}</strong>
  </p>

  {if $euwb_mail_status == 'sent'}
    <p>{l s='Die Eingangsbestätigung wurde per E-Mail versendet.' mod='euwithdrawalbutton'}</p>
  {elseif $euwb_mail_status == 'partial'}
    <p>{l s='Die Eingangsbestätigung wurde verarbeitet; mindestens eine Benachrichtigung konnte nicht zugestellt werden.' mod='euwithdrawalbutton'}</p>
  {else}
    <p>{l s='Die Eingangsbestätigung konnte nicht sofort versendet werden. Ihre Erklärung wurde trotzdem gespeichert.' mod='euwithdrawalbutton'}</p>
  {/if}
</section>
