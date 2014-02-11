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

class FBConnect_PSBRegistrationModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	public $ssl = true;
 
	/**
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		parent::initContent();

		if ($this->context->customer->isLogged())
		{
			Tools::redirect('index.php?controller=my-account');
		}

		$fb_connect_appid = (Configuration::get('FB_CONNECT_APPID'));
		$fb_connect_appkey = (Configuration::get('FB_CONNECT_APPKEY'));

		$this->redirect_uri = $this->context->link->getModuleLink('fbconnect_psb', 'registration', array('done' => 1), TRUE, $this->context->language->id);

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

		// if user's FB account is linked than log the user in
		if (isset($fb_user_profile['id']))
		{
			$sql = 'SELECT `id_customer`
				FROM `'._DB_PREFIX_.'customer_profile_connect`
				WHERE `facebook_id` = \''.(int)$fb_user_profile['id'].'\''
					. Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

			if (Db::getInstance()->getValue($sql))
			{
				Tools::redirect($this->context->link->getModuleLink('fbconnect_psb', 'login', array(), TRUE, $this->context->language->id));
			}
		}

		if (Tools::getValue('done'))
		{
			$response = $facebook->getSignedRequest($_REQUEST['signed_request']);

			$reg_metadata_fields = '[{"name":"name"},{"name":"first_name"},{"name":"last_name"},{"name":"email"},{"name":"password"},{"name":"birthday"},{"name":"gender"}]';

			$reg_metadata_fields_clean = preg_replace('/\s+/', '', $reg_metadata_fields);
			$response_metadata_fields_clean = preg_replace('/\s+/', '', $response['registration_metadata']['fields']);
			if (strcmp($reg_metadata_fields_clean,$response_metadata_fields_clean) != 0)
				$this->errors[] = Tools::displayError('registration metadata fields not valid');
			$response_email = trim($response['registration']['email']);

			if (empty($response_email))
				$this->errors[] = Tools::displayError('An email address required.');
			else if (!Validate::isEmail($response_email))
				$this->errors[] = Tools::displayError('Invalid email address.');
			else if (Customer::customerExists($response_email))
			{
				// Need to clean up the code here most of it is from
				// IDFBCon_v.0.2 (Chandra R. Atmaja <chandra.r.atmaja@gmail.com>)
				// Someone has already registered with this e-mail address
				// This will link the 1st existing email/account on site with Facebook 
				// and log the user in to the account. Is this safe?

				$customer = new Customer();
				$authentication = $customer->getByEmail($response['registration']['email']);

				// This is done to see if a existing users try's to re-registrar
				$sql = 'SELECT `facebook_id`
					FROM `'._DB_PREFIX_.'customer_profile_connect`
					WHERE `id_customer` = \''.(int)$customer->id.'\' '
						. Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

				$customer_fb_id = Db::getInstance()->getValue($sql);

				if($customer_fb_id)
				{
					if($customer_fb_id == (int)$response['user_id'])
						Tools::redirect($this->context->link->getModuleLink('fbconnect_psb', 'login', array(), false, $this->context->language->id));
					else
						$this->errors[] = Tools::displayError('An error occurred while linking your Facebook account.');
				}
				else
				{
					if(Db::getInstance()->insert('customer_profile_connect',array( 'id_customer' => (int)$customer->id, 'facebook_id' => (int)$response['user_id'])))
						$this->errors[] = Tools::displayError('an error occurred while linking your Facebook account.');

					$customer->active = 1;
					$customer->deleted = 0;
					$this->context->cookie->id_customer = intval($customer->id);
					$this->context->cookie->customer_lastname = $customer->lastname;
					$this->context->cookie->customer_firstname = $customer->firstname;
					$this->context->cookie->logged = 1;
					$this->context->cookie->passwd = $customer->passwd;
					$this->context->cookie->email = $customer->email;
					if (Configuration::get('PS_CART_FOLLOWING') AND (empty($this->context->cookie->id_cart) OR Cart::getNbProducts($this->context->cookie->id_cart) == 0))
						$this->context->cookie->id_cart = intval(Cart::lastNoneOrderedCart(intval($customer->id)));

					Module::hookExec('authentication');

					if ($back = Tools::getValue('back'))
						Tools::redirect($back);
					Tools::redirect('index.php?controller=my-account');
				}
			}

			if (!sizeof($this->errors))
			{
				// TODO: use this->context for customer instead of new object?
				// Need to clean up the code here most of it is from
				// IDFBCon_v.0.2 (Chandra R. Atmaja <chandra.r.atmaja@gmail.com>)

				$customer = new Customer();
				$customer_birthday = explode('/',$response['registration']['birthday']);
				$customer->birthday = intval($customer_birthday[2]).'-'.intval($customer_birthday[0]).'-'.intval($customer_birthday[1]);
				if ($response['registration']['last_name'] == "male")
					$_POST['id_gender'] = 1;
				else if ($response['registration']['last_name'] == "female")
					$_POST['id_gender'] = 2;
				else
					$_POST['id_gender'] = 0;
				$_POST['lastname'] = $response['registration']['last_name'];
				$_POST['firstname'] = $response['registration']['first_name'];
				$_POST['passwd'] = $response['registration']['password'];
				$_POST['email'] = $response['registration']['email'];
				$this->errors = $customer->validateControler();

				if (!sizeof($this->errors))
				{
					$customer->active = 1;
					if (!$customer->add())
						$this->errors[] = Tools::displayError('an error occurred while creating your account');
					else
					{
						if(Db::getInstance()->insert('customer_profile_connect',array( 'id_customer' => (int)$customer->id, 'facebook_id' => (int)$response['user_id'])))
							$this->errors[] = Tools::displayError('an error occurred while linking your Facebook account.');

						$email_var = array('{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{email}' => $customer->email, '{passwd}' => $response['registration']['password']);

						if (!Mail::Send(intval($this->context->cookie->id_lang), 'account', 'Welcome!', $email_var, $customer->email, $customer->firstname.' '.$customer->lastname))
							$this->errors[] = Tools::displayError('cannot send email');

						$this->context->smarty->assign('confirmation', 1);
						$this->context->cookie->id_customer = intval($customer->id);
						$this->context->cookie->customer_lastname = $customer->lastname;
						$this->context->cookie->customer_firstname = $customer->firstname;
						$this->context->cookie->passwd = $customer->passwd;
						$this->context->cookie->logged = 1;
						$this->context->cookie->email = $customer->email;

						Module::hookExec('createAccount', array(
							'_POST' => $_POST,
							'newCustomer' => $customer
						));

						if ($back)
							Tools::redirect($back);
						Tools::redirect('index.php?controller=my-account');
					}
				}
			}
		}

		$useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;

		$this->context->smarty->assign(array(
			'redirect_uri'     => $this->redirect_uri,
			'protocol_content' => ($useSSL) ? 'https://' : 'http://',
			'fb_connect_appid' => $fb_connect_appid,
		));

		$this->setTemplate('registration_fb.tpl');
	}
}