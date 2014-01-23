<?php
/**
 * To translate a HTML text into PDF format using FPDF API.
 *
 * History :
 * 	@version 1.0 (2012-02-21) : 1st class version
 */

class Pss_Html2Fpdf 
{
	var $HREF;
	var $B;
	var $I;
	var $U;
	var $align;
	var $border=0;
	var $lineHeight=4;
	var $afterMulticell = false;
	var $textColor = null;
	
	// to debug HTML perser, set this to true, and add a die() function at the last line of the WriteHTML(...) function
	var $DEBUG_HTML = false;
	
	/** 
	 * Parse some simple HTML to produce FPDF content
	 */
	public function WriteHTML($pdf, $html)
	{
		// Parseur HTML
		$html = str_replace("\n",' ',$html);
		$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				if (strlen($e)>0)
				{
					if ($this->DEBUG_HTML) echo '<b>deal with key:'.$i.' --> display text :'.$e.' (len='.strlen($e).')</b><br />';
					// apply text color if defined
					if ($this->textColor!=null)
					{
						if ($this->DEBUG_HTML) echo 'apply text color r:'.$this->textColor[0].', g:'.$this->textColor[1].', b:'.$this->textColor[2].'<br />';
						$pdf->SetTextColor($this->textColor[0],$this->textColor[1],$this->textColor[2]);
					}
					else
					{
						if ($this->DEBUG_HTML) echo 'reset text color<br />';
						$pdf->SetTextColor(255,0,0);
					}
					// Texte
					if($this->HREF)
						$this->PutLink($pdf, $this->HREF,$e);
					else
					{
						if ($this->align)
						{
							if ($this->align=='CENTER')
								$cellAlign = 'C';
							elseif ($this->align=='RIGHT')
								$cellAlign = 'R';
							else
								$cellAlign = 'L';
							if ($this->DEBUG_HTML) echo 'Multicell text ('.$e.') with align '.$this->align.' --> cell align : '.$cellAlign.' and border '.$this->border.'<br />';
							// in multicells, replace <br /> by \n
							$e = str_replace('<br />', "\n", $e);
							$pdf->MultiCell(0, $this->lineHeight, $e, intVal($this->border), $cellAlign);
							$this->afterMulticell = true;
						}
						else
						{
							if ($this->DEBUG_HTML) echo 'Write text ('.$e.') with write<br />';
							$pdf->Write($this->lineHeight,$e);
							$this->afterMulticell = false;
						}
					}
				}
			}
			else
			{
				// Balise
				if($e[0]=='/')
				{
					$this->CloseTag($pdf, strtoupper(substr($e,1)));
				}
				else
				{
					// Extraction des attributs
					$a2 = explode(' ',$e);
					$tag = strtoupper(array_shift($a2));
					$attr = array();
					foreach($a2 as $v)
					{
						if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
							$attr[strtoupper($a3[1])] = $a3[2];
					}
					$this->OpenTag($pdf, $tag, $attr);
				}
			}
		}
	}

	function OpenTag($pdf, $tag, $attr)
	{
		if ($this->DEBUG_HTML) echo 'open tag '.$tag.' '.($attr?'With attr '.print_r($attr, true):'').'<br />';
		// Balise ouvrante
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($pdf, $tag, true);
		if($tag=='A')
			$this->HREF = $attr['HREF'];
		if($tag=='CENTER' || $tag=='RIGHT' || $tag=='LEFT')
		{
			$this->align = $tag;
			if (array_key_exists('BORDER', $attr))
				$this->border = intVal($attr['BORDER']);
			else if (array_key_exists('STYLE', $attr))
				$this->parseStyle($attr['STYLE']);
			else
				$this->border = 0;
		}
		if($tag=='BR')
		{
			if (!$this->afterMulticell)
			{
				if ($this->DEBUG_HTML) echo '<b>Carriage return</b><br />';
				$pdf->Ln($this->lineHeight);
			}
		}
		if($tag=='IMG' && array_key_exists('SRC', $attr))
		{
			$filename = _PS_ROOT_DIR_.$attr['SRC'];
			if ($this->DEBUG_HTML) echo 'insert image / src='.$filename.'<br />';
			if (file_exists($filename))
			{
				$width = 0;
				$height = 0;
				// some size attribute ?
				if (array_key_exists('WIDTH', $attr))
					$width = intval($attr['WIDTH']);
				if (array_key_exists('HEIGHT', $attr))
					$height = intval($attr['HEIGHT']);
				$pdf->Image($filename, null, null, $width, $height);
			}
			
		}
	}
	function CloseTag($pdf, $tag)
	{
		if ($this->DEBUG_HTML) echo 'close tag '.$tag.'<br />';
		// Balise fermante
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($pdf,$tag,false);
		if($tag=='A')
			$this->HREF = '';
		if($tag=='CENTER' || $tag=='RIGHT' || $tag=='LEFT')
		{
			$this->align = '';
			$this->border = 0;
			$this->textColor = null;
			$this->bgColor = null;
		}
	}
	function parseStyle($style) 
	{
		// look for attribute part like 'color:#FF0000; background:#5566aa;'
		$parts = explode(';', $style);
		foreach($parts as $part)
		{
			$part = trim($part);
			// check for color:
			if (strlen($part)>6 && substr($part, 0, 6)=='color:')
			{
				// extract color value
				$subparts = explode(':', $part);
				// remove trailing ; if any
				if (strpos($subparts[1], ';'))
					$subparts[1] = substr($subparts[1], 0, strlen($subparts[1])-1);					
				if ($this->DEBUG_HTML) echo '<b>have extracted text color '.$subparts[1].'</b><br />';
				$this->textColor = $this->html2rgb($subparts[1]);
			}
			// check for background:
			if (strlen($part)>11 && substr($part, 0, 11)=='background:')
			{
				// extract color value
				$subparts = explode(':', $part);
				// remove trailing ; if any
				if (strpos($subparts[1], ';'))
					$subparts[1] = substr($subparts[1], 0, strlen($subparts[1])-1);
				if ($this->DEBUG_HTML) echo '<b>have extracted background color '.$subparts[1].'</b><br />';
				$this->bgColor = $this->html2rgb($subparts[1]);
			}
		}
	}
	function SetStyle($pdf, $tag, $enable)
	{
		if ($this->DEBUG_HTML) echo 'set style for '.$tag.' / enable : '.($enable==1?'yes':'no').'<br />';
		// Modifie le style et sélectionne la police correspondante
		$this->$tag += ($enable ? 1 : -1);
		$style = '';
		foreach(array('B', 'I', 'U') as $s)
		{
			if($this->$s>0)
				$style .= $s;
		}
		$pdf->SetFont('',$style);
	}

	function PutLink($pdf, $URL, $txt)
	{
		// Place un hyperlien
		$pdf->SetTextColor(0,0,255);
		$this->SetStyle($pdf,'U',true);
		$pdf->Write($this->lineHeight,$txt,$URL);
		$this->SetStyle($pdf,'U',false);
		$pdf->SetTextColor(0);
	}
	function html2rgb($color)
	{
		if ($color[0] == '#')
			$color = substr($color, 1);

		if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
		elseif (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1],   $color[2].$color[2]);
		else
			return false;

		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return array($r, $g, $b);
	} 


}
?>