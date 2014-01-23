<div class="y-wrapper">
  <div class="y-side-box">
    <div class="y-side-header">
      Yotpo makes it easy to generate beautiful reviews for your products. These in turn lead to higher sales and happier customers.
     </div>
       <hr>
      <div class="row-fluid y-features-list text-shadow">
        <ul>
          <li><i class="y-side-icon conversation-rate"></i>Increase conversion rate</li>
          <li><i class="y-side-icon multi-languages"></i>Multi languages</li>
          <li><i class="y-side-icon forever-free"></i>Forever free</li>
          <li><i class="y-side-icon social-engagement"></i>Increase social engagement</li>
          <li><i class="y-side-icon plug-play"></i>Plug &amp; play installation</li>
          <li><i class="y-side-icon full-customization"></i>Full customization</li>
          <li><i class="y-side-icon analytics"></i>Advanced analytics</li>
          <li><i class="y-side-icon seo"></i>SEO capabilities</li>
        </ul>
      </div>
    </div>
  <div class="y-white-box">
    <form action="{$action}" method="post">
      <div class="y-page-header">
        <i class="y-logo"></i>Create your Yotpo account</div>
      <fieldset id="y-fieldset">
        <div class="y-header">Generate more reviews, more engagement, and more sales. </div>
        <div class="y-label">{l s='Email address: ' mod='yotpo'}</div>
        <div class="y-input">
          <input type="text" name="yotpo_user_email" value="{$email}"/>
        </div>
        <div class="y-label">{l s='Name' mod='yotpo'}</div>
        <div class="y-input">
          <input type="text" name="yotpo_user_name" value="{$userName}"/>
        </div>
        <div class="y-label">{l s='Password' mod='yotpo'}</div>
        <div class="y-input">
          <input type="password" name="yotpo_user_password"/>
        </div>
        <div class="y-label">{l s='Confirm password' mod='yotpo'}</div>
        <div class="y-input">
          <input type="password" name="yotpo_user_confirm_password"/>
        </div>
      </fieldset>
      <div class="y-footer">
        <input type="submit" name="yotpo_register" value="{l s='Register' mod='yotpo'}" class="y-submit-btn" />
      </div>
    </form>
    <form action="{$action}" method="post">
      <div class="y-footer">
        Already using Yotpo? <input type="submit" name="log_in_button" value="click here" class="y-already-logged-in" />
      </div>
    </form>
  </div>
</div>

