<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('migration');
		loadModelHelp('home/Model_Migrate');
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
		// $this->load->library('migration');
		// echo hello();
		// echo "The version is $version, and the module name is $moduleName";
	}
}