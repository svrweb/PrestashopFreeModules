<?php

class Customer extends CustomerCore
{
	/**
	 * Logout
	 *
	 * @since 1.5.0
	 */
	public function logout()
	{
		if (file_exists(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php'))
		{
			include(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php');
			$facebook = new Facebook(array(
				'appId'  => Configuration::get('FB_CONNECT_APPID'),
				'secret' => Configuration::get('FB_CONNECT_APPKEY'),
			));
			$facebook->destroySession();
		}

		parent::logout();
	}

	/**
	 * Soft logout, delete everything links to the customer
	 * but leave there affiliate's informations
	 *
	 * @since 1.5.0
	 */
	public function mylogout()
	{
		if (file_exists(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php'))
		{
			include(_PS_ROOT_DIR_.'/modules/fbconnect_psb/fb_sdk/facebook.php');
			$facebook = new Facebook(array(
				'appId'  => Configuration::get('FB_CONNECT_APPID'),
				'secret' => Configuration::get('FB_CONNECT_APPKEY'),
			));
			$facebook->destroySession();
		}

		parent::mylogout();
	}
}
