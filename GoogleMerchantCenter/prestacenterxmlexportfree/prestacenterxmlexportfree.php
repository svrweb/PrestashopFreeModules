<?php
/*
*
* 2012-2013 PrestaCS
*
* Module PrestaCenter XML Export Free – version for PrestaShop 1.5.x
* Modul PrestaCenter XML Export Free – verze pro PrestaShop 1.5.x
* 
* @author PrestaCS <info@prestacs.com>
* PrestaCenter XML Export Free (c) copyright 2012-2013 PrestaCS - Anatoret plus s.r.o.
* 
* PrestaCenter - modules and customization for PrestaShop
* PrestaCS - moduly, česká lokalizace a úpravy pro PrestaShop
*
* http://www.prestacs.cz
* 
*/

if (!defined('_PS_VERSION_'))
	exit;
class PcXmlFreeAvailabilityException extends PrestaShopModuleException { }
class PrestaCenterXmlExportFree extends Module
{
	const CONTEXT_ALL = 1;
	const CONTEXT_FILE = 2;
	const CONTEXT_ITEM = 3;
	const CFG_PREFIX = 'PC_XMLFREE_';
	const XMLTPL_BLOCK = 'ps_block';
	protected $controllerClass;
	protected $exportFiles = array();
	protected $exportInfo = array();
	protected $languages = array();
	protected $currencies = array();
	protected $categories = array();
	protected $allowedProperties = array(
		'id'				=> array(),
		'name'				=> array('key' => 'id_lang', 'helper' => 'clean|escape'),
		'ean'				=> array(),
		'upc'				=> array(),
		'description'		=> array('key' => 'id_lang', 'helper' => 'clean|escape'),
		'description_short' => array('key' => 'id_lang', 'helper' => 'clean|escape'),
		'manufacturer'		=> array('helper' => 'escape|strip'),
		'categories'		=> array('key' => 'id_lang', 'helper' => 'clean|escape'),
		'price_vat'			=> array('key' => 'id_currency', ),
		'price_vat_local'	=> array('key' => 'id_currency', ),
		'price_vat_iso'		=> array('key' => 'id_currency', ),
		'condition'			=> array('helper' => 'clean|escape'),
		'online_only'		=> array(),
		'url'				=> array('key' => 'id_lang', 'helper' => 'escape|strip', ),
		'img_url'			=> array('key' => 'id_lang', 'helper' => 'escape|strip', ),
		'days'				=> array('key' => 'id_lang'),
		'availability'		=> array('helper' => 'availability|clean|escape'),
		'reference'			=> array('helper' => 'clean|escape', ),
		'supplier_reference'=> array('helper' => 'clean|escape', ),
		'update_feed'		=> array('context' => self::CONTEXT_ALL, 'helper' => 'ftime'),
		'update_item'		=> array('helper' => 'ftime'),
		'shop_name'			=> array('context' => self::CONTEXT_ALL, 'helper' => 'escape|strip', ),
		'shop_url'			=> array('context' => self::CONTEXT_ALL, 'helper' => 'escape|strip', ),
		'lang_code'			=> array('context' => self::CONTEXT_ALL, 'key' => 'id_lang'),
		'lang_code_iso'		=> array('context' => self::CONTEXT_ALL, 'key' => 'id_lang'),
	);
	protected $tplDir = '';
	protected $exportDir = '';
	protected $moduleUrl = '';
	protected $commonTplData = array();
	protected $tempExt = '.tmp';
	protected $template;
	protected $installerData = array(
		'files' => array(),
		'rollback' => array(),
		'sqlReplacements' => array(
			'@engine@' => _MYSQL_ENGINE_,
			'@prefix@' => _DB_PREFIX_,
			'@database@' => _DB_NAME_,
			'@xml_feed@' => 'pc_xmlfree_feed',
			'@xml_service@' => 'pc_xmlfree_service'
		),
		'xpath' => null,
		'tablesExist' => false,
		'tablesEmpty' => true,
	);
	public function __construct()
	{
		$this->name = 'prestacenterxmlexportfree';
		$this->tab = 'AdminCatalog';
		$this->controllerClass = 'PcXmlFree'; 
		$this->version = '1.0.2.1';
		$this->author = 'PrestaCenter';
		$this->need_instance = 1;
		parent::__construct();
		$this->displayName = $this->l('PrestaCenter XML Export Free');
		$this->description = $this->l('Universal XML feed export for shopping comparison websites.');
		$this->tplDir = $this->getLocalPath().'templates'.DS;
		$this->exportDir = rtrim(_PS_ROOT_DIR_, DS).DS.'xml'.DS;
		$this->moduleUrl = rtrim($this->_path, '/').'/';
	}
	protected function initInstall()
	{
		clearstatcache();
		$this->installerData['files'] = Tools::scandir($this->getLocalPath().'override', 'php', '', true);
		$doc = new DOMDocument;
		$doc->load($this->getLocalPath().'sql.xml');
		$this->installerData['xpath'] = new DOMXPath($doc);
	}
	public function install()
	{
		$this->initInstall();
		try {
			$this->checkInstallFolders();
			$this->createTables();
						$this->insertDefaultData();
			if (!parent::install())
				throw new RuntimeException; 
			$this->addTab();
			Autoload::getInstance()->generateIndex();
			return true;
		} catch (RuntimeException $e) {
			$this->_errors[] = $e->getMessage();
			foreach (array_reverse($this->installerData['rollback']) as $method) {
				try {
					$this->$method();
				} catch (RuntimeException $re) {
				}
			}
			return false;
		}
	}
	public function uninstall()
	{
		$this->initInstall();
		try {
			$this->checkInstallFolders();
			$this->deleteTables();
			if (!parent::uninstall())
				throw new RuntimeException; 
			$this->removeTab();
			return true;
		} catch (RuntimeException $e) {
			$this->_errors[] = $e->getMessage();
			return false;
		}
	}
	public function installOverrides()
	{
		$errorFlag = false;
		if (!@copy($this->getLocalPath().'getfeed.php', $this->exportDir.'getfeed.php'))
			$errorFlag = true;
		$from = $this->getLocalPath().'override'.DS;
		$to = rtrim(_PS_ROOT_DIR_, DS).DS.'override'.DS;
		foreach ($this->installerData['files'] as $file) {
			if (!@copy($from . $file, $to . $file))
				$errorFlag = true;
		}
		if ($errorFlag)
			throw new RuntimeException($this->l('Failed to copy scripts for this module.'));
		return true;
	}
	public function uninstallOverrides()
	{
		$errorFlag = false;
		if (file_exists($this->exportDir.'getfeed.php') && !@unlink($this->exportDir.'getfeed.php'))
			$errorFlag = true;
		$dir = rtrim(_PS_ROOT_DIR_, DS).DS.'override'.DS;
		foreach ($this->installerData['files'] as $file) {
			if (file_exists($dir.$file) && !@unlink($dir.$file))
				$errorFlag = true;
		}
		if ($errorFlag)
			throw new RuntimeException($this->l('Failed to delete scripts for this module.'));
		return true;
	}
	protected function insertDefaultData()
	{
		$db = Db::getInstance();
		$inserted = 0;
		foreach($this->installerData['xpath']->query('//sql/defaultData/query') as $node) {
			$sql = strtr($node->nodeValue, $this->installerData['sqlReplacements']);
			if (!$db->execute($sql))
				throw new RuntimeException(sprintf($this->l('Saving the source data into the database failed.')) . ' : ' . $db->getMsgError());
			$inserted += $db->Affected_Rows();
		}
	}
	protected function tablesExist()
	{
		foreach($this->installerData['xpath']->query('//sql/check/query') as $node) {
			$sql = strtr($node->nodeValue, $this->installerData['sqlReplacements']);
			$tmp = Db::getInstance()->executeS($sql);
			if ($tmp === false)
				continue;
			if (count($tmp) > 0) {
				$this->installerData['tablesExist'] = true;
				foreach($tmp as $tableInfo) {
					if ($tableInfo['Rows'] > 0) {
						$this->installerData['tablesEmpty'] = false;
						return true;
					}
				}
			}
			return true;
		}
		return false;
	}
	protected function createTables()
	{
		$this->installerData['rollback'][] = 'deleteTables';
		$db = Db::getInstance();
		foreach($this->installerData['xpath']->query('//sql/install/query') as $node) {
			$sql = strtr($node->nodeValue, $this->installerData['sqlReplacements']);
			if (!$db->execute($sql))
				throw new RuntimeException(sprintf($this->l('Failed to create database tables for this module.')).' : '.$db->getMsgError());
		}
	}
	protected function deleteTables()
	{
		$db = DB::getInstance();
		foreach($this->installerData['xpath']->query('//sql/uninstall/query') as $node) {
			$sql = strtr($node->nodeValue, $this->installerData['sqlReplacements']);
			if (!$db->execute($sql))
				throw new RuntimeException(sprintf($this->l('Failed to remove database tables of this module.')).' : '.$db->getMsgError());
		}
	}
	protected function addTab()
	{
		$this->installerData['rollback'][] = 'removeTab';
		$query = new DbQuery;
		$query->select('id_tab')
			->from('tab')
			->where('`id_parent` = 0')->where('`active` = 1')
			->orderBy('`position`');
		$id_parent = Db::getInstance()->getValue($query, false);
		if ($id_parent === false)
			throw new RuntimeException(sprintf($this->l('Failed to add the module into the main BO menu.')).' : '.Db::getInstance()->getMsgError());
		$tabNames = array();
		foreach(Language::getLanguages(false) as $lang)
			$tabNames[$lang['id_lang']] = $this->displayName;
		$tab = new Tab(); 
		$tab->class_name = $this->controllerClass;
		$tab->name = $tabNames;
		$tab->module = $this->name;
		$tab->id_parent = $id_parent;
		if (!$tab->save())
			throw new RuntimeException($this->l('Failed to add the module into the main BO menu.'));
	}
	protected function removeTab()
	{
		if (!Tab::getInstanceFromClassName($this->controllerClass)->delete())
			throw new RuntimeException($this->l('Failed to remove the module from the main BO menu.'));
	}
	public function getContent()
	{
		$id = Tab::getIdFromClassName($this->controllerClass);
		$token = Tools::getAdminToken($this->controllerClass.$id.(int)$this->context->employee->id);
		header('Location: index.php?controller='.$this->controllerClass.'&token='.$token);
		exit;
	}
	protected function checkInstallFolders()
	{
		$errors = '';
		$writableDirs = array(
			$this->exportDir,
			_PS_ROOT_DIR_.DS.'override'.DS.'classes'.DS,
		);
		foreach ($writableDirs as $dir) {
			if (!file_exists($dir) && !@mkdir($dir)) {
				$errors .= sprintf($this->l('Directory (%s) cannot be created. Please create it and set the write permission.'), $dir);
			} elseif (!is_dir($dir)) {
				$errors .= sprintf($this->l('Error: name (%s) is not a directory but a file.'), $dir);
			} elseif (!is_writable($dir) && !chmod($dir, 0775)) {
				$errors .= sprintf($this->l('Directory (%s) is not writable. Please set the write permission.'), $dir);
			}
		}
		if ($errors) {
			throw new RuntimeException($errors);
		}
	}
	public function checkExportFolder()
	{
		$errors = '';
		clearstatcache();
		if (!is_writable($this->exportDir) && !chmod($this->exportDir, 0775))
			$errors .= ' '.sprintf($this->l('Directory (%s) is not writable. Please set the write permission.'), '/xml');
		if (!is_writable($this->tplDir) && !chmod($this->tplDir, 0755))
			$errors .= ' '.sprintf($this->l('Directory (%s) is not writable. Please set the write permission.
'), '/modules/'.$this->name.'/templates');
		if ($errors)
			throw new RuntimeException($errors);
	}
	public function parseXmlTemplate($xmlTpl)
	{
		if (get_magic_quotes_gpc())
			$xmlTpl = stripslashes($xmlTpl);
		$this->checkExportFolder();
		$crate = new stdClass;
		$crate->source = $xmlTpl;
				$value = 'product';
		$re = '~<(\b(?!XML)[a-z][\w0-9-]*)(\s+(?:[^>]+\s+)?)'.self::XMLTPL_BLOCK.'=([\'"])((?:.+?[,\s]+)?'.$value.'(?:[\s,]+.+?)?)\\3(.+</\\1>|[^>]+/>)~isu';
		if (!preg_match($re, $xmlTpl, $matches)) {
			throw new InvalidArgumentException($this->l('XML template does not contain any element identified as product.'));
		}
		$xmlTpl = preg_replace('~\s+'.self::XMLTPL_BLOCK.'='.$matches[3].'.+?'.$matches[3].'~isu', '', $xmlTpl);
		$xmlTpl = preg_replace('~(?<=>)\s+(?=<)|\s{2,}~', '', $xmlTpl);
		if (!preg_match('~^(.+)(<'.$matches[1].'\b.+</'.$matches[1].'>|<'.$matches[1].'\b(?:.(?!=/>))*?/>)(.+)$~isu', $xmlTpl, $m))
			throw new InvalidArgumentException($this->l('XML template does not contain any element identified as product.'));
		$crate->parts = array();
		$crate->parts['header'] = $m[1];
		$crate->parts['product'] = $m[2];
		$crate->parts['footer'] = $m[3];
		return $crate;
	}
	public function updateExportTemplate()
	{
		$primaryKey = PcXmlFreeFeed::$definition['primary'];
		$query = new DbQuery;
		$query->select('f.`'.$primaryKey.'` id, f.`xml_source`, f.`allow_empty_tags`, f.`filename`')
			->from(PcXmlFreeFeed::$definition['table'], 'f');
		$data = Db::getInstance()->executeS($query);
		if ($data === false)
			throw new RuntimeException($this->l('Error reading from the database.').' : '.Db::getInstance()->getMsgError());
		$phpTemplate = $this->tplDir.'PcXmlFreeTemplate.tpl.php';
		if (!is_file($phpTemplate) || !is_readable($phpTemplate)) {
			throw new InvalidArgumentException(sprintf($this->l('File (%s) does not exist or is not readable.'), basename($phpTemplate)));
		}
		require $this->tplDir.'PcXmlFreeTplGenerator.php';
		$generator = new PcXmlFreeTplGenerator($this->allowedProperties);
		$generator->setSource(file_get_contents($phpTemplate));
		foreach($data as $file) {
			try {
				$xml = $this->parseXmlTemplate($file['xml_source']);
				$generator->allowEmptyTags($file['allow_empty_tags'])
					->addBlock('feed'.$file['id'].'header', $xml->parts['header'])
					->addBlock('feed'.$file['id'].'product', $xml->parts['product'])
					->addBlock('feed'.$file['id'].'footer', $xml->parts['footer']);
			} catch (Exception $e) {
				throw new InvalidArgumentException($e->getMessage().' ('.$file['filename'].')');
			}
		}
		if (!file_put_contents($this->tplDir.'PcXmlFreeTemplate.php', $generator->getTemplate()))
			throw new RuntimeException(sprintf($this->l('Directory (%s) is not writable. Please set the write permission.'), $this->tplDir));
		$generator->reset();
		return true;
	}
	public function createExportFiles($settings)
	{
		$oldCurrency = $this->context->currency;
		$oldLanguage = $this->context->language;
		$oldLinkRewriting = $this->context->link->allow;
		$oldShop = $this->context->shop;
		try {
			$this->initExport($settings);
			$this->exportProducts();
			$this->finishExport();
			$this->context->shop = $oldShop;
			$this->context->currency = $oldCurrency;
			$this->context->language = $oldLanguage;
			$this->context->link->allow = $oldLinkRewriting;
			Dispatcher::getInstance()->use_routes = $oldLinkRewriting;
		} catch (Exception $e) {
			$this->closeFiles();
			$this->removeTempFiles();
			$this->context->shop = $oldShop;
			$this->context->currency = $oldCurrency;
			$this->context->language = $oldLanguage;
			$this->context->link->allow = $oldLinkRewriting;
			Dispatcher::getInstance()->use_routes = $oldLinkRewriting;
			throw $e;
		}
		return $this->exportFiles;
	}
	protected function exportProducts()
	{
		$db = Db::getInstance();
		$sql = 'SELECT p.`id_product`, p.`ean13`, p.`upc`, p.`supplier_reference`, p.`reference`,
				pl.`id_lang`, pl.`name`, pl.`description`, pl.`description_short`, pl.`link_rewrite`,
				IFNULL(i.`id_image`, 0) id_image, m.`name` manufacturer, pl.`available_later`, pl.`available_now`,
				stck.`quantity`, stck.`out_of_stock`';
		$sql .= ', p.`condition`, p.`id_category_default`, p.`online_only`, p.`date_upd`, p.`available_for_order`
			FROM `'._DB_PREFIX_.'product` p
			INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON p.`id_product` = pl.`id_product`
				AND pl.`id_lang` IN ('.implode(',', array_keys($this->languages)).')
			LEFT JOIN `'._DB_PREFIX_.'stock_available` stck ON p.`id_product` = stck.`id_product`
				AND stck.`id_product_attribute` = 0
			LEFT JOIN `'._DB_PREFIX_.'image` i ON i.`id_product` = p.`id_product` AND i.`cover` = 1
			LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer` AND m.`active` = 1
			WHERE p.`active` = 1 ';
		$sql .= 'ORDER BY p.`id_product`, pl.`id_lang`';
		$result = $db->query($sql);
		if ($result === false)
			throw new RuntimeException($this->l('Error reading from the database.').' '.$db->getMsgError());
		unset($query);
		$lastProductId =  0;
		$tmp =  array();
		$product = new Product;
		$properties = array_flip(array_keys(get_object_vars($product))); 
		while ($row = $db->nextRow($result)) {
			if ($row['id_product'] != $lastProductId) {
				if ($lastProductId > 0) {
					$this->writeProduct($tmp, $product);
				}
				$lastProductId = $row['id_product'];
				$tmp = $row;
				$tmp['id']					= $row['id_product'];
				$tmp['ean']					= $row['ean13'];
				$tmp['update_item']			= strtotime($row['date_upd']);
				$tmp['availability']		= ''; 
				$tmp['days']			= array();
				$tmp['available_now']	= array();
				$tmp['available_later'] = array();
				$tmp['url']				= array();
				$tmp['name']			= array();
				$tmp['name_variant']	= array();
				$tmp['categories']		= array();
				$tmp['link_rewrite']	= array();
				$tmp['img_url']			= array();
				$tmp['description']		= array();
				$tmp['description_short']	= array();
				$tmp['price_vat']		= array();
				$tmp['price_vat_local'] = array();
				$tmp['price_vat_iso']	= array();
			}
			$product->id = $row['id_product'];
			$product->category = null;
			$tmp['categories'][$row['id_lang']] = null;
			foreach ($row as $key => $value) {
				if (isset($properties[$key])) {
					$product->$key = $value;
				}
			}
			if (!empty($this->categories['rewriteLink'][$row['id_category_default']][$row['id_lang']])) {
				$product->category = $this->categories['rewriteLink'][$row['id_category_default']][$row['id_lang']];
			}
			if (!empty($this->categories['breadcrumb'][$row['id_category_default']][$row['id_lang']])) {
				$tmp['categories'][$row['id_lang']] = $this->categories['breadcrumb'][$row['id_category_default']][$row['id_lang']];
			}
			$tmp['name'][$row['id_lang']] = $row['name'];
			$tmp['description'][$row['id_lang']] = $row['description'];
			$tmp['description_short'][$row['id_lang']] = $row['description_short'];
			$tmp['available_now'][$row['id_lang']] = $row['available_now'];
			$tmp['available_later'][$row['id_lang']] = $row['available_later'];
			$tmp['link_rewrite'][$row['id_lang']] = $row['link_rewrite'];
			$this->context->language = $this->languages[$row['id_lang']];
			if ($this->exportInfo['rewrite'] == 1) {
				$tmp['url'][$row['id_lang']] = $this->context->link->getProductLink($product, null, null, $row['ean13'], $row['id_lang'], null, 0, true);
				$tmp['img_url'][$row['id_lang']] = !empty($row['id_image']) ? $this->context->link->getImageLink($row['link_rewrite'], $row['id_image'], $this->exportInfo['imgType']) : '';
			} else {
				$tmp['url'][$row['id_lang']] = $this->context->link->getProductLink($product, null, null, $row['ean13'], $row['id_lang'], null, 0, false);
				$tmp['img_url'][$row['id_lang']] = !empty($row['id_image']) ? $this->context->link->getImageLink('', $row['id_image'], $this->exportInfo['imgType']) : '';
			}
			if ($row['quantity'] > 0)
				$tmp['days'][$row['id_lang']] = preg_match('~(\d+)~', $row['available_now'], $m) ? $m[1] : 0;
			else
				$tmp['days'][$row['id_lang']] = preg_match('~(\d+)~', $row['available_later'], $m) ? $m[1] : '';
		}
		if ($tmp) {
			$this->writeProduct($tmp, $product);
		}
		unset($tmp, $product);
	}
	protected function initExport(array $settings)
	{
		@set_time_limit(0);
		$this->exportInfo = $settings;
		$this->checkExportFolder();
		$this->context->shop = new Shop(1);  
		$this->context->link->allow = $this->exportInfo['rewrite'] = (int)Configuration::get('PS_REWRITING_SETTINGS', null, null, Configuration::get('PS_SHOP_DEFAULT'));
		Dispatcher::getInstance()->use_routes = $this->exportInfo['rewrite'];
		if ($this->exportInfo['rewrite']) {
			Dispatcher::getInstance()->loadRoutes();
		}
		$this->commonTplData = array(
			'update_feed'	=> time(),
			'shop_name'		=> $this->context->shop->name,
			'shop_url'		=> $this->context->shop->getBaseURL(),
			'lang_code'		=> array(),
			'lang_code_iso' => array(),
		);
		$db = Db::getInstance();
		$this->exportInfo['numWritten'] = 0;
		$this->updateExportTemplate();
		$this->getFileInfo($this->exportInfo['feedIds']);
		$this->getAllCategories();
		$this->openFiles();
	}
	protected function getFileInfo(array $feedIds)
	{
		$primaryKey = PcXmlFreeFeed::$definition['primary'];
		$query = new DbQuery;
		$query->select('f.`'.$primaryKey.'`, f.`'.$primaryKey.'` id, f.`xml_source`, f.`allow_empty_tags`, f.`filename`')
			->select('f.`id_lang`, f.`id_currency`, l.`iso_code`, l.`language_code`')
			->from(PcXmlFreeFeed::$definition['table'], 'f')
			->innerJoin('lang', 'l', 'l.`id_lang` = f.`id_lang` AND l.`active` = 1')
			->innerJoin('currency', 'c', 'c.`id_currency` = f.`id_currency`')
			->where('f.`'.$primaryKey.'` IN ('.implode(',', $feedIds).')')
			->orderBy('`id_currency`');
		$data = Db::getInstance()->executeS($query);
		if ($data === false)
			throw new RuntimeException($this->l('Error reading from the database.').' '.Db::getInstance()->getMsgError());
		elseif (empty($data))
			throw new RuntimeException($this->l('Output XML files are not defined.'));
		$this->exportInfo['feedIds'] = array();
		foreach($data as $feed) {
			$this->exportInfo['feedIds'][] = $feed['id'];
			if (!isset($this->languages[$feed['id_lang']])) {
				$this->languages[$feed['id_lang']] = new Language($feed['id_lang']);
				$this->commonTplData['lang_code'][$feed['id_lang']] = $feed['language_code'];
				$this->commonTplData['lang_code_iso'][$feed['id_lang']] = $feed['iso_code'];
			}
			if (!isset($this->currencies[$feed['id_currency']])) {
				$this->currencies[$feed['id_currency']] = new Currency($feed['id_currency']);
			}
		}
		$this->exportFiles = $data;
	}
	protected function getAlternativeCategories()
	{
		$sql = "SELECT DISTINCT cp.id_category_default as 'default', alt.`id_category` as alternative
				FROM `"._DB_PREFIX_."category_product` alt
				INNER JOIN (
					SELECT p3.`id_product`, p3.`id_category_default`, MIN(tmp2.`position`) position
					FROM `"._DB_PREFIX_."product` p3
					INNER JOIN (
						SELECT DISTINCT p.`id_category_default`
						FROM `"._DB_PREFIX_."product` p
						WHERE NOT EXISTS (
							SELECT *
							FROM `"._DB_PREFIX_."category` c
							WHERE c.`id_category` = p.`id_category_default`
						)
					) p4 ON p4.`id_category_default` = p3.`id_category_default`
					LEFT JOIN `"._DB_PREFIX_."category_product` tmp2 ON p3.`id_product` = tmp2.`id_product`
					WHERE tmp2.`position` > 0
					GROUP BY p3.`id_product`
					ORDER BY NULL
				) cp ON alt.`id_product` = cp.`id_product` AND alt.`position` = cp.`position`
				ORDER BY NULL";
		return (array) Db::getInstance()->ExecuteS($sql);
	}
	protected function getAllCategories()
	{
		$db = Db::getInstance();
		$langIds = implode(',', array_keys($this->languages));
		$rootCategory = (int) Configuration::get('PS_HOME_CATEGORY');
		if (!$rootCategory)
			$rootCategory = $this->context->shop->getCategory();
		$sql = 'SELECT c.`id_category` id, c.`id_parent` parent, c.`active`,
			cl.`name`, cl.`id_lang`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'category` c
			INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`
				AND cl.`id_lang` IN ('.$langIds.')
			WHERE c.`nleft` >
				(SELECT `nleft`
				FROM `'._DB_PREFIX_.'category`
				WHERE `id_category` = '.(int) $rootCategory.'
				LIMIT 1)
			ORDER BY c.`nleft`';
		$result = $db->query($sql);
		if ($result === false)
			throw new RuntimeException($this->l('Error reading from the database.').' '.$db->getMsgError());
		elseif (!$db->numRows())
			return;
		$this->categories = array('breadcrumb' => array(), 'rewriteLink' => array());
		while ($row = $db->nextRow($result)) {
			$tmp = '';
			if (isset($this->categories['breadcrumb'][$row['parent']][$row['id_lang']])) {
				$tmp = $this->categories['breadcrumb'][$row['parent']][$row['id_lang']];
			}
			if ($row['active']) {
				$tmp .= (!empty($tmp) ? ' | ' : '') . $row['name'];
			}
			$this->categories['breadcrumb'][$row['id']][$row['id_lang']] = $tmp;
			$this->categories['rewriteLink'][$row['id']][$row['id_lang']] = $row['link_rewrite'];
		}
		foreach ($this->getAlternativeCategories() as $cat) {
			if (isset($this->categories['breadcrumb'][$cat['alternative']]))
				$this->categories['breadcrumb'][$cat['default']] = $this->categories['breadcrumb'][$cat['alternative']];
			if (isset($this->categories['rewriteLink'][$cat['alternative']]))
				$this->categories['rewriteLink'][$cat['default']] = $this->categories['rewriteLink'][$cat['alternative']];
		}
	}
	protected function openFiles()
	{
		require $this->tplDir.'PcXmlFreeTemplate.php';
		$this->template = new PcXmlFreeTemplate;
		$this->template->setCommonData($this->commonTplData);
		foreach($this->exportFiles as $key => &$file) {
			$file['filename'] = $this->exportDir.$file['filename'];
				$this->removeTempFiles($file['filename']);
			$file['pointer'] = @fopen($file['filename'].$this->tempExt, 'w');
			if (!$file['pointer']) {
				$this->_errors[] = sprintf($this->l('XML file %s is not writable.'), basename($file['filename']));
				$this->removeTempFiles($file['filename']);
				unset($this->exportFiles[$key]);
			}
				$this->template
					->set('id_lang', $file['id_lang'])
					->set('id_currency', $file['id_currency']);
				@fwrite($file['pointer'], $this->template->{'feed'.$file['id'].'header'}());
		}
		unset($file);
		if (empty($this->exportFiles))
			throw new RuntimeException($this->l('XML files cannot be created, please check the access rights.'));
	}
	protected function writeProduct(array $productData, Product $product)
	{
		foreach($this->exportFiles as $file) {
			$feedCurrency = $file['id_currency'];
			if ($feedCurrency != $this->context->currency->id) {
				$this->context->currency = $this->currencies[$feedCurrency];
			}
			if (!isset($productData['price_vat'][$feedCurrency])) {
				$price = $product->getPrice(true);
				$productData['price_vat'][$feedCurrency] = Tools::ps_round($price, ((int)$this->context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_));
				$productData['price_vat_local'][$feedCurrency] = Tools::displayPrice($price, $this->context->currency, false, $this->context);
				$productData['price_vat_iso'][$feedCurrency] = $productData['price_vat'][$feedCurrency] . ' ' . $this->context->currency->iso_code;
			}
			$this->template
				->set('product', $productData)
				->set('id_lang', $file['id_lang'])
				->set('id_currency', $file['id_currency']);
			try {
				@fwrite($file['pointer'], $this->template->{'feed'.$file['id'].'product'}($productData));
			} catch (PcXmlFreeAvailabilityException $e) {
				continue;
			}
		}
		$this->exportInfo['numWritten']++;
		$product->flushPriceCache();
	}
	protected function finishExport()
	{
		foreach($this->exportFiles as &$file) {
				$this->template
					->set('id_lang', $file['id_lang'])
					->set('id_currency', $file['id_currency']);
				@fwrite($file['pointer'], $this->template->{'feed'.$file['id'].'footer'}());
				@fclose($file['pointer']);
				$this->updateExportFiles($file['filename']);
			$file['filename'] = basename($file['filename']);
			unset($file['pointer']);
		}
		unset($file, $this->template, $this->languages, $this->currencies,
			$this->categories );
	}
	protected function closeFiles()
	{
		foreach($this->exportFiles as $file) {
			@fclose($file['pointer']);
		}
		unset($file, $this->template, $this->languages, $this->currencies,
			$this->categories );
	}
	protected function updateExportFiles($filename = null)
	{
		if (!$filename) {
			foreach ($this->exportFiles as $file)
				$this->updateExportFiles($file['filename']);
			return;
		}
		if (file_exists($filename . $this->tempExt)) {
			if (file_exists($filename) && ! @unlink($filename))
				throw new RuntimeException(sprintf($this->l('Unable to update the feed %s.'), basename($filename)));
			if (! @rename($filename . $this->tempExt, $filename))
				throw new RuntimeException(sprintf($this->l('Unable to update the feed %s.'), basename($filename)));
		}
	}
	protected function removeTempFiles($filename = null)
	{
		if (!$filename) {
			foreach ($this->exportFiles as $file)
				$this->removeTempFiles($file['filename']);
			return;
		}
		if (file_exists($filename . $this->tempExt)) {
			if (! @unlink($filename . $this->tempExt))
				throw new RuntimeException(sprintf($this->l('Incorrect feed %s cannot be deleted.'), basename($filename)));
		}
	}
	public function getTplDir()
	{
		return $this->tplDir;
	}
	public function getExportDir()
	{
		return $this->exportDir;
	}
	public function getExportInfo()
	{
		return $this->exportInfo;
	}
	public function getModuleUrl()
	{
		return $this->moduleUrl;
	}
	public function readableFileSize($bytes, $precision = 2)
	{
		$bytes = round($bytes);
		if ($bytes <= 0)
			return '0 B';
		$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		foreach($units as $unit) {
			if ($bytes < 1024 || $unit === end($units))
				break;
			$bytes /= 1024;
		}
		return round($bytes, $precision).' '.$unit;
	}
}
