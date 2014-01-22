{assign var=fcbc_appid value=$var['fcbc_appid']}
{assign var=fcbc_admins value=$var['fcbc_admins']}
{assign var=fcbc_lang value=$var['fcbc_lang']}
{assign var=fcbc_appid value=$var['fcbc_appid']} 

<meta property="fb:app_id" content="{$fcbc_appid}"/><meta property="fb:admins" content="{$fcbc_admins}"/><div id="fb-root"></div>
<script>{literal}(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/{/literal}{$fcbc_lang}{literal}/all.js#xfbml=1&appId={/literal}{$fcbc_appid}{literal}";
  fjs.parentNode.insertBefore(js, fjs);
}{/literal}(document, 'script', 'facebook-jssdk'));</script>