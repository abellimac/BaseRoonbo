<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_migration extends CI_Migration
{
	/**
	 * Current migration version
	 *
	 * @var mixed
	 */
	// protected $_migration_version = 0;

	/**
	 * Set the module to should be apply the migration.
	 * @param    string $current_module Target module.
	 */
	// public function set_module ( $current_module = 'home' )
	// {
		// $this->_add_to_log( "Cambiando modulo de $this->_current_module a $current_module" );
		// $this->_current_module = $current_module;
	// }

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @param	string	$target_version	Target schema version
	 * @return	mixed	TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	// public function version($target_version)
	// {
	// 	echo "herer after to ci_migrations";
	// }

	public function index()
	{
		echo "echo index MY_Migration";
	}
}