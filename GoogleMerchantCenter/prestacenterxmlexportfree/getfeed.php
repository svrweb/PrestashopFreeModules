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

if (empty($_GET['file'])) {
	header("HTTP/1.0 400 Bad Request");
	exit;
}
$file = dirname(__FILE__).'/'.basename($_GET['file']);
if (!file_exists($file) || !is_readable($file)) {
	header("HTTP/1.0 503 Service Unavailable");
	header("Retry-After: 1200");
	exit;
}
$lastmod = gmdate(DATE_RFC2822, filemtime($file));
$size = filesize($file);
header("Content-Type: application/xml; charset=UTF-8");
header("Content-Length: $size");
header("Date: $lastmod");
header("Last-Modified: $lastmod");
readfile($file);
