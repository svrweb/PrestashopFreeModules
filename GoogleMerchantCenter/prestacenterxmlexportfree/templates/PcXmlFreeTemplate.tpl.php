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

class PcXmlFreeTemplate
{
	protected $common = array();
	protected $feedVars = array();
	protected $helperData = array();
	public function __construct() { }
	public function setCommonData(array $common)
	{
		$this->common = $common;
		return $this;
	}
	public function set($name, $value)
	{
		$this->feedVars[$name] = $value;
		return $this;
	}
	public function reset()
	{
		$this->feedVars = array();
		return $this;
	}
	protected function parseCustomAvailability($custom)
	{
		$opts = array(0, '#', '#', '#', 'Y-m-d', '#skipProduct');
		$custom = array_map('trim', explode(',', $custom));
		if (isset($custom[0]) && $custom[0] !== '') {
			$tmp = array_map('trim', explode(':', $custom[0]));
			if (count($tmp) === 1) {  
				$opts[0] = 0;
				$opts[1] = $opts[2] = $tmp[0];
			} elseif (count($tmp) === 3 && ctype_digit($tmp[0])) { 
				$opts[0] = (int)$tmp[0];
				$opts[1] = $tmp[1];
				$opts[2] = $tmp[2];
			}
		}
		if (isset($custom[1]) && $custom[1] !== '') {
			$tmp = array_map('trim', explode(':', $custom[1]));
			if (count($tmp) === 1) {   
				$opts[3] = $tmp[0];
			} elseif ($tmp[0] === '#') {  
				$opts[3] = '#';
				$opts[4] = !empty($tmp[1]) ? $tmp[1] : $opts[4];
			}
		}
		if (isset($custom[2]) && $custom[2] !== '') {
			$opts[5] = $custom[2];
		}
		return $opts;
	}
	protected function generatorAvailability($uid, $value, $custom = '')
	{
		if (empty($this->helperData['order_default'])) {
			$this->helperData['order_default'] = (int)Configuration::get('PS_ORDER_OUT_OF_STOCK');
		}
		if (empty($this->helperData['availability'][$uid])) {
			$this->helperData['availability'][$uid] = $this->parseCustomAvailability($custom);
		}
		$opts = $this->helperData['availability'][$uid];
		$product = $this->feedVars['product'];
		if (!$this->id_combination && $product['quantity'] > 0 || $this->id_combination && $product['variant'][$this->id_combination]['quantity'] > 0) {
			$days = preg_match('~(\\d)~', $product['available_now'][$this->feedVars['id_lang']], $m) ? $m[1] : 0;
			if ($opts[0] === 0 || $days <= $opts[0]) {
				return $opts[1] === '#' ? $days : $opts[1];
			} else {
				return $opts[2] === '#' ? $days : $opts[2];
			}
		} elseif ($product['out_of_stock'] == 1 || $product['out_of_stock'] == 2 && $this->helperData['order_default']) {
			if ($opts[3] === '#skipProduct') {
				throw new PcXmlFreeAvailabilityException;
			} elseif ($opts[3] !== '#') {
				return $opts[3] === '#skipTag' ? '' : $opts[3];
			} else {
				if ($product['available_later'][$this->feedVars['id_lang']] === '')
					return '';
				if (preg_match('~(\d{4}-\d{1,2}-\d{1,2}|\d{1,2}\.\s*\d{1,2}\.\s*\d{4})~',
						$product['available_later'][$this->feedVars['id_lang']], $m)) {
					return date($opts[4], strtotime(str_replace(' ', '', $m[1])));
				} elseif (preg_match('~(\d+)~', $product['available_later'][$this->feedVars['id_lang']], $m)) {
					return (int)$m[1];
				} else {
					return '';
				}
			}
		} else {
			if ($opts[5] === '#skipProduct')
				throw new PcXmlFreeAvailabilityException;
			return $opts[5] === '#skipTag' ? '' : $opts[5];
		}
		return ''; 
	}
	protected function helperCondition($uid, $condition, $custom = '')
	{
		if (empty($this->helperData['condition'][$uid])) {
			$values = explode(',', trim($custom, '[]')); 
			$values = array_map('trim', $values);
			$fromDb = array('new', 'bazaar', 'refurbished');
			foreach($values as $key => $translation) {
				if (isset($fromDb[$key]))
					$this->helperData['condition'][$uid][$fromDb[$key]] = $translation;
			}
		}
		return isset($this->helperData['condition'][$uid][$condition]) ? $this->helperData['condition'][$uid][$condition] : $condition;
	}
	protected function helperFtime($uid, $time, $format = 'Y-m-d\TH:i:sP')
	{
		return gmdate(trim($format), $time);
	}
	protected function helperCategories($uid, $categories, $delim = '|')
	{
		return str_replace('|', htmlspecialchars($delim, null, null, false), $categories);
	}
	protected function helperEscape($uid, $input, $custom = '')
	{
		return htmlspecialchars($input, ENT_QUOTES, 'UTF-8', false);
	}
	protected function helperStrip($uid, $input, $custom = '')
	{
		return preg_replace('~\s+~u', ' ', $input);
	}
	public function helperClean($uid, $input, $custom = '')
	{
		$input = preg_replace("/(?:\s|&nbsp;|\<br\>|\<br \/\>|\<br\/\>)+/ui", " ", $input, -1);
		$input = trim(strip_tags($input));
		return $input;
	}
	/* @methods@ */
}
