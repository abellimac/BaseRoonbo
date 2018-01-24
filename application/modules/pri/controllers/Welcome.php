<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MX_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		// $this->load->view('mes');
		echo "hello in base roonbo";
	}

	public function guardar()
	{
		gnLoadModel("pri/Model_Curso");
		// var_dump();
		// Model_curso
	}
}