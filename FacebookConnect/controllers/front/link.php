<?php
/*
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
* No promise of safety or security.
*
*  @author          Ha!*!*y <ha99ys@gmail.com>
*  @copyright       2013 Ha!*!*y
*  @license         http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
  exit;

class FBConnect_PSBLinkModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	public $ssl = true;
 
	/**
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		parent::initContent();
 
		if (!$this->context->customer->isLogged())
		{
			$back = $this->context->link->getModuleLink('fbconnect_psb', 'link', array(), TRUE, $this->context->language->id);
			Tools::redirect('index.php?controller=authentication&back='.urlencode($back));

		}

		$fb_connect_appid = (Configuration::get('FB_CONNECT_APPID'));
		$fb_connect_appkey = (Configuration::get('FB_CONNECT_APPKEY'));

		require_once(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php');

		$facebook = new Facebook(array(
			'appId'  => $fb_connect_appid,
			'secret' => $fb_connect_appkey,
		));

		// Get User ID
		$user = $facebook->getUser();

		if ($user)
		{
			try {
				// Proceed knowing you have a logged in user who's authenticated.
				$fb_user_profile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
			}
		}
		else
		{
			// Get new Access tokens
			Tools::redirect($facebook->getLoginUrl(array('scope' => 'email')));
		}

		// current user state Logged In with FB
		if (!$user || !$fb_user_profile['id'])
		{
			// Get new Access tokens
			Tools::redirect($facebook->getLoginUrl(array('scope' => 'email')));
		}

			$sql = 'SELECT `id_customer`
				FROM `'._DB_PREFIX_.'customer_profile_connect`
				WHERE `facebook_id` = \''.(int)$fb_user_profile['id'].'\''
					. Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

			$customer_id = Db::getInstance()->getValue($sql);

			if ($customer_id > 0 && $customer_id != $this->context->customer->id)
			{
				$this->context->smarty->assign(array(
					'fbconnect_psb_status' => 'error',
					'fbconnect_psb_massage' => 'The Facebook account is already linked to another account.',
					'fbconnect_psb_fb_picture' => 'https://graph.facebook.com/'.$fb_user_profile['username'].'/picture',
					'fbconnect_psb_fb_name' => $fb_user_profile['name']
				));
			}
			else if ($customer_id == $this->context->customer->id)
			{
				$this->context->smarty->assign(array(
					'fbconnect_psb_status' => 'linked',
					'fbconnect_psb_massage' => 'The Facebook account is already linked to your account.',
					'fbconnect_psb_fb_picture' => 'https://graph.facebook.com/'.$fb_user_profile['username'].'/picture',
					'fbconnect_psb_fb_name' => $fb_user_profile['name']
				));
			}
			else
			{
				if($fb_user_profile['email'] != $this->context->customer->email)
				{
					// The message
					$message = 'Email address on files was not the same as the Facebook account.';
					$message .= 'customer ID: '. print_r($this->context->customer->id, true);
					$message .= "\n\n";
					$message .= 'user info:'. print_r($fb_user_profile, true);
					$message .= "\n\n";

					// In case any of our lines are larger than 70 characters, we should use wordwrap()
					$message = wordwrap($message, 70, "\r\n");
					@mail(Configuration::get('PS_SHOP_EMAIL'), 'fbconnect_psb: error #1 log', $message);
				}

			$sql = 'SELECT `facebook_id`
				FROM `'._DB_PREFIX_.'customer_profile_connect`
				WHERE `id_customer` = \''.(int)$this->context->customer->id.'\' AND `id_shop` = '
					. (int)$this->context->getContext()->shop->id;

			$facebook_id = Db::getInstance()->getValue($sql);

				if(!$facebook_id)
				{
					Db::getInstance()->insert('customer_profile_connect',array( 'id_customer' => (int)$this->context->customer->id, 'facebook_id' => (int)$fb_user_profile['id']));

					$this->context->smarty->assign(array(
						'fbconnect_psb_status' => 'conform',
						'fbconnect_psb_massage' => 'Your Facebook account has been linked to account.',
						'fbconnect_psb_fb_picture' => 'https://graph.facebook.com/'.$fb_user_profile['username'].'/picture',
						'fbconnect_psb_fb_name' => $fb_user_profile['name']
					));
				}
				else
				{
// This could happen if the user logged off from FB but not the prestashop
// And 2nd user logs in to facebook than opens this page.
					$this->context->smarty->assign(array(
						'fbconnect_psb_status' => 'error',
						'fbconnect_psb_massage' => 'Sorry, there was a error when we tried to link your account with Facebook. Our Site admin has been notified of error, once it\'s resolved you will be sent a email notice.',
					));

					// The message
					$message = 'customer ID: '. print_r($this->context->customer->id, true);
					$message .= "\n\n";
					$message .= 'user info:'. print_r($fb_user_profile, true);
					$message .= "\n\n";

					// In case any of our lines are larger than 70 characters, we should use wordwrap()
					$message = wordwrap($message, 70, "\r\n");
					@mail(Configuration::get('PS_SHOP_EMAIL'), 'fbconnect_psb: error #2 log', $message);
				}
			}

		$this->setTemplate('link_fb.tpl');
	}
}