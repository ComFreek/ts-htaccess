<?php
/**
  * This script is invoked from .htaccess when referencing *.ts files.
	* $_GET['file'] will hold the requested filename.
	*
	* This version uses the small JG_Cache class in order
	* to achieve simple caching advantages. The used functions should be self-explanatory.
	* https://github.com/jonknee/JG_Cache
	*
	* You should use memcached or APC in production environments, of course.
	*
	* Checkout the other version without caching: ts-compile.php 
	*
	* @author ComFreek
	* @license Public Domain
	*/
require('TSCompiler.class.php');
require('JG_Cache/JG_Cache.php');

$file = isset($_GET['file']) ? $_GET['file'] : NULL;

// Throw a 404 error if file does not exist
if (!isset($file) || !file_exists($file)) {
	header('HTTP/1.0 404 Not Found');
	exit;
}

$file = realpath($file);
$data = '';

$cache = new JG_Cache('tmp');
$cacheName = 'TS_'.$file;

// Set 10 seconds for testing purpose
$data = $cache->get($cacheName, 10);

// Recompile if cache is invalid
if ($data === false) {
	$data = TSCompiler::compileToStr(array('inputFile' => $file));
	$cache->set($cacheName, $data);
}

header('Content-Type: application/javascript');
echo $data;