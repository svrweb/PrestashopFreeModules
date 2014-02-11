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

class FBConnect_PSB extends Module
{
	public function __construct()
	{
		$this->name = 'FacebookConnect';
		$this->tab = 'social_networks';
		$this->author = 'You';
		$this->version = '1.06b';

		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

		parent::__construct();

		$this->displayName = $this->l('Facebook Connect (OpenID)');
		$this->description = $this->l('This modules allows Customers to Register & Login with Facebook.');

		$this->dbUpdate_output = '';
	}

	public function install()
	{
		// Removed this from install because
		// on reset every time the values were emptied
		// !Configuration::updateValue('FB_CONNECT_APPID', '')
		// !Configuration::updateValue('FB_CONNECT_APPKEY', '')

		// Removed this because we have to use a override
		// $this->registerHook('DisplayTop') == false ||

		if (parent::install() == false ||
			$this->registerHook('displayCustomerAccountFormTop') == false ||
			$this->registerHook('displayCustomerAccount') == false)
				return false;

		return Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'customer_profile_connect` (
			`id_customer` int(10) unsigned NOT NULL,
			`id_shop` int(11) NOT NULL DEFAULT \'1\',
			`facebook_id` varchar(50) NOT NULL,
			UNIQUE KEY `id_customer` (`id_customer`,`id_shop`))
			ENGINE=InnoDB DEFAULT CHARSET=latin1');
	}

	public function uninstall()
	{
		//TODO: see if you have to delete the Hook from install function
		if (!parent::uninstall())
			return false;

		// TODO: Should the table be deleted?
		//return Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'customer_profile_connect`');
		return true;
	}

	public function getContent()
	{
		$errors = array();

		if( $_SERVER["SERVER_NAME"] == "localhost" || $_SERVER["SERVER_NAME"] == "127.0.0.1" )
		{
			$errors[] = $this->l('NOTE: Facebook Connect will not work properly in localhost.');
		}
		if( !Configuration::get('PS_SSL_ENABLED') )
		{
			$errors[] = $this->l('NOTE: Facebook Connect will not work with out SSL.');
		}
		if (!function_exists('curl_init'))
		{
			$errors[] = $this->l('Error: Facebook Connect needs the CURL PHP extension.');
		}
		if (!function_exists('json_decode'))
		{
			$errors[] = $this->l('Error: Facebook Connect needs the JSON PHP extension.');
		}
		if (!function_exists('hash_hmac'))
		{
			$errors[] = $this->l('Error: Facebook Connect needs the HMAC Hash (hash_hmac) PHP extension.');
		}

		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitFBKey'))
		{
			$fb_connect_appid = (Tools::getValue('fb_connect_appid'));
			if (!$fb_connect_appid)
				$errors[] = $this->l('Invalid Facebook AppID');
			else
				Configuration::updateValue('FB_CONNECT_APPID', $fb_connect_appid);
				
			$fb_connect_appkey = (Tools::getValue('fb_connect_appkey'));
			if (!$fb_connect_appkey)
				$errors[] = $this->l('Invalid Facebook App Key');
			else
				Configuration::updateValue('FB_CONNECT_APPKEY', $fb_connect_appkey);
		}
		else if(Tools::isSubmit('submitFBconnect_psb_db_update'))
		{
			$this->dbUpdate_output = $this->_dbUpdate();
		}

		if (isset($errors) AND sizeof($errors))
			$output .= $this->displayError(implode('<br />', $errors));
		else
			$output .= $this->displayConfirmation($this->l('Settings updated'));

		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<p>'.$this->l('Facebook AppID').'</p><br />
				<label>'.$this->l('Facebook AppID').'</label>
				<div class="margin-form">
					<input type="text" size="20" name="fb_connect_appid" value="'.Tools::getValue('fb_connect_appid', Configuration::get('FB_CONNECT_APPID')).'" />

				</div>

				<p>'.$this->l('Your Facebook App Key').'</p><br />
				<label>'.$this->l('Facebook App Key').'</label>
				<div class="margin-form">
					<input type="text" size="40" name="fb_connect_appkey" value="'.Tools::getValue('fb_connect_appkey', Configuration::get('FB_CONNECT_APPKEY')).'" />

				</div>
				<center><input type="submit" name="submitFBKey" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';

		$output .= '<br /><div class="module_confirmation conf">
			Edit authentication.tpl place the link to Facebook login<br />You can put the login links anywhere.<br /><br />
			&lt;a title="Login with your Facebook Account" class="button_large" href="{$link-&gt;getModuleLink(\'fbconnect_psb\', \'login\', array(), true)}"&gt;Facebook Login&lt/a&gt;</br></br>
			</div>';

		if(!empty($this->dbUpdate_output))
			$output .= '<br /><div class="module_confirmation warn">'.$this->dbUpdate_output.'</div><br />';
		
		$output .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend>'.$this->l('DB Update').'</legend>
				<p>'.$this->l('DB Update from v1.0b to 1.06b').'</p><br />
				<center><input type="submit" name="submitFBconnect_psb_db_update" value="'.$this->l('Run DB Update').'" class="button" /></center>
			</fieldset>
		</form>';

		return $output;
	}

	public function hookDisplayTop($params)
	{
/*
		//This does not work need to have a override of Customer.php
		// Have to do this to destroy FB session at logout
		if (isset($_GET['mylogout']))
		{
			require_once(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php');

			// Create our Application instance (replace this with your appId and secret).
			$facebook = new Facebook(array(
				'appId'  => $fb_connect_appid,
				'secret' => $fb_connect_appkey,
			));

			$facebook->destroySession();
		}
*/

		return '';
	}

	public function hookDisplayCustomerAccount($params)
	{
		$this->context->smarty->assign(array(
			'fbconnect_psb_link' => $this->context->link->getModuleLink('fbconnect_psb', 'link', array(), false, $this->context->language->id)
		));

		return $this->display(__FILE__, 'customer-account.tpl');
	}

	public function hookDisplayCustomerAccountFormTop($params)
	{
		$this->context->smarty->assign(array(
			'fbconnect_psb_reg_link' => $this->context->link->getModuleLink('fbconnect_psb', 'registration', array(), true, $this->context->language->id)
		));

		return $this->display(__FILE__, 'customer-account-form-top.tpl');
	}

	//TODO: find a way to hook to the login (Authentication) page
