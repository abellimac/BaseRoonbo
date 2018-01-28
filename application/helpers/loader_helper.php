<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('loadModelHelp'))
{
	/**
	 * loadModelHelp
	 *
	 * @param	string
	 * @return	mixed	depends on what the array contains
	 */
	function loadModelHelp($modelPath)
	{
		loadModuleFileHelp(getPathModel($modelPath, "models"));
	}
}

if ( ! function_exists('loadModuleFileHelp'))
{
	/**
	 * loadModuleFileHelp
	 *
	 *
	 * @param	string
	 * @return	mixed	get requiere_once
	 */
	function loadModuleFileHelp($path)
	{
		if (file_exists($path))
		{
			require_once($path);
		}
		else
		{
			// to do 
			echo 'The file does not found';
		}
	}
}

if ( ! function_exists('getPathModel'))
{
	/**
	 * getPathModel
	 *
	 *
	 * @param	string
	 * @param	string
	 * @return	get real path of model file
	 */
	function getPathModel($modelPath, $folderName)
	{
		$arrayParts = explode("/", $modelPath);
		$moduleName = $arrayParts[0];
		$modelName  = $arrayParts[1];
		$modelPath  = APPPATH;
		if (count($arrayParts) > 0)
		{
			$modelPath .= "modules/".$moduleName."/";
		}

		$modelPath .= $folderName."/".$modelName.".php";
		return $modelPath;
	}
}