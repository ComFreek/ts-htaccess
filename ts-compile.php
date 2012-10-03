<?php
/**
  * This script is invoked from .htaccess when referencing *.ts files.
	* $_GET['file'] will hold the requested filename.
	*
	* This version will always recompile the TypeScript file on-the-fly.
	*
	* Checkout the other version: ts-compile.cache.php
	*
	* @author ComFreek
	* @license Public Domain
	*/
require('TSCompiler.class.php');

$file = isset($_GET['file']) ? $_GET['file'] : NULL;

// Throw a 404 error if file does not exist
if (!isset($file) || !file_exists($file)) {
	header('HTTP/1.0 404 Not Found');
	exit;
}

$data = TSCompiler::compileToStr(array('inputFile' => $file));

header('Content-Type: application/javascript');
echo $data;
