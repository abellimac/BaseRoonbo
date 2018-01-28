<?php
class Model_Migrate
{
	const ID_FIELD = '';
	const TABLE_NAME = '';
	protected $_id;
	protected $_module;
	protected $_version;

	function __construct()
	{
		parent::__construct();
	}

	public function getVersion()
	{
		return $this->_version;
	}

	public function setVersion($version)
	{
		$this->_version = $version;
	}

	public function getModule()
	{
		return $this->_module;
	}

	public function setModule($module)
	{
		$this->_module = $module;
	}

	// function init()
}