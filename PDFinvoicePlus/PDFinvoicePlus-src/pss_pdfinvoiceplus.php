<?php
/**
 * To customize invoice PDF footer.
 *
 * See LICENCE.txt for terms of use
 * History :
 * 	@version 1.0 (2012-02-07) : 1st version
 * 	@version 1.1 (2012-02-22) : 
 *		> support of <img src="" width="" height=""/>
 *		> support of <center>, <right> or <left>
 * 	@version 1.2 (2012-07-06) : 
 *		> version migration process
 *		> multilingual support
 * 	@version 1.3 (2012-10-09) : 
 *		> add support for style attribute (text color)
 *		> compatible with Prestashop 1.5
 */

class pss_pdfinvoiceplus extends Module
{
	// absolute URL to this module
	public $absoluteUrl;
	// absolute path (in OS sense) to this module
	public $absolutePath;
	// the admin URL to get configuration screen for current module
	private $confUrl;
	// config default set
	private $_config = array(
		'PRESTASCOPE_PDFINVPL_VERSION' 		=> '1.3',
		'PRESTASCOPE_PDFINVPL_TEXT' 		=> '',
	);
	public $_languages;
	private $_defaultFormLanguage;
	
	private $html2pdf;

	// the version of module files in directory
	private $_directoryVersion;
	// the version history
	private $_versionHistory = array('1.0', '1.1', '1.2', '1.3');

	function __construct()
	{
		// Module definition
		$this->name = 'pss_pdfinvoiceplus';
		// some changes between 1.3.x (and previous) and 1.4.x Prestashop versions
		if ($this->isPs12x() || $this->isPs13x()) 
		{
			$this->tab = 'Prestascope';
		} 
		else if ($this->isPs14x()) 
		{
			$this->tab = 'billing_invoicing';
			$this->author = 'PrestaScope';
		}
		else if ($this->isPs15x())
		{
			$this->tab = 'billing_invoicing';
			$this->author = 'PrestaScope';
			// set the version compliancy
			$this->ps_versions_compliancy = array('min' => '1.2', 'max' => '1.6');	// 1.2 included / 1.6 excluded
			// set the dependency with the block_newsletter module
			array_push($this->dependencies, 'blocknewsletter');
		}
		$this->need_instance = 0;
		$this->version = $this->_config['PRESTASCOPE_PDFINVPL_VERSION'];

		// full url & path to current module
		// Use Tools::getHttpHost(false, true).__PS_BASE_URI__
		$this->absoluteUrl = $this->is_https()?'https':'http'.'://'.$_SERVER['HTTP_HOST']. _MODULE_DIR_ . $this->name . '/';
		$this->absolutePath = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->name.DIRECTORY_SEPARATOR;
		
		// standard constructor
		parent::__construct();
		
		// custom display for module list
		$this->displayName = $this->l('PSS/PDF Invoice plus');
		$buf = '<style type="text/css">';
		$buf .= 'a.descriptionLink {color:blue;text-decoration:underline;} a.descriptionLink:hover{color:red;text-decoration:none;}';
		$buf .= 'div.pssDescriptionDiv {background:#6a6a6a; color:white; padding:3px; margin-top:2px;}';
		$buf .= '</style>';
		$buf .= '<div class="pssDescriptionDiv">';
		$buf .= $this->l('Add some custom multi-lingual text / html to the PDF invoice (after the order details)');
		$buf .= '</div>';
		$this->description = $buf;
		
		// the admin URL to get configuration for current module
		$tab = Tools::getValue('tab');
		$token = Tools::getValue('token');
		$mainParts = explode('?', $_SERVER['REQUEST_URI']);
		$this->confUrl = $mainParts[0].'?tab=AdminModules&amp;configure='.$this->name.'&amp;token='.$token;
		
		// object util to translate HTML to PDF
		require_once(_PS_ROOT_DIR_.'/modules/pss_pdfinvoiceplus/pss_html2fpdf.php');
		$this->html2pdf = new Pss_Html2Fpdf();
		
		// get the version of module files in directory
		$this->_directoryVersion = $this->_config['PRESTASCOPE_PDFINVPL_VERSION'];
	}
	private function is_https()
	{
		return strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? true : false;
	}
	function install()
	{
		// check required version - 1.2.x or 1.3.x or 1.4.x
		if (!$this->isPs12x() && !$this->isPs13x() && !$this->isPs14x() && !$this->isPs15x())
			return false;
		if (!parent::install() || 
			!$this->registerHook('PDFInvoice') ||
			!$this->_installConfig())
			return false;
		return true;
	}
	public function uninstall() {
		if(!parent::uninstall() || 
			!$this->_uninstallConfig())
			return false;
		return true;
	}
	private function _installConfig() {
		foreach ($this->_config as $key => $value) {
			Configuration::updateValue($key, $value);
		}
		return true;
	}
	private function _uninstallConfig() {
		foreach ($this->_config as $key => $value) {
			Configuration::deleteByName($key);
		}
		return true;
	}
	/**
	 * Init language management for admin display
	 */
	public function initAdminLang() 
	{
		global $cookie;
		
		// languages management
		$allowEmployeeFormLang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		if ($allowEmployeeFormLang && !$cookie->employee_form_lang)
			$cookie->employee_form_lang = intval(Configuration::get('PS_LANG_DEFAULT'));
		$useLangFromCookie = false;
		$this->_languages = Language::getLanguages();
		if ($allowEmployeeFormLang)
			foreach ($this->_languages AS $lang)
				if ($cookie->employee_form_lang == $lang['id_lang'])
					$useLangFromCookie = true;
		if (!$useLangFromCookie)
			$this->_defaultFormLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		else
			$this->_defaultFormLanguage = intval($cookie->employee_form_lang);
	}
	/**
	 * Provide public access to this private property
	 */
	public function getDefaultFormLanguage() 
	{
		return $this->_defaultFormLanguage;
	}
	/**
	 * Give public access to protected attibute (since 1.4.5)
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	/**
	 * Give public access to protected attibute (since 1.4.5)
	 */
	public function addError($text)
	{
		return $this->_errors[] = $text;
	}
	/* *****************************************************************************************
	 *
	 *						FUNCTION TO DISPLAY BO FORMS AND MANAGE ACTIONS
	 *
	 * ***************************************************************************************** */
	/**
	 * Prepare form to display
	 * Dedicated function to Prestashop 1.5.x version.
	 */	
	private function initForm15()
	{
		foreach ($this->_languages as $k => $language)
			$this->_languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));

		$helper = new HelperForm();
		$helper->module = $this;
		$helper->name_controller = 'editorial';
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->languages = $this->_languages;
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
		$helper->allow_employee_form_lang = true;
		$helper->toolbar_scroll = true;
		$helper->toolbar_btn = $this->initToolbar15();
		$helper->title = 'Prestascope : '.$this->l('PDF Facture plus');
		$helper->submit_action = 'submitUpdate';
		
		$this->fields_form[0]['form'] = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('PDF Invoice text'),
				'image' => $this->_path.'logo.gif'
			),
			'submit' => array(
				'name' => 'submitUpdate',
				'title' => $this->l('Update'),
				'class' => 'button'
			),
			'input' => array(
				array(
					'type' => 'textarea',
					'cols' => 100,
					'rows' => 10,
					'label' => $this->l('Text'),
					'name' => 'PRESTASCOPE_PDFINVPL_TEXT',
					'lang' => true,
					'autoload_rte' => true,
					'hint' => $this->l('Text of your choice; for example, explain your mission, highlight a new product, or describe a recent event.'),
				)
			)
		);		
		return $helper;
	}
	private function initToolbar15()
	{
		$this->toolbar_btn['save'] = array(
			'href' => '#',
			'desc' => $this->l('Save')
		);
		
		return $this->toolbar_btn;
	}
	
	/**
	 * Deal with BO configuration form user actions
	 */
	public function getContent()
	{
		// post process
		// load datas
		// display form

		if (!$this->isPs15x()) 
			// languages management
			$this->initAdminLang();
		else
			$this->_languages = Language::getLanguages(false);
		
		// process actions
		$this->postProcess();

		// -------------------------------------------------------
		// check module version
		//
		// Get the installed version
		$installedVersion = Configuration::get('PRESTASCOPE_PDFINVPL_VERSION');
		// if the version is not up-to-date, display the update version form
		if ($installedVersion!==$this->_directoryVersion) 
		{
			// the beginning of the HTML to display
			return $this->displayMigrationForm($installedVersion);
		}

		return $this->isPs15x() ? $this->_displayForm15() : $this->_displayForm();
	}
	/**
	 * Process actions
	 */
	public function postProcess()
	{
		// empty errors buffer
		$this->_errors = array();
		$validationMsg = '';

		// -------------------------------------------------------
		// Update module version
		//
		if (Tools::isSubmit('submitUpdateModule'))
		{
			// Get the installed version
			$installedVersion = Configuration::get('PRESTASCOPE_PDFINVPL_VERSION');
			$this->migrateVersion($installedVersion);
			// everything's ok ?
			if (empty($this->_errors))
			{
				echo '<div class="conf ok">'.$this->l('Version updated successfully').'</div>';
			}
		}
		
		if (Tools::getValue('submitUpdate'))
		{
			// check each submitted value
			foreach ($this->_languages as $language) 
			{
				if (!isset($_POST['PRESTASCOPE_PDFINVPL_TEXT_'.$language['id_lang']]) || !Validate::isCleanHtml($_POST['PRESTASCOPE_PDFINVPL_TEXT_'.$language['id_lang']]))
					$this->_errors[] = $this->l('Text of lang').' : '.$language['name'].' '.$this->l('should be valid HTML');
			}

			// if no error, then update
			if (count($this->_errors)==0)
			{
				// prepare array of values to update configuration
				$values = array();
				foreach ($this->_languages as $language) 
				{
					$value = $_POST['PRESTASCOPE_PDFINVPL_TEXT_'.$language['id_lang']];
					// for 1.5.X version, only store the field value (TinyMce will manage that)
					// for previous version
					if (!$this->isPs15x())
					{
						// replace nl by br to store in DB
						$value = ereg_replace( "\r\n", '<br />', $value);
						// prepare value for SQL insertion (htmlentities ensure the data will not be altered bu DB storage)
						$values[$language['id_lang']] = pSQL(htmlentities($value, ENT_QUOTES, 'utf-8'));
					}
					// $_POST[$field.'_'.(int)($language['id_lang'])]
					else
						$values[$language['id_lang']] = $value;
				}
				Configuration::updateValue('PRESTASCOPE_PDFINVPL_TEXT', $values, true);
					
				// finally
				$validationMsg = '<div class="conf ok">'.$this->l('Updated successfully').'</div>';
			}
		}

		// the beginning of the HTML to display
		$this->_html = '<h2>Prestascope : '.$this->l('PDF Invoice Plus').'</h2>';

		// display errors if any
		if (!empty($this->_errors))
			foreach ($this->_errors as $error)
				$this->_html .= '<div class="alert error">'.$error.'</div>';

		// display validation message if any
		if (!empty($validationMsg))
			$this->_html .= $validationMsg;
				
		return $this->_html;
	}
	/**
	 * Display the form
	 */
	private function _displayForm15()
	{
		// prepare form
		$helper = $this->initForm15();
		
		// load values
//		$id_shop = (int)$this->context->shop->id;
		$values = $this->loadConfiguration('PRESTASCOPE_PDFINVPL_TEXT');

		// be sure there is a value (even empty) for each active language
		foreach ($this->_languages as $langParam)
			if (!array_key_exists($langParam['id_lang'], $values))
				$values[$langParam['id_lang']] = '';
		
		// fill form with values
		foreach($this->fields_form[0]['form']['input'] as $input) //fill all form fields
			if ($input['name'] == 'PRESTASCOPE_PDFINVPL_TEXT')
				$helper->fields_value[$input['name']] = $values;
		
		$this->_html .= $helper->generateForm($this->fields_form);
		// add help area
		$this->_html .= '
					<div class="clear">
						<p>'.$this->l('Text to append to PDF invoices').'. '.$this->l('Could be simple HTML with only some supported tags.').'</p>
						<p style="margin-left:20px;">'.$this->l('List and details are available with').' <a href="http://www.tcpdf.org/doc/classTCPDF.html#ac3fdf25fcd36f1dce04f92187c621407">'.$this->l('TCPDF documentation').'</a> '.$this->l('(tool used by Prestashop 1.5 to convert HTML to PDF).').'</p>
						<p style="margin-left:20px;">'.$this->l('Important : if invoices don\'t generate well, please, remove some HTML and try again ... the only way to find what is supported and what is not ! Sorry.').'</p>
					</div>
			';
		return $this->_html;
	}
	/**
	 * Display the form
	 */
	private function _displayForm()
	{
		global $cookie;

		// load all conf values
		$values = $this->loadConfiguration('PRESTASCOPE_PDFINVPL_TEXT');

		// prepare multi-lang fields
//		$divLangName = 'mmsg¤href';
		$divLangName = 'mPRESTASCOPE_PDFINVPL_TEXT';
		
		// restore newline chars for textarea display (stored in DB as <br />
		foreach($values as &$value)
			$value = ereg_replace( "<br />", "\r\n", html_entity_decode($value, ENT_QUOTES, 'utf-8'));

		// ------------------------------------------------------------
		// The text : PRESTASCOPE_PDFINVPL_TEXT
		$this->_html .= '
		<script type="text/javascript">
			id_language = Number('.$this->_defaultFormLanguage.');
		</script>
		<form method="post" action="'.$this->confUrl.'">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" />'.$this->l('PDF Invoice text').'</legend>
				<label style="width:100px;">'.$this->l('Text').' :</label>
				<div class="margin-form" style="padding-left:110px;">';
		// one div per language
		foreach ($this->_languages as $language)
			$this->_html .= '
					<div id="mPRESTASCOPE_PDFINVPL_TEXT_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').';float: left;">
						<textarea cols="110" rows="10" id="PRESTASCOPE_PDFINVPL_TEXT_'.$language['id_lang'].'" name="PRESTASCOPE_PDFINVPL_TEXT_'.$language['id_lang'].'">'.(array_key_exists($language['id_lang'], $values)?$values[$language['id_lang']]:'').'</textarea>
					</div>';
		$this->_html .= $this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'mPRESTASCOPE_PDFINVPL_TEXT', true);
		
		$this->_html .= '
					<div class="clear">
						<p>'.$this->l('Text to append to PDF invoices').'. '.$this->l('Could be HTML with supported tags').':</p>
						<p style="margin-left:20px;">'.htmlentities('<b>...</b>').'</p>
						<p style="margin-left:20px;">'.htmlentities('<u>...</u>').'</p>
						<p style="margin-left:20px;">'.htmlentities('<i>...</i>').'</p>
						<p style="margin-left:20px;">'.htmlentities('<a href="">...</a>').'</p>
						<p style="margin-left:20px;">'.htmlentities('<img src="" width="" height="" />').' : '.$this->l('where SRC should be relative path to image file (like /img/logo.jpg), width / height are optional and expressed in mm on your page').'</p>
						<p style="margin-left:20px;">'.htmlentities('<center>...</center>').'</p>
						<p style="margin-left:20px;">'.htmlentities('<left>...</left>').' : '.$this->l('Yes, I know it\'s not a valid HTML tag').' !</p>
						<p style="margin-left:20px;">'.htmlentities('<right>...</right>').' : '.$this->l('Yes, I know it\'s not a valid HTML tag').' !</p>
						<p style="margin-left:20px;">'.htmlentities('<center>, <left>, <right>').' '.$this->l('could have one attribute').' : '.$this->l('style="color:#ff0000;" to set the text color to #ff0000').'
						<p style="margin-left:20px;">'.$this->l('Example : ').htmlentities('<center style="color:#FF0000;">My Shop name</center>').'
					</div>
				</div>
				<div class="clear"></div>
				<div class="margin-form clear pspace"><input type="submit" name="submitUpdate" value="'.$this->l('Update').'" class="button" /></div>
			</fieldset>
		</form>';

		return $this->_html;
	}
	/**
	 * Display the form to inform user that the module has to be updated
	 */
	private function displayMigrationForm($installedVersion) 
	{
		global $cookie;

		$this->_html .= '
		<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post" enctype="multipart/form-data" class="width2" style="width:100%;">
			<fieldset><legend><img src="../img/admin/nav-user.gif" />'.$this->l('Module update').'</legend>
				<div style="text-align:center; color:red; font-size:15px; font-weight:bold;">'.$this->l('Important').' !!</div>
				<div style="height:20px;">&nbsp;</div>
				<div style="text-align:center;">'.$this->l('Your module directory contains a newer version. It must be applied before going on.').'</div>
				<div style="height:10px;">&nbsp;</div>
				<div style="text-align:center;">'.$this->l('Installed version').' : '.$installedVersion.' / '.$this->l('Directory version').' : '.$this->_directoryVersion.'</div>
				<div style="height:40px;">&nbsp;</div>
				<div style="text-align:center;">
					<input type="submit" value="'.$this->l('   Update   ').'" name="submitUpdateModule" class="button" />
				</div>
			</fieldset>
		</form>';
		return $this->_html;
	}
	/* *****************************************************************************************
	 *
	 *								HOOKS
	 *
	 * ***************************************************************************************** */
	function hookDisplayPDFInvoice($params)
	{
		global $smarty, $cookie;

		// get order id from hook params (see Module.PDFInvoice function)
		$order = new Order($params['object']->id_order);
		
		// load conf value for order lang
		$value = Configuration::get('PRESTASCOPE_PDFINVPL_TEXT', $order->id_lang);
		
		// decode the HTML
		// have to use "windows-1252" instead of 'ISO-8859-15' with FPDF 1.6
		$html = html_entity_decode($value, ENT_QUOTES, 'utf-8');
		$html = iconv("UTF-8", "windows-1252", $html);
		
		return $html;
	}
	/**
	 * HOOK PDF INVOICE
	 */
	function hookPDFInvoice($params)
	{
		global $smarty, $cookie;
		
		// get order id from hook params (see Module.PDFInvoice function)
		$order = new Order($params['id_order']);
		// get also the PDF object itself from input params
		$pdf = $params['pdf'];
		
		// load conf value for order lang
		$value = Configuration::get('PRESTASCOPE_PDFINVPL_TEXT', $order->id_lang);
		
		// decode the HTML
		// have to use "windows-1252" instead of 'ISO-8859-15' with FPDF 1.6
		$html = html_entity_decode($value, ENT_QUOTES, 'utf-8');
		$html = iconv("UTF-8", "windows-1252", $html);

		// translate the HTML to PDF content
		$this->html2pdf->WriteHTML($pdf, $html);
	}
	
	// ******************************************************************************************
	//
	//
	// Tools for installation
	//
	//
	// ******************************************************************************************
	/**
	 * Check if installed Prestashop is a 1.2.x version
	 */
	public static function isPs12x() 
	{
		return self::checkPsVersion('1.2');
	}
	/**
	 * Check if installed Prestashop is a 1.3.x version
	 */
	public static function isPs13x() 
	{
		return self::checkPsVersion('1.3');
	}
	/**
	 * Check if installed Prestashop is a 1.4.x version
	 */
	public static function isPs14x() 
	{
		return self::checkPsVersion('1.4');
	}
	/**
	 * Check if installed Prestashop is a 1.5.x version
	 */
	public static function isPs15x() 
	{
		return self::checkPsVersion('1.5');
	}
	/**
	 * Check if installed Prestashop match an input radix
	 */
	public static function checkPsVersion($radixVersion) 
	{
		// get PS version
		$psVersion = _PS_VERSION_;
		if ($psVersion==null)
			return false;
		
		// look for version like 1.3.1, 1.3.7.0 or 1.4.3
		$subVersions = explode('.', $psVersion);
		$searchVersions = explode('.', $radixVersion);
		
		for ($i=0;$i<count($searchVersions);$i++) 
		{
			// compare each sub part of version
			if ($subVersions[$i] !== $searchVersions[$i]) 
				return false;
		}
		return true;
	}
	/**
	 * Manage datas migration for an already installed version
	 * Check for each version of version history. Start migrating after currently installed version,
	 * and stop migrating after target version achieved.
	 * Returns true if the migration is ok or false elsewhere
	 */
	private function migrateVersion($installedVersion)
	{
		
		// if module not installed, return
		if (!$installedVersion)
			return;

		// if the version is up-to-date, return
		if ($installedVersion===$this->_directoryVersion)
			return;
			
		// look for installed version in the history
		$migrate = false;
		foreach($this->_versionHistory as $v)
		{
			// apply migrate
			if ($migrate)
			{
				$migrationFunction = 'migrate_to_'.str_replace ('.', '_', $v);
				if ($this->$migrationFunction())
					Configuration::updateValue('PRESTASCOPE_PDFINVPL_VERSION',  $v);
				else	// some issue during migration, so interrupt the migration
					return false;					
			}
			// start to migrate after installed version
			if ($v === $installedVersion) 
				$migrate = true;
			// stop to migrate after target version
			if ($v === $this->_directoryVersion) 
				$migrate = false;
		}

		return true;
	}
	private function migrate_to_1_0() 
	{
		//echo 'migration to 1.0<br />';
		return true;
	}
	private function migrate_to_1_1() 
	{
		//echo 'migration to 1.1<br />';
		return true;
	}
	private function migrate_to_1_2() 
	{
		// Multi-lingual support
		// So moving PRESTASCOPE_PDFINVPL_TEXT to multilingual configuration value
		
		// get single current value
		$value = Configuration::get('PRESTASCOPE_PDFINVPL_TEXT');
		
		// create an array with [id_lang=>value]
		$values = array();
		foreach ($this->_languages as $langParam)
			$values[$langParam['id_lang']] = $value;
		
		// update the configuration
		Configuration::updateValue('PRESTASCOPE_PDFINVPL_TEXT', $values, true);
			
		return true;
	}
	private function migrate_to_1_3() 
	{
		return true;
	}
	// ******************************************************************************************
	//
	//
	// Some other tools
	//
	//
	// ******************************************************************************************
	/** 
	 * Load an array : Array ( [0] => Array ( [id_lang] => 1 [value] => the_text_for_lang1 ) [1] => Array ( [id_lang] => 2 [value] => the_text_for_lang2 ) ... )
	 */
	private function loadConfiguration($key)
	{
		$query = '
		SELECT cl.`id_lang`, cl.`value` AS value
		FROM `'._DB_PREFIX_.'configuration` c
		LEFT JOIN `'._DB_PREFIX_.'configuration_lang` cl ON c.`id_configuration` = cl.`id_configuration` 
		WHERE c.`name` = \''.pSQL($key).'\'';

		$result = Db::getInstance()->ExecuteS($query);;
		$values = array();
		foreach ($result as $r) 
			$values[$r['id_lang']] = $r['value'];
			
		return $values;
	}
}

?>
