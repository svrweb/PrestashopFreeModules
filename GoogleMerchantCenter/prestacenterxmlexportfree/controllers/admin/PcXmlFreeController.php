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

class PcXmlFreeController extends ModuleAdminController
{
	const DEFAULT_IMAGE_SIZE = 'large';
	protected $fields_value_override = array();
	static protected $db;
	protected $currentHelper;
	protected $smartyNS = 'xmlexport';
	public function __construct()
	{
		Autoload::getInstance()->index["PcXmlFreeFeed"] = 'modules/prestacenterxmlexportfree/classes/PcXmlFreeFeed.php';
		Autoload::getInstance()->index["PcXmlFreeService"] = 'modules/prestacenterxmlexportfree/classes/PcXmlFreeService.php';
		$this->table = PcXmlFreeService::$definition['table'];
		$this->identifier = PcXmlFreeService::$definition['primary'];
		$this->_defaultOrderBy = PcXmlFreeService::$definition['primary'];
		$this->className = 'PcXmlFreeService';
		$this->lang = false;
		$this->multishop_context = Shop::CONTEXT_ALL;
		self::$db = Db::getInstance();
		parent::__construct();
		$this->tpl_folder = 'xml_export/';
	}
	public function renderList()
	{
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->addRowAction('details');
		$this->tpl_list_vars[$this->smartyNS]['onclick'] = array(
			'type' => 'onclick',
			'name' => 'ajaxDetails',
		);
		$this->tpl_list_vars[$this->smartyNS]['cbx'] = array(
			'dependent' => true,
		);
		$this->explicitSelect = true;
		$this->_select = "a.`name`, COUNT(`".PcXmlFreeFeed::$definition['primary']."`) as 'count_values'";
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.PcXmlFreeFeed::$definition['table'].'` f';
		$this->_join .= ' USING (`'.PcXmlFreeService::$definition['primary'].'`)';
		$this->_group = 'GROUP BY a.`'.PcXmlFreeService::$definition['primary'].'`';
		return parent::renderList();
	}
	public function setHelperDisplay(Helper $helper)
	{
		$this->currentHelper = $helper;
		parent::setHelperDisplay($helper);
	}
	public function setMedia()
	{
		parent::setMedia();
		$this->addJS($this->module->getModuleUrl().'views/js/xmlexport.js');
	}
	public function init()
	{
		$table = PcXmlFreeFeed::$definition['table'];
		if (Tools::getIsset('add'.$table) || Tools::getIsset('update'.$table) || Tools::getIsset('delete'.$table)
				|| Tools::isSubmit('submitAdd'.$table)) {
			$this->useXmlFeed();
		} else
			$this->useXmlService();
		parent::init();
		$this->context->smarty->assign($this->smartyNS, array());
	}
	public function ajaxPreProcess()
	{
		$this->context->smarty->assign(array(
			'currentIndex' => self::$currentIndex,
			'table' => $this->table,
			'identifier' => $this->identifier,
		));
	}
	public function initProcess()
	{
		parent::initProcess();
		if ($this->action === 'bulkexport') {
			$this->useXmlService();
			$this->boxes = array_unique(array_merge(
				PcXmlFreeService::getFeedIds(Tools::getValue(PcXmlFreeService::$definition['table'].'Box', array())),
				Tools::getValue(PcXmlFreeFeed::$definition['table'].'Box', array())
			));
		}
	}
	public function postProcess()
	{
		if ($this->ajax && Tools::getValue('action') === 'details')
			$this->useXmlFeed(false);
		parent::postProcess();
	}
	public function initToolbar()
	{
		switch ($this->display)
		{
			case 'add':
			case 'edit':
				$this->toolbar_btn['save'] = array(
					'href' => '#',
					'desc' => $this->l('Save')
				);
				if ($this->table === PcXmlFreeFeed::$definition['table']) {
					$this->toolbar_btn['save-and-stay'] = array(
						'short' => 'SaveAndStay',
						'href' => '#',
						'desc' => $this->l('Save and stay in this form'),
						'force_desc' => true,
					);
				}
				$back = self::$currentIndex.'&token='.$this->token;
				$this->toolbar_btn['back'] = array(
					'href' => $back,
					'desc' => $this->l('Back to the list of services')
				);
				break;
			default:
				$this->toolbar_btn['new'] = array(
					'href' => self::$currentIndex.'&amp;add'.$this->table.'&amp;token='.$this->token,
					'desc' => $this->l('Add a new service')
				);
				$this->toolbar_btn['newFeed'] = array(
					'href' => self::$currentIndex.'&amp;add'.PcXmlFreeFeed::$definition['table'].'&amp;token='.$this->token,
					'desc' => $this->l('Add a new feed'),
					'class' => 'toolbar-new'
				);
		}
	}
	public function initToolbarTitle()
	{
		$bread_extended = $this->breadcrumbs;
		switch ($this->table)
		{
			case PcXmlFreeService::$definition['table']:
				if ($this->display === 'edit')
					$bread_extended[] = $this->l('Edit the Service');
				elseif ($this->display === 'add')
					$bread_extended[] = $this->l('New service');
				break;
			case PcXmlFreeFeed::$definition['table']:
				if ($this->display === 'edit')
					$bread_extended[] = $this->l('Edit feed');
				elseif ($this->display === 'add')
					$bread_extended[] = $this->l('Add a new feed');
				break;
		}
		$this->toolbar_title = $bread_extended;
	}
	public function ajaxProcessDetails()
	{
		try {
			if (($id = Tools::getValue('id')))
			{
				$this->addRowAction('edit');
				$this->addRowAction('delete');
				$this->display = 'list';
				$query = new DbQuery;
				$query->select("SQL_CALC_FOUND_ROWS
						f.`".PcXmlFreeFeed::$definition['primary']."`, f.`filename`,
						c.`iso_code` as 'currency', l.`name` as 'lang', l.`active` as 'lang_active'")
					->from(PcXmlFreeFeed::$definition['table'], 'f')
					->leftJoin('currency', 'c', 'f.`id_currency` = c.`id_currency`')
					->leftJoin('lang', 'l', 'f.`id_lang` = l.`id_lang`')
					->where('f.`'.PcXmlFreeService::$definition['primary'].'` = '.(int)$id)
					->orderBy('`'.PcXmlFreeFeed::$definition['primary'].'`, `filename`, `lang`');
				$this->_list = self::$db->executeS($query);
				foreach($this->_list as &$item) {
					$path = $this->module->getExportDir().$item['filename'];
					if (file_exists($path)) {
						$item['filesize'] = $this->module->readableFileSize(filesize($path));
						$item['created'] = date('Y-m-d H:i:s', filemtime($path));
					} else {
						$item['remove_onclick'] = true;
						$item['filesize'] = $this->module->readableFileSize(0);
						$item['created'] = '0000-00-00 00:00:00';
					}
					if (!$item['currency'])
						$item['currency'] = $this->l('undefined');
					if (!$item['lang'])
						$item['lang'] = $this->l('undefined');
					elseif (!$item['lang_active'])
						$item['lang'] .= ' - '.$this->l('inactive');
				}
				unset($item);
				$this->list_no_link = false;
				$this->shopLinkType = '';
				$this->toolbar_scroll = false;
				$this->list_simple_header = true; 
				$this->show_toolbar = false;
				$this->context->smarty->assign($this->smartyNS, array(
					'onclick' => array(
						'type' => 'link',
						'name' => 'xmlLink',
						'key' => 'filename',
						'data' => array(__PS_BASE_URI__.'xml/'),
					),
					'cbx' => array(
						'show' => true, 
						'dependent' => true, 
					),
				));
				$this->setHelperDisplay($helper = new HelperList);
				$content = $helper->generateList($this->_list, $this->fields_list);
				die (Tools::jsonEncode(array('use_parent_structure' => false, 'data' => $content)));
			} else
				die('id missing');
		} catch(Exception $e) {
			die((string) $e);
		}
	}
	public function processSave()
	{
		try {
			$return = parent::processSave();
			return $return;
		} catch (Exception $e) {
			$this->errors[] = $e->getMessage();
			$this->display = 'edit'; 
			$this->redirect_after = '';
			return false;
		}
	}
	public function processBulkExport()
	{
		$settings = array();
		if (!empty($this->boxes)) {
			$settings = array(
				'feedIds' => $this->boxes,
				'imgType' => $this->setDefaultImgType(self::DEFAULT_IMAGE_SIZE),
			);
		}
		if (empty($settings)) {
			$this->errors[] = $this->l('You have not selected any feeds or services, XML files cannot be created.');
			return false;
		}
		try {
			$this->module->createExportFiles($settings);
			$exportInfo = $this->module->getExportInfo();
		} catch (Exception $e) {
			$this->errors[] = sprintf($this->l('Failed to create XML files (error description: %s). XML feeds have been restored to the previous state.'), $e->getMessage());
			return false;
		}
		$this->warnings = $this->module->getErrors();
		$this->confirmations[] = $this->l('The selected XML feeds have been successfully created / updated.');
		$this->useXmlService();
		return true;
	}
	protected function _childValidation()
	{
		if ($this->identifier === PcXmlFreeFeed::$definition['primary']) {
			$currencyExists = self::$db->getValue("SELECT COUNT(*)
				FROM `"._DB_PREFIX_."currency`
				WHERE `id_currency` = ".(int)Tools::getValue('id_currency', 0));
			$languageExists = self::$db->getValue("SELECT COUNT(*)
				FROM `"._DB_PREFIX_."lang`
				WHERE `id_lang` = ".(int)Tools::getValue('id_lang', 0));
			if (!$currencyExists)
				$this->errors[] = $this->l('You have selected the currency that is not in the database.');
			if (!$languageExists)
				$this->errors[] = $this->l('You have selected the language that is not in the database.');
		}
	}
	protected function setDefaultImgType($size)
	{
		if ($type = Configuration::get(PrestaCenterXmlExportFree::CFG_PREFIX.'IMAGE_TYPE')) {
			return $type;
		}
		$type = Db::getInstance()->getValue('SELECT `name` FROM `'._DB_PREFIX_.'image_type`
			WHERE (`name` LIKE "'.$size.'%") AND (`products` = 1)');
        Configuration::updateValue(PrestaCenterXmlExportFree::CFG_PREFIX.'IMAGE_TYPE', $type);
        return $type;
	}
	public function getFieldsValue($obj)
	{
		parent::getFieldsValue($obj);
		$this->fields_value = array_merge($this->fields_value, $this->fields_value_override);
		return $this->fields_value;
	}
	protected function useXmlService()
	{
		$primaryKey = PcXmlFreeService::$definition['primary'];
		$this->table = PcXmlFreeService::$definition['table'];
		$this->identifier = $primaryKey;
		$this->_defaultOrderBy = $primaryKey;
		$this->className = 'PcXmlFreeService';
		$this->fields_list = array(
			$primaryKey => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25,
			),
			'name' => array(
				'title' => $this->l('Service name'),
			),
			'count_values' => array(
				'title' => $this->l('Feeds'),
				'width' => 60,
				'align' => 'center',
				'havingFilter' => true,
			),
		);
		$this->bulk_actions = array(
			'export' => array(
				'text' => $this->l('Create XML'),
			),
		);
		$fields = PcXmlFreeService::$definition['fields'];
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Price comparison services:'),
				'image' => $this->module->getModuleUrl().'img/feed_link.png',
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Service name:'),
					'name' => 'name',
					'size' => 30,
					'maxlength' => $fields['name']['size'],
					'required' => $fields['name']['required'],
					'hint' => $this->l('You can use only letters, numbers and hyphens.')
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);
	}
	protected function useXmlFeed()
	{
		$primaryKey = PcXmlFreeFeed::$definition['primary'];
		$this->table = PcXmlFreeFeed::$definition['table'];
		$this->identifier = $primaryKey;
		$this->_defaultOrderBy = 'name';
		$this->className = 'PcXmlFreeFeed';
		$this->fields_list = array(
			'filename' => array(
				'title' => $this->l('XML file'),
				'width' => '20%',
			),
			'created' => array(
				'title' => $this->l('Created / updated'),
				'width' => '20%',
			),
			'filesize' => array(
				'title' => $this->l('Size'),
				'remove_onclick' => true,
				'width' => '15%',
			),
			'lang' => array(
				'title' => $this->l('Language'),
				'remove_onclick' => true,
				'width' => '15%',
			),
			'currency' => array(
				'title' => $this->l('Currency'),
				'remove_onclick' => true,
				'width' => '15%',
			),
		);
		$this->bulk_actions = array();
		$services = PcXmlFreeService::getList();
		$fields = PcXmlFreeFeed::$definition['fields'];
		$tplDataObject = $this->context->smarty->createData();
		$tplDataObject->assign('module', $this->module->name);
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('XML feed'),
				'image' => $this->module->getModuleUrl().'img/feed.png',
			),
			'description' => $this->context->smarty->fetch($this->module->getTplDir().'feedLegend.tpl', $tplDataObject),
			'input' => array(
				array(
					'name' => PcXmlFreeService::$definition['primary'],
					'label' => $this->l('Service'),
					'type' => 'select',
					'required' => true,
					'options' => array(
						'query' => $services,
						'id' => 'id',
						'name' => 'name',
					),
					'desc' => $this->l('Select a service for this feed.'),
				),
				array(
					'name' => 'filename',
					'label' => $this->l('Name of the XML file'),
					'type' => 'text',
					'required' => true,
					'size' => 35,
					'maxlength' => $fields['filename']['size'],
				),
				array(
					'name' => 'id_lang',
					'label' => $this->l('Language of the feed'),
					'type' => 'select',
					'required' => true,
					'options' => array(
						'query' => Language::getLanguages(false ),
						'id' => 'id_lang',
						'name' => 'name'
					)
				),
				array(
					'name' => 'id_currency',
					'label' => $this->l('Currency of the feed'),
					'type' => 'select',
					'required' => true,
					'options' => array(
						'query' => Currency::getCurrencies( false, false),
						'id' => 'id_currency',
						'name' => 'name'
					)
				),
				array(
					'name' => 'xml_source',
					'label' => $this->l('XML template'),
					'type' => 'textarea',
					'required' => true,
					'maxlength' => $fields['xml_source']['size'],
					'rows' => 15,
					'cols' => 80,
				),
				array(
					'name' => 'allow_empty_tags',
					'label' => $this->l('Generate empty elements?'),
					'type' => 'radio',
					'required' => true,
					'default' => 0,
					'desc' => $this->l('Create an empty tag in the feed if the requested data is missing?'),
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'add',
							'value' => 1,
							'label' => $this->l('Create')
						),
						array(
							'id' => 'dont_add',
							'value' => 0,
							'label' => $this->l('Skip')
						)
					)
				),
			),
			'submit' => array(
				'title' => $this->l('Save feed'),
				'class' => 'button',
			)
		);
	}
}
