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

class PcXmlFreeTplGenerator
{
	const TAG_OPEN = "\xc2\x98";
	const TAG_CLOSE = "\xc2\x9c";
	const ATTR_OPEN = "\xc2\x96";
	const ATTR_CLOSE = "\xc2\x97";
	const VAR_OPEN = "\xc2\x86";
	const VAR_CLOSE = "\xc2\x87";
	const SEP = "\xc2\x91";
	protected $phpTemplate;
	protected $replace = '/* @methods@ */';
	protected $stack = array();
	protected $uid = 0;
	protected $allowEmptyTags = false;
	protected $allowedHelpers = array();
	protected $allowedGenerators = array();
	protected $allowedProperties = array();
	protected $buffer = array();
	protected $allowedModifiers = array(
		'CDATA' => '', 'NOCDATA' => '', 
		'HTML' => '', 'NOHTML' => '', 
	);
	public function __construct(array $allowedProperties)
	{
		$this->allowedProperties = $allowedProperties;
	}
	public function setSource($phpTemplate)
	{
		$this->parsePhpTemplate($this->phpTemplate = $phpTemplate);
		return $this;
	}
	public function allowEmptyTags($isAllowed = true)
	{
		$this->allowEmptyTags = (bool) $isAllowed;
		return $this;
	}
	protected function parsePhpTemplate($source)
	{
		$watch = false;
		foreach(token_get_all($source) as $token) {
			if (!is_array($token))
				$token = array(0, $token);
			if ($token[0] === T_FUNCTION) {
				$watch = true;
			} elseif ($watch && $token[0] === T_STRING) {
				if (substr($token[1], 0, 6) === 'helper') {
					$this->allowedHelpers[strtolower(substr($token[1], 6))] = $token[1];
				}
				if (substr($token[1], 0, 9) === 'generator') {
					$this->allowedGenerators[strtolower(substr($token[1], 9))] = $token[1];
				}
				$watch = false;
			}
		}
	}
	public function getTemplate()
	{
		$tmp = explode($this->replace, $this->phpTemplate);
		return $tmp[0] . implode('', $this->stack) . $tmp[1];
	}
	public function reset()
	{
		$this->stack = array();
		$this->buffer = array();
		return $this;
	}
	public function addBlock($name, $xml)
	{
		if (isset($this->stack[$name]))
			throw new LogicException(sprintf($this->l('Error creating the XML template. Block with the name "%s" already exists.'), $name));
		$xml = preg_replace('~\{.+\}~Us', self::VAR_OPEN.'$0'.self::VAR_CLOSE, $xml);
		$xml = preg_replace('~(?<=\s)[a-z:0-9_]+=(["\'])'.self::VAR_OPEN.'.+'.self::VAR_CLOSE.'\\1(?=\s|>|/>)~Uis', self::ATTR_OPEN.'$0'.self::ATTR_CLOSE, $xml);
		$xml = preg_replace('~(<([a-z:0-9_]+)\W[^>]*'.self::VAR_OPEN.'[^>]+>)([^<]*)(</\\2>)' 
				.'|(<([a-z:0-9_]+)>)([^<]*'.self::VAR_OPEN.'[^<]+)(</\\6>)' 
				.'|(<[a-z:0-9_]+\W[^<>]*'.self::VAR_OPEN.'[^<>]*\s*/>)'  
				.'~Uis',
			self::TAG_OPEN.'$1$5$9'.self::SEP.'$3$7'.self::SEP.'$4$8'.self::TAG_CLOSE, $xml);
		$this->buffer = array('main' => '', 'product' => '' );
		$this->buffer['main'] = "\n\tpublic function $name(array \$product = array()) \n\t{ ";
		$this->buffer['product'] = "\n\t\t\$output = ''";
		$bufferKey = 'product';
		$isTag = false;
		$splitChars = self::TAG_OPEN.'|'.self::TAG_CLOSE
;
		foreach (preg_split('~(?='.$splitChars.')|(?<='.$splitChars.')~', $xml) as $token) {
			if (strlen($token) === 2 && ord($token[1]) >= 0x80 && ord($token[1]) <= 0x9f) {
				if ($token === self::TAG_OPEN) {
					$isTag = true;
				} elseif ($token === self::TAG_CLOSE) {
					$isTag = false;
				}
				continue;
			}
			if ($isTag) {
				$this->buffer[$bufferKey] .= "\n\t\t." . $this->parseTag($token);
			} elseif ($token !== '') {
				$this->buffer[$bufferKey] .= "\n\t\t." . var_export($token, true);
			}
		}
		$this->buffer['main'] .= $this->buffer['product']
			. '."\\n"' . "; \n\t\treturn \$output; \n\t} \n";
		$this->stack[$name] = preg_replace('~[\\x80-\\x9f]~u', '', $this->buffer['main']);
		$this->buffer = array();
		return $this;
	}
	protected function parseTag($input)
	{
		$isAttr = false;
		$isVar = false;
		$index = 0; 
		$buffer = array($index => array(), 'attr' => '');
		$varNames = array($index => array()); 
		$splitChars = self::ATTR_OPEN.'|'.self::ATTR_CLOSE.'|'.self::VAR_OPEN.'|'.self::VAR_CLOSE.'|'.self::SEP;
		foreach (preg_split('~(?='.$splitChars.')|(?<='.$splitChars.')~', $input) as $token) {
			if (strlen($token) < 1)
				continue;
			if (strlen($token) === 2 && ord($token[1]) >= 0x80 && ord($token[1]) <= 0x9f) {
				if ($token === self::SEP) {
					$index++;
					$buffer[$index] = array();
					$varNames[$index] = array();
				} elseif ($token === self::ATTR_OPEN) {
					$buffer['attr'] = '';
					$isAttr = true;
				} elseif ($token === self::ATTR_CLOSE) {
					$attr = $this->parseAttribute($buffer['attr']);
					$varNames[$index][] = $attr->fullName;
					$buffer[$index][] = $attr->replace;
					$isAttr = false;
				} elseif ($token === self::VAR_OPEN) {
					$buffer['attr'] .= $isAttr ? $token : '';
					$isVar = true;
				} elseif ($token === self::VAR_CLOSE) {
					$buffer['attr'] .= $isAttr ? $token : '';
					$isVar = false;
				}
				continue;
			}
			if ($isAttr) {
				$buffer['attr'] .= $token;
			} elseif ($isVar) {
				$var = $this->parseVariable($token);
				$varNames[$index][] = $var->fullName;
				$buffer[$index][] = (!$this->allowEmptyTags ? '('.$var->fullName." !== '' ? " : '')
					.$var->replaceWithEscape
					.(!$this->allowEmptyTags ? " : '')" : '');
			} else {
				$buffer[$index][] = var_export($token, 1);
			}
		}
		unset($buffer['attr']);
		$output = '';
		if (!$this->allowEmptyTags) {
			$output .= '(' . implode(" !== '' || ", (!empty($varNames[1]) ? $varNames[1] : $varNames[0])) . " !== '' ? ";
		}
		$output .= implode('.', $buffer[0]); 
		if (!empty($buffer[1])) { 
			$output .= '.' . implode('.', $buffer[1]);
			$output .= '.' . implode('.', $buffer[2]);
		}
		if (!$this->allowEmptyTags) {
			$output .= " : '')";
		}
		return $output;
	}
	protected function parseAttribute($input)
	{
		$attr = new stdClass;
		$stack = array();
		$isVar = false;
		$splitChars = self::VAR_OPEN.'|'.self::VAR_CLOSE;
		foreach (preg_split('~(?='.$splitChars.')|(?<='.$splitChars.')~', $input) as $token) {
			if (strlen($token) < 1)
				continue;
			if ($token === self::VAR_OPEN) {
				$isVar = true;
				continue;
			} elseif ($token === self::VAR_CLOSE) {
				$isVar = false;
				continue;
			}
			if ($isVar) {
				$var = $this->parseVariable($token);
				$attr->fullName = $var->fullName;
				$stack[] = (!$this->allowEmptyTags ? '('.$var->fullName." !== '' ? " : '')
						.$var->replaceWithEscape
						.(!$this->allowEmptyTags ? " : '')" : '');
			} else {
				$stack[] = var_export($token, true);
			}
		}
		$attr->replace = implode('.', $stack);
		return $attr;
	}
	protected function parseVariable($input)
	{
		if (!preg_match('~\{([a-z_]+)(?:\s*\:\s*(["\'])(.+)(?<!\\\\)\\2)?(?:\s*\:\s*([A-Z_, ]+)\s*)?\}~Uis', $input, $matches))
			throw new InvalidArgumentException(sprintf($this->l('Variable %s has wrong format or contains illegal characters. Are permitted only original letters (without diacritical marks) and underscores.'), $input));
		$varName = strtolower($matches[1]);
		if (!isset($this->allowedProperties[$varName]))
			throw new InvalidArgumentException(sprintf($this->l('XML template uses the undefined property {%s}.'), $varName));
		$this->uid++;
		$var = new stdClass;
		$var->name = $varName;
			$context = '';
			if (isset($this->allowedProperties[$var->name]['context'])) {
				if ($this->allowedProperties[$var->name]['context'] === PrestaCenterXmlExportFree::CONTEXT_ALL)
					$context = '$this->common';
				elseif ($this->allowedProperties[$var->name]['context'] === PrestaCenterXmlExportFree::CONTEXT_FILE)
					$context = '$this->feedVars';
				else
					$context = '$product';
			} else {
				$context = '$product';
			}
			$var->fullName = $context ? $context . "['".$var->name."']" : '$'.$var->name;
			if (!empty($this->allowedProperties[$var->name]['key'])) {
				foreach (explode('|', $this->allowedProperties[$var->name]['key']) as $key) {
					$var->fullName .= "[\$this->feedVars['".$key."']]";
				}
			}
		$var->param = isset($matches[3]) && $matches[3] !== '' ? "'".str_replace('"', "'", stripslashes($matches[3]))."'" : '';
		$var->helpers = $tmp = array();
		if (!empty($this->allowedProperties[$var->name]['helper']))
			$tmp += array_map('strtolower', explode('|', $this->allowedProperties[$var->name]['helper']));
		if ($var->param)
			$tmp[] = $var->name;
		$tmp = array_unique($tmp);
		foreach ($tmp as $name) {
			if (isset($this->allowedGenerators[$name])) {
				$tplCode = "\n\t\t".$var->fullName.' = $this->'.$this->allowedGenerators[$name]
					.'('.$this->uid.', '.$var->fullName.($var->param ? ', '.$var->param : '').');';
				$this->buffer['main'] .= $tplCode;
			} elseif (isset($this->allowedHelpers[$name])) {
				$var->helpers[$this->allowedHelpers[$name]] = true;
			} elseif (function_exists($name)) {
				$var->helpers[$name] = false;
			} else {
				throw new InvalidArgumentException(sprintf($this->l('For properties {%1$s} is set the undefined helper "%2$s".'), $var->name, $name));
			}
		}
		$var->replace = $var->fullName;
		foreach ($var->helpers as $name => $isInternal) {
			if ($name === $this->allowedHelpers['escape']) { continue; }
			if ($isInternal) {
				$var->replace = '$this->'.$name.'('.$this->uid.', '.$var->replace
					.($var->param ? ', '.$var->param : '').')';
			} else {
				$var->replace = $name.'('.$var->replace.')';
			}
		}
		$var->replaceWithEscape = '$this->helperEscape('.$this->uid.', '.$var->replace.')';
		return $var;
	}
	public function l($string)
	{
		return Context::getContext()->controller->module->l($string);
	}
}
