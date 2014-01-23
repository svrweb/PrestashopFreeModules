<?php

if (!defined('_PS_VERSION_')) {
	exit;
}

class AfterShip extends Module {
	public function __construct()
	{
		$this->name = 'aftership';
		$this->tab = 'AfterShip';
		$this->version = '1.0.1';
		$this->author = 'AfterShip';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('AfterShip');
		$this->description = $this->l('AfterShip Connector');
	}

	public function install()
	{
		if (parent::install() == false) {
			return false;
		}
		return true;
	}
}