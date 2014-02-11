{*
* 2013 Ha!*!*y
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* It is available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*
* DISCLAIMER
* This code is provided as is without any warranty.
* No promise of being safe or secure
*
*  @author      Ha!*!*y <ha99ys@gmail.com>
*  @copyright   2013 Ha!*!*y
*  @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{include file="$tpl_dir./errors.tpl"}

<br/>
{if $fbconnect_psb_status == 'error'}
	<div class="error">
		<p>{$fbconnect_psb_massage}{if isset($fbconnect_psb_fb_picture)}<br/><img src="{$fbconnect_psb_fb_picture}">{$fbconnect_psb_fb_name}{/if}</p>
	</div>
{else if $fbconnect_psb_status == 'linked' || $fbconnect_psb_status == 'conform'}
	<div class="success">
		<p>{$fbconnect_psb_massage}<br/><img src="{$fbconnect_psb_fb_picture}">{$fbconnect_psb_fb_name}</p>
	</div>
{else if $fbconnect_psb_status == 'login'}
	<div class="error">
		<p>{$fbconnect_psb_massage}<br/><a href="{$fbconnect_psb_loginURL}">Log in to Facebook</a></p>
	</div>
{else}
	<div class="error">
		<p>Sorry, there was error with Facebook Profile Connect.</p>
	</div>
{/if}
<br/>