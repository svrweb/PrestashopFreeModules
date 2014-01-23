   <div class="y-settings-white-box">
    <form action="{$action}" method="post">
      <div class="y-page-header">
        <i class="y-logo"></i>Settings</div>
           {if !$finishedRegistration && !$allreadyUsingYotpo}<div class="y-settings-title">To customize the look and feel of the widget, and to edit your Mail After Purchase settings, just head to the 
            {if $appKey && $appKey != '' && $oauthToken && $oauthToken != ''}
              <a class="y-href" href="https://api.yotpo.com/users/b2blogin?app_key={$appKey}&secret={$oauthToken}" target="_blank">Yotpo Dashboard.</a></div> 
            {else}
              <a class="y-href" href="https://api.yotpo.com/users/sign_in" target="_blank">Yotpo Dashboard.</a></div> 
            {/if}
           {/if}
   {if $allreadyUsingYotpo}<div class="y-settings-title">To get your api key and secret token  
     <a class="y-href" href="https://api.yotpo.com/users/sign_in" target="_blank">log in here.</a></div> 
   {/if}

      {if $finishedRegistration}<div class="y-settings-title">All set! The Yotpo widget is now properly installed on your shop. </br>
    To customize the look and feel of the widget, and to edit your Mail After Purchase settings, just head to the 
      {if $appKey && $appKey != '' && $oauthToken && $oauthToken != ''}
        <a class="y-href" href="https://api.yotpo.com/users/b2blogin?app_key={$appKey}&secret={$oauthToken}" target="_blank">Yotpo Dashboard.</a></div> 
      {else}
        <a class="y-href" href="https://api.yotpo.com/users/sign_in" target="_blank">Yotpo Dashboard.</a></div> 
      {/if}
    {/if}

      <fieldset id="y-fieldset">
        <div class="y-label">{l s='App key' mod='yotpo'}</div>
        <div class="y-input">
          <input type="text" name="yotpo_app_key" value="{$appKey}"/>
        </div>

        <div class="y-label">{l s='Secret token' mod='yotpo'}</div>
        <div class="y-input">
          <input type="text" name="yotpo_oauth_token" value="{$oauthToken}"/>
        </div>

        <div class="y-label">{l s='Mail after purchase' mod='yotpo'}
            <input type="checkbox" name="yotpo_map_enabled" value="yotpo_map_enabled" {if $mapEnabled}checked="checked"{/if} </>
        </div>
        
      </fieldset>
      <div class="y-footer">
        <input type="submit" name="yotpo_settings" value="{l s='Update' mod='yotpo'}" class="y-submit-btn" />
      </div>
    </form>

    </div>

