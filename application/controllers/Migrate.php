<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('migration');
		$this->load->model("home/Migrate_model", "migrate");
	}

	public function index()
	{
		if ( ! $this->migration->latest())
		{
			show_error($this->migration->error_string());
		}
		else
		{
			echo "Migrate executed success!";
		}
	}

	public function runMigrate($version=null, $moduleName=null)
	{
		echo $this->migration->current();
		echo $this->migrate->getVersion();
	}
}