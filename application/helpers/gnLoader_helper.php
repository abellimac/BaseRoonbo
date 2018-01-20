<?php
/**
 * load a Model to include it in the project
 */
function gnLoadModel($modelClassPath) {
    var_dump($modelClassPath);
    gnLoadFile(getRealPathFromCiPath($modelClassPath, "models"));
}

function gnLoadLibrary($libraryClass) {
    gnLoadFile(getRealPathFromCiPath($libraryClass, "libraries"));
}

function gnLoadClass($classPath, $ciDirectory) {
    gnLoadFile(getRealPathFromCiPath($classPath, $ciDirectory));
}

function gnLoadFile($filePath) {
    if (file_exists($filePath)) {
        require_once $filePath;
    } else {
        throw new Exception($filePath." not found!", 1);
    }
}

/**
 * Returns the real class path of a given CI path, with or without module name.
 * @param string $ciClassPath Codeigniter class path. i.e.: "sec/Model_User", "GN_File"
 * @param string $ciDirectory Codeigniter standard Directory. i.e.: "models", "libraries", "language"
 * @param string $fileExt (Optional) The file extension.
 */
function getRealPathFromCiPath($ciClassPath, $ciDirectory, $fileExt = "php") {
    $pathParts = explode("/", $ciClassPath);
    $className = array_pop($pathParts);
    $classPath = APPPATH;
    if (count($pathParts) > 0) {
        $moduleName = array_shift($pathParts);
        $ci = &get_instance();
        $registeredModules = $ci->config->item("modules");
        if (in_array($moduleName, $registeredModules)) {
            $classPath .= "modules/".$moduleName."/";
        }
    }
    $classPath.= $ciDirectory."/".$className.".".$fileExt;
    return $classPath;
}