/*
	public function hookDisplayAuthenticationFormTop($params)
	{
		return 'hookDisplay AuthenticationFormTop';
	}
*/

	function _dbUpdate()
	{
		$output = '';
	
		if(Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'customer_profile_connect` CHANGE `id_customer` `id_customer` INT( 10 ) UNSIGNED NOT NULL'))
			$output .= 'Removed AUTO INCREMENT</br>';
		else
			$output .= 'Error: could not remove AUTO INCREMENT</br>';

		if(Db::getInstance()->ExecuteS('SHOW INDEX FROM `'._DB_PREFIX_.'customer_profile_connect` WHERE Key_name = \'PRIMARY\''))
		{
			if(Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'customer_profile_connect` DROP PRIMARY KEY'))
				$output .= 'Droped the primary key</br>';
			else
				$output .= 'Error: Could not drop primary key</br>';
		}
		else
		{
			$output .= 'Note: Did not drop primary key because could not find any.</br>';
		}

		if(!Db::getInstance()->ExecuteS('SHOW INDEX FROM `'._DB_PREFIX_.'customer_profile_connect` WHERE Key_name = \'id_customer\''))
		{
			if(Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'customer_profile_connect` ADD UNIQUE (`id_customer` , `id_shop`)'))
				$output .= 'Added a new UNIQUE id_customer & id_shop</br>';
			else
				$output .= 'Error: Did not add UNIQUE id_customer & id_shop</br>';
		}
		else
		{
			$output .= 'Note: Index was already set to id_customer</br>';
		}
		
		return $output;
	}
}