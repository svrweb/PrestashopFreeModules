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

class PcXmlFreeFeed extends ObjectModel
{
	public $id_pc_xmlfree_service;
	public $id_lang;
	public $id_currency;
	public $xml_source;
	public $allow_empty_tags;
	public $filename;
	public $filesize;
	public $created;
	public static $definition = array(
		'table' => 'pc_xmlfree_feed',
		'primary' => 'id_pc_xmlfree_feed',
		'multilang' => false,
		'fields' => array(
			'id_pc_xmlfree_service' => array(
				'type' => self::TYPE_INT,
				'lang' => false,
				'validate' => 'isUnsignedInt',
				'required' => true,
			),
			'id_lang' => array(
				'type' => self::TYPE_INT,
				'lang' => false,
				'validate' => 'isUnsignedInt',
				'required' => true,
			),
			'id_currency' => array(
				'type' => self::TYPE_INT,
				'lang' => false,
				'validate' => 'isUnsignedInt',
				'required' => true,
			),
			'xml_source' => array(
				'type' => self::TYPE_HTML,
				'lang' => false,
				'validate' => 'isString',
				'required' => true,
				'size' => 2500,
			),
			'allow_empty_tags' => array(
				'type' => self::TYPE_BOOL,
				'lang' => false,
				'validate' => 'isBool',
				'required' => true,
				'default' => 0,
			),
			'filename' => array(
				'type' => self::TYPE_STRING,
				'lang' => false,
				'validate' => 'isFileName', 
				'required' => true,
				'size' => 60,
			),
			'filesize' => array(
				'type' => self::TYPE_INT,
				'lang' => false,
				'required' => false,
				'default' => 0,
			),
			'created' => array(
				'type' => self::TYPE_DATE,
				'lang' => false,
				'validate' => 'isDateFormat',
				'required' => false,
				'default' => '0000-00-00 00:00:00',
			),
		)
	);
	public function update($null_values = false, $autodate = true)
	{
		Hook::exec('actionObjectUpdateBefore', array('object' => $this));
		Hook::exec('actionObject'.get_class($this).'UpdateBefore', array('object' => $this));
		$this->clearCache();
		Context::getContext()->controller->module->parseXmlTemplate($this->xml_source);
		try {
			if (!ObjectModel::$db->update($this->def['table'], $this->getFields(), '`'.pSQL($this->def['primary']).'` = '.(int)$this->id, 0, $null_values))
				throw new RuntimeException;
		} catch (Exception $e) {
			if (self::$db->getNumberError() == 1062) {
				throw new RuntimeException(sprintf($this->l('The name of the specified XML file (%s) already exists.'), $this->filename));
			} else {
				throw $e;
			}
		}
		Hook::exec('actionObjectUpdateAfter', array('object' => $this));
		Hook::exec('actionObject'.get_class($this).'UpdateAfter', array('object' => $this));
		return true;
	}
	public function add($autodate = true, $null_values = false)
	{
		Hook::exec('actionObjectAddBefore', array('object' => $this));
		Hook::exec('actionObject'.get_class($this).'AddBefore', array('object' => $this));
		Context::getContext()->controller->module->parseXmlTemplate($this->xml_source);
		try {
			if (!self::$db->insert($this->def['table'], $this->getFields(), $null_values))
				throw new RuntimeException;
		} catch (Exception $e) {
			if (self::$db->getNumberError() == 1062) {
				throw new RuntimeException(sprintf($this->l('The name of the specified XML file (%s) already exists.'), $this->filename));
			} else {
				throw $e;
			}
		}
		$this->id = ObjectModel::$db->Insert_ID();
		Hook::exec('actionObjectAddAfter', array('object' => $this));
		Hook::exec('actionObject'.get_class($this).'AddAfter', array('object' => $this));
		return true;
	}
	public function l($string)
	{
		return Context::getContext()->controller->module->l($string);
	}
}
