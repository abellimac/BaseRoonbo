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

	public function version($target_version)
	{
		// Note: We use strings, so that timestamp versions work on 32-bit systems
		$current_version = $this->_get_version();

		if ($this->_migration_type === 'sequential')
		{
			$target_version = sprintf('%03d', $target_version);
		}
		else
		{
			$target_version = (string) $target_version;
		}

		$migrations = $this->find_migrations();

		if ($target_version > 0 && ! isset($migrations[$target_version]))
		{
			$this->_error_string = sprintf($this->lang->line('migration_not_found'), $target_version);
			return FALSE;
		}

		if ($target_version > $current_version)
		{
			$method = 'up';
		}
		elseif ($target_version < $current_version)
		{
			$method = 'down';
			// We need this so that migrations are applied in reverse order
			krsort($migrations);
		}
		else
		{
			// Well, there's nothing to migrate then ...
			return TRUE;
		}

		// Validate all available migrations within our target range.
		//
		// Unfortunately, we'll have to use another loop to run them
		// in order to avoid leaving the procedure in a broken state.
		//
		// See https://github.com/bcit-ci/CodeIgniter/issues/4539
		$pending = array();
		foreach ($migrations as $number => $file)
		{
			// Ignore versions out of our range.
			//
			// Because we've previously sorted the $migrations array depending on the direction,
			// we can safely break the loop once we reach $target_version ...
			if ($method === 'up')
			{
				if ($number <= $current_version)
				{
					continue;
				}
				elseif ($number > $target_version)
				{
					break;
				}
			}
			else
			{
				if ($number > $current_version)
				{
					continue;
				}
				elseif ($number <= $target_version)
				{
					break;
				}
			}

			// Check for sequence gaps
			if ($this->_migration_type === 'sequential')
			{
				if (isset($previous) && abs($number - $previous) > 1)
				{
					$this->_error_string = sprintf($this->lang->line('migration_sequence_gap'), $number);
					return FALSE;
				}

				$previous = $number;
			}

			include_once($file);
			$class = 'Migration_'.ucfirst(strtolower($this->_get_migration_name(basename($file, '.php'))));

			// Validate the migration file structure
			if ( ! class_exists($class, FALSE))
			{
				$this->_error_string = sprintf($this->lang->line('migration_class_doesnt_exist'), $class);
				return FALSE;
			}
			elseif ( ! is_callable(array($class, $method)))
			{
				$this->_error_string = sprintf($this->lang->line('migration_missing_'.$method.'_method'), $class);
				return FALSE;
			}

			$pending[$number] = array($class, $method);
		}

		// Now just run the necessary migrations
		foreach ($pending as $number => $migration)
		{
			log_message('debug', 'Migrating '.$method.' from version '.$current_version.' to version '.$number);

			$migration[0] = new $migration[0];
			call_user_func($migration);
			$current_version = $number;
			$this->_update_version($current_version);
		}

		// This is necessary when moving down, since the the last migration applied
		// will be the down() method for the next migration up from the target
		if ($current_version <> $target_version)
		{
			$current_version = $target_version;
			$this->_update_version($current_version);
		}

		log_message('debug', 'Finished migrating to '.$current_version);
		return $current_version;
	}

	protected function _update_version($migration)
	{
		$row = $this->db->select('version')->get_where($this->_migration_table, array('module_name'=>$this->_module))->row();
		if ($row != NULL)
		{
			$this->db->where('module_name', $this->_module);
			$this->db->update($this->_migration_table, array(
				'version'		=> $migration,
				'module_name'	=> $this->_module
			));
		}
		else
		{
			$this->db->insert($this->_migration_table, array('version' => $migration, 'module_name' => $this->_module));
		}
	}

	protected function _get_version()
	{
		$row = $this->db->select('version')->get_where($this->_migration_table, array('module_name'=>$this->_module))->row();
		return $row ? $row->version : '0';
	}
}