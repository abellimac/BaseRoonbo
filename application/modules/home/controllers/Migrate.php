<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends MY_Migration
{
	public function run_migration ($module, $version)
	{
		if ($module != null && $version != null)
		{
			$mod = $module;
			$ver = $version;
		}
		else
		{
			return false;
		}

		$this->migration->set_module($mod);
		$result = $this->migration->version($ver);
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

		$this->buildResponseJSON($response, JSON_PRETTY_PRINT);
	}
}