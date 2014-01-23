<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class Yotpo extends Module
{

  private $_html = '';
  private $_httpClient = NULL;
  public function __construct()
    {
      // version test
      $version_mask = explode('.', _PS_VERSION_, 3);
      $version_test = $version_mask[0] > 0 && $version_mask[1] > 4;

      $this->name = 'yotpo';
      $this->tab = $version_test ? 'advertising_marketing' : 'Reviews';
      $this->version = '1.0.2';
      if($version_test)
        $this->author = 'Yotpo';
      $this->need_instance = 1;
 
      parent::__construct();
   
      $this->displayName = $this->l('* Yotpo *');
      $this->description = $this->l('The #1 reviews add-on for SMBs. Generate beautiful, trusted reviews for your shop.');

      include_once(_PS_MODULE_DIR_.'yotpo/httpClient.php');
      if(!Configuration::get('yotpo_app_key'))
        $this->warning = $this->l('Set your api key in order to use this module correctly');
      $this->_httpClient = new YotpoHttpClient($this->name);
    }
 
  public function install()
  {
  
    $is_curl_installed = true;
    if (!function_exists('curl_init'))
    {
      $is_curl_installed = false;
      if (isset($this->_errors))
        $this->_errors[] = $this->l('Yotpo needs the PHP Curl extension, please ask your hosting provider to enable it prior to install this module.');
    }
    if (!$is_curl_installed || parent::install() == false OR !$this->registerHook('productfooter') 
                                                          OR !$this->registerHook('postUpdateOrderStatus')) {
      return false;  
    }
    return true;
  }

  public function hookproductfooter($params)
  {

    global $smarty;
    $product = $params['product'];
    $smarty->assign('yotpoAppkey', Configuration::get('yotpo_app_key'));
    $smarty->assign('yotpoProductId', $product->id);
    $smarty->assign('yotpoProductName', strip_tags($product->name));
    $smarty->assign('yotpoProductDescription', strip_tags($product->description));
    $smarty->assign('yotpoDomain', $this->_getShopDomain());
    $smarty->assign('yotpoProductModel', $this->_getProductModel($product));
    $smarty->assign('yotpoProductImageUrl', $this->_getProductImageUrl($product->id));
    $smarty->assign('yotpoProductBreadCrumbs', $this->_getBreadCrumbs($product));

    // TODO check if can insert this in header part so it will be loaded only once
    echo "<script src ='http://www.yotpo.com/js/yQuery.js'></script>";
    return $this->display(__FILE__,'tpl/widgetDiv.tpl');
  }

  public function hookpostUpdateOrderStatus($params)
  { 
    $accepted_status = array(defined('PS_OS_WS_PAYMENT') ? (int)Configuration::get(PS_OS_WS_PAYMENT) : _PS_OS_WS_PAYMENT_,
                             defined('PS_OS_PAYMENT') ? (int)Configuration::get(PS_OS_PAYMENT) : _PS_OS_PAYMENT_,
                             defined('PS_OS_DELIVERED') ? (int)Configuration::get(PS_OS_DELIVERED) : _PS_OS_DELIVERED_,
                             defined('PS_OS_SHIPPING') ? (int)Configuration::get(PS_OS_SHIPPING) : _PS_OS_SHIPPING_);
    if(in_array($params['newOrderStatus']->id, $accepted_status))
    {
      $app_key = Configuration::get('yotpo_app_key');
      $secret = Configuration::get('yotpo_oauth_token');
      $enable_feature = Configuration::get('yotpo_map_enabled');   

      if((isset($app_key)) AND (isset($secret)) AND $enable_feature == "1")
        $this->_httpClient->makeMapRequest($params, $app_key, $secret, $this);
    }
  }
  
  private function _getShopDomain()
  {
    if(method_exists('Tools', 'getShopDomain'))
      return Tools::getShopDomain(false,false);
    return str_replace('www.', '', $_SERVER['HTTP_HOST']);;
  }

  public function _getProductImageUrl($id_product)
  {
    $id_image = Product::getCover($id_product);
    // get Image by id
    if (sizeof($id_image) > 0) {
        $image = new Image($id_image['id_image']);
        // get image full URL

        return $image_url = method_exists($image, 'getExistingImgPath') ? _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg" : $this->getExistingImgPath($image);
    }  
    return NULL;
  }


  public function getExistingImgPath($image)
  {
    if (!$image->id)
      return NULL;
    if (file_exists(_PS_PROD_IMG_DIR_.$image->id_product.'-'.$image->id.'.jpg'))
      return _PS_BASE_URL_._THEME_PROD_DIR_.$image->id_product.'-'.$image->id.'.'.'jpg';     
  }

  public function getCurrency($id_order)
  {
        $id_currency = (int)Db::getInstance()->getValue('
        SELECT id_currency
        FROM '._DB_PREFIX_.'orders
        WHERE id_order = '.(int)$id_order);        
        return Currency::getCurrency($id_currency);
        
  }
  
  public function getOrderDetails($id_order)
  {
    if(method_exists('OrderDetail', 'getList'))
      return OrderDetail::getList($id_order);
    else
      return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'order_detail` WHERE `id_order` = '.(int)$id_order);  
  }

  public function uninstall()
  {
    Configuration::deleteByName('yotpo_app_key');
    Configuration::deleteByName('yotpo_oauth_token');
    Configuration::deleteByName('yotpo_map_enabled');
    return parent::uninstall();
  }

// module configuration
  public function getContent()
  {
    if (!function_exists('curl_init'))
      return '<div class="error">'.$this->l('Yotpo needs the PHP Curl extension, please ask your hosting provider to enable it prior to use this module.').'</div>';
    
    if(Configuration::get('yotpo_map_enabled') == NULL)
    {
      Configuration::updateValue('yotpo_map_enabled', '1', false);
      echo ' 
        <script type="text/javascript">
        var prefix ="";
        if (typeof _gaq != "object") {
          window["_gaq"] = [];
          _gaq.push(["_setAccount", "UA-25706646-2"]);
          (function() {
            var ga = document.createElement("script");
            ga.type = "text/javascript";
            ga.async = true;
            ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(ga, s);
          })();
        } else {
          prefix = "t2.";
          _gaq.push([prefix + "_setAccount", "UA-25706646-2"]);
        }
        _gaq.push([prefix + "_trackEvent", "prestashop", "install"]);
        </script>';
    }

    if(isset($this->context) && isset($this->context->controller) && method_exists($this->context->controller, 'addCSS'))
      $this->context->controller->addCSS($this->_path.'/css/form.css', 'all');
    else
      echo '<link rel="stylesheet" type="text/css" href="../modules/yotpo/css/form.css" />'; 
    $this->_processRegistrationForm();
    $this->_processSettingsForm();
    $this->_displayForm();
    return $this->_html;
  }


  private function _processRegistrationForm()
  {
    if (Tools::isSubmit('yotpo_register'))
    {
      $email = Tools::getValue('yotpo_user_email');
      $name = Tools::getValue('yotpo_user_name');
      $password = Tools::getValue('yotpo_user_password');
      $confirm = Tools::getValue('yotpo_user_confirm_password');
      if ($email === false || $email === '')
        return $this->_prepareError($this->l('Provide valid email address'));
      if(strlen($password) < 6 || strlen($password) > 128)
        return $this->_prepareError($this->l('Password must be at least 6 characters'));

      if ($password != $confirm)
        return $this->_prepareError($this->l('Passwords are not identical'));

      if ($name === false || $name === '')
        return $this->_prepareError($this->l('Name is missing'));
      $is_mail_valid = $this->_httpClient->checkeMailAvailability($email);

      if($is_mail_valid['status']['code'] == 200 && $is_mail_valid['response']['available'] == true)
      {
        $response = $this->_httpClient->register($email, $name, $password, _PS_BASE_URL_);      
        if($response['status']['code'] == 200)
        {
          $accountPlatformResponse = $this->_httpClient->createAcountPlatform($response['response']['app_key'], $response['response']['secret'], _PS_BASE_URL_);        
          if($accountPlatformResponse['status']['code'] == 200)
          {
            Configuration::updateValue('yotpo_app_key', $response['response']['app_key'], false);
            Configuration::updateValue('yotpo_oauth_token', $response['response']['secret'], false);
            return $this->_prepareSuccess($this->l('Account successfully created'));  
          }
          else
            return $this->_prepareError($response['status']['message']);  
          
        } 
        else
        {        
          return $this->_prepareError($response['status']['message']);        
        } 
      }
      else
      {
        if($is_mail_valid['status']['code'] == 200 )
          return $this->_prepareError('This mail is allready taken.');
        else
          return $this->_prepareError();
      }  
    }
  }

  private function _processSettingsForm()
  {
    if (Tools::isSubmit('yotpo_settings'))
    {
      
      $api_key = Tools::getValue('yotpo_app_key');
      $secret_token = Tools::getValue('yotpo_oauth_token');
      $map_enabled = Tools::getValue('yotpo_map_enabled');
      if($api_key == '')
        return $this->_prepareError($this->l('Api key is missing'));
      if($map_enabled && $secret_token == '')
        return $this->_prepareError($this->l('Please fill out the secret token'));

      $yotpo_map_enabled = Tools::getValue('yotpo_map_enabled') == false ? "0" : "1";
      Configuration::updateValue('yotpo_map_enabled', $yotpo_map_enabled, false);
      Configuration::updateValue('yotpo_app_key', Tools::getValue('yotpo_app_key'), false);
      Configuration::updateValue('yotpo_oauth_token', Tools::getValue('yotpo_oauth_token'), false);
      return $this->_prepareSuccess();
    }
  }

  private function _displayForm()
  {
    global $smarty;
    $smarty->assign('finishedRegistration', false);
    $smarty->assign('allreadyUsingYotpo', false);
    if (Tools::isSubmit('log_in_button'))
    {
      $smarty->assign('allreadyUsingYotpo', true);
      return $this->_displaySettingsForm();
    }
    if (Tools::isSubmit('yotpo_register'))
    {
      global $smarty;
      $smarty->assign('finishedRegistration', true);
    }
    return Configuration::get('yotpo_app_key') == '' ? $this->_displayRegistrationForm() : $this->_displaySettingsForm();
  }

  private function _displayRegistrationForm()
  {
    global $smarty;
    $smarty->assign(array(
        'action' => Tools::safeOutput($_SERVER['REQUEST_URI']),
        'email' => Tools::safeOutput(Tools::getValue('yotpo_user_email')),
        'userName' => Tools::safeOutput(Tools::getValue('yotpo_user_name'))));

    $this->_html .= $this->display(__FILE__, 'tpl/registrationForm.tpl');
    return $this->_html;
  }

  private function _displaySettingsForm()
  {
    global $smarty;
    $smarty->assign(array(
        'action' => Tools::safeOutput($_SERVER['REQUEST_URI']),
        'appKey' => Tools::safeOutput(Tools::getValue('yotpo_app_key',Configuration::get('yotpo_app_key'))),
        'oauthToken' => Tools::safeOutput(Tools::getValue('yotpo_oauth_token',Configuration::get('yotpo_oauth_token'))),
        'mapEnabled' => Configuration::get('yotpo_map_enabled') == "0" ? false : true));
    
    $this->_html .= $this->display(__FILE__, 'tpl/settingsForm.tpl');
  }

  private function _getProductModel($product)
  {    
    if(Validate::isEan13($product->ean13))
    {
      return $product->ean13;
    }
    else if(Validate::isUpc($product->upc))
    {
      return $product->upc;  
    }
    return NULL;
  }

  private function _getBreadCrumbs($product)
  {
   if (!method_exists('Product', 'getProductCategoriesFull'))
    return ''; 
   $result = '';
   $all_product_subs = Product::getProductCategoriesFull($product->id, $this->context->language->id);
   $all_product_subs_path = array();
   if(isset($all_product_subs) && count($all_product_subs)>0)
   {
      foreach($all_product_subs as $subcat)
      {
        $sub_category = new Category($subcat['id_category'], $this->context->language->id);
        $sub_category_path = $sub_category->getParentsCategories();
        foreach ($sub_category_path as $key) {
          $result .= ''.$key['name'].';';  
        }
        $result .= ',';  
      }
   }
   if($result[strlen($result)-1] == ',')
   {
     $result = substr_replace($result ,"",-1); 
   }
   return $result;
  }

  private function _prepareError($message = '')
  {
    $this->_html .= sprintf('<div class="alert">%s</div>', $message == '' ? $this->l('Error occured') : $message);
  }

  protected function _prepareSuccess($message = '')
  {
    $this->_html .= sprintf('<div class="conf confirm">%s</div>', $message == '' ? $this->l('Settings updated') : $message);
  }
}
?>