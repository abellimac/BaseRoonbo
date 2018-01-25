<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Migration extends CI_Migration
{
	/**
	 * Set the module to should be apply the migration.
	 * @param    string $current_module Target module.
	 */
	public function set_module ( $current_module = 'home' )
	{
		$this->_add_to_log( "Cambiando modulo de $this->_current_module a $current_module" );
		$this->_current_module = $current_module;
	}
}