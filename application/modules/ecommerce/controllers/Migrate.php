<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends MY_Controller
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->load->library('migration');
	}

	public function run()
	{
		$module = 'ecommerce';
		$this->migration->set_module($module);
		$resultMigration = $this->migration->latest();
		if ($resultMigration)
		{
			echo "Migrate ran success for Module ".$module."!";
		}
	}
}