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

class PcXmlFreeService extends ObjectModel
{
	public $name;
	public static $definition = array(
		'table' => 'pc_xmlfree_service',
		'primary' => 'id_pc_xmlfree_service',
		'multilang' => false,
		'fields' => array(
			'name' => array(
				'type' => self::TYPE_STRING,
				'lang' => false,
				'validate' => 'isGenericName',
				'required' => true,
				'size' => 50,
			),
		)
	);
	public function delete()
	{
		$sql = "DELETE FROM `"._DB_PREFIX_.PcXmlFreeFeed::$definition['table']."` WHERE `".$this->def['primary']."` = ".(int)$this->id;
		return self::$db->execute($sql) && parent::delete();
	}
	static public function getList()
	{
		$query = new DbQuery;
		$sql = $query
			->select('`'.self::$definition['primary'].'` id')
			->select('`name`')
			->from(self::$definition['table'])
			->orderBy('id')
			->build();
		return self::$db->executeS($sql);
	}
	static public function getFeedIds($ids)
	{
		if (empty($ids))
			return array();
		$query = new DbQuery;
		$query->select('GROUP_CONCAT(`'.PcXmlFreeFeed::$definition['primary'].'`) ids')
			->from(PcXmlFreeFeed::$definition['table']);
		if (is_array($ids)) {
			$ids = array_map('intval', $ids);
			$query->where('`'.self::$definition['primary'].'` IN ('.implode(',', $ids).')');
		} else {
			$query->where('`'.self::$definition['primary'].'` = '.(int)$ids);
		}
		return explode(',', self::$db->getValue($query));
	}
}
