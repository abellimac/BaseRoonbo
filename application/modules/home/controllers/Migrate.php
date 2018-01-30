<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends MY_Controller
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->load->library('migration');
	}

	public function runMigration ($module, $version)
	{
		// $result = $this->migration->set_module_version($module, $version);
		$this->migration->set_module($module);
		$this->migration->latest();
		// if (! $this->migration->latest())
		// {
		// 	# code...
		// }
		// if ($module != null && $version != null)
		// {
		// 	$mod = $module;
		// 	$ver = $version;
		// }
		// else
		// {
		// 	return false;
		// }

		// $this->migration->set_module($mod);
		// $result = $this->migration->version($ver);
		// $response = array();

		// if ( $result === false )
		// {
		// 	$response[ 'status' ] = 401;
		// 	$response[ 'message' ] = 'No se ha realizado la migracion a la version ' . $version . ' para el modulo ' . $module;
		// }
		// else
		// {
		// 	$response[ 'status' ] = 200;
		// 	$response[ 'message' ] = 'Actualizado el modulo ' . $module . ' a la version ' . $version;
		// }
		// $response[ 'errors' ] = $this->migration->get_errors();
		// $response[ 'log' ] = $this->migration->get_log();

		// $this->buildResponseJSON($response, JSON_PRETTY_PRINT);
	}
}