<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Migration extends CI_Migration
{
	private $_module;

	function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function set_module($module)
	{
		$this->_module = $module;
	}

	public function latest()
	{
		$migrations = $this->find_migrations();

		if (empty($migrations))
		{
			$this->_error_string = $this->lang->line('migration_none_found');
			return FALSE;
		}

		$last_migration = basename(end($migrations));

		// Calculate the last migration step from existing migration
		// filenames and proceed to the standard version migration
		return $this->version($this->_get_migration_number($last_migration));
	}

	public function find_migrations()
	{
		$migrations = array();

		$migration_path = APPPATH.'modules/'.$this->_module.'/migrations/*_*.php';
		// Load all *_*.php files in the migrations path
		// foreach (glob($this->_migration_path.'*_*.php') as $file)
		foreach (glob($migration_path) as $file)
		{
			$name = basename($file, '.php');

			// Filter out non-migration files
			if (preg_match($this->_migration_regex, $name))
			{
				$number = $this->_get_migration_number($name);

				// There cannot be duplicate migration numbers
				if (isset($migrations[$number]))
				{
					$this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $number);
					show_error($this->_error_string);
				}

				$migrations[$number] = $file;
			}
		}

		ksort($migrations);
		return $migrations;
	}
}