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

class FBConnect_PSBLoginModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	public $ssl = true;
 
	/**
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		parent::initContent();
 
		$fb_connect_appid = (Configuration::get('FB_CONNECT_APPID'));
		$fb_connect_appkey = (Configuration::get('FB_CONNECT_APPKEY'));

		$this->login_url = $this->context->link->getModuleLink('fbconnect_psb', 'login', array(), TRUE, $this->context->language->id);

		require_once(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php');

		$facebook = new Facebook(array(
			'appId'  => $fb_connect_appid,
			'secret' => $fb_connect_appkey,
		));

		// Get User ID
		$user = $facebook->getUser();

		// We may or may not have this data based on whether the user is logged in.
		//
		// If we have a $user id here, it means we know the user is logged into
		// Facebook, but we don't know if the access token is valid. An access
		// token is invalid if the user logged out of Facebook.

		if ($user)
		{
			try {
				// Proceed knowing you have a logged in user who's authenticated.
				$fb_user_profile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				//die('Error: '.$e);
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
		if ($user)
		{
			//get the user email from DB with FB ID
			$sql = 'SELECT c.`email`
				FROM `'._DB_PREFIX_.'customer` c
					LEFT JOIN `'._DB_PREFIX_.'customer_profile_connect` pc ON pc.id_customer = c.id_customer
				WHERE pc.`facebook_id` = '.(int)$fb_user_profile['id'] . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c');

			$email = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

			if (empty($email))
			{
				Tools::redirect($this->context->link->getModuleLink('fbconnect_psb', 'registration', array(), TRUE, $this->context->language->id));
			}
			else
			{
				$customer = new Customer();
				$authentication = $customer->getByEmail(trim($email));
				if (!$authentication || !$customer->id)
				{
					$this->errors[] = Tools::displayError('Error: Authentication failed.');
				}
				else
				{
					$this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
					$this->context->cookie->id_customer = (int)($customer->id);
					$this->context->cookie->customer_lastname = $customer->lastname;
					$this->context->cookie->customer_firstname = $customer->firstname;
					$this->context->cookie->logged = 1;
					$customer->logged = 1;
					$this->context->cookie->is_guest = $customer->isGuest();
					$this->context->cookie->passwd = $customer->passwd;
					$this->context->cookie->email = $customer->email;

					// Add customer to the context
					$this->context->customer = $customer;

					if (Configuration::get('PS_CART_FOLLOWING') && (empty($this->context->cookie->id_cart) || Cart::getNbProducts($this->context->cookie->id_cart) == 0) && $id_cart = (int)Cart::lastNoneOrderedCart($this->context->customer->id))
						$this->context->cart = new Cart($id_cart);
					else
					{
						$this->context->cart->id_carrier = 0;
						$this->context->cart->setDeliveryOption(null);
						$this->context->cart->id_address_delivery = Address::getFirstCustomerAddressId((int)($customer->id));
						$this->context->cart->id_address_invoice = Address::getFirstCustomerAddressId((int)($customer->id));
					}
					$this->context->cart->id_customer = (int)$customer->id;
					$this->context->cart->secure_key = $customer->secure_key;
					$this->context->cart->save();
					$this->context->cookie->id_cart = (int)$this->context->cart->id;
					$this->context->cookie->update();
					$this->context->cart->autosetProductAddress();

					Hook::exec('actionAuthentication');

					// Login information have changed, so we check if the cart rules still apply
					CartRule::autoRemoveFromCart($this->context);
					CartRule::autoAddToCart($this->context);

					if ($back = Tools::getValue('back'))
						Tools::redirect(html_entity_decode($back));
					else
					{
						Tools::redirect('index.php?controller='.(($this->authRedirection !== false) ? url_encode($this->authRedirection) : 'my-account'));
					}
				}
			}

			$this->context->smarty->assign(array(
				'redirect_uri'     => urlencode($this->login_url),
				'fb_connect_appid' => $fb_connect_appid,
				'fb_connect_error' => $this->errors
			));

			$this->setTemplate('login_fb.tpl');
		}
		else
		{
			if(isset($_GET['error']) && isset($_GET['error_code']))
			{
				$msg = 'There was error while trying to get information from Facebook.';
				$msg .= '<br>'. $_GET['error'] .' - '. $_GET['error_code'] .' - '. $_GET['error_description'] .' - '. $_GET['error_reason'];

				$this->errors[] = Tools::displayError($msg);
				$this->setTemplate('login_fb.tpl');
			}
			else
			{
				Tools::redirect($facebook->getLoginUrl(array('scope' => 'email')));
			}
		}
	}
}