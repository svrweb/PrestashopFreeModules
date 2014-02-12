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

class Dispatcher extends DispatcherCore
{
	public $use_routes = false;
	public function loadRoutes($id_shop = NULL)
	{
		parent::loadRoutes($id_shop);
	}
}
