<?php
/**
  * Encapsulates helper functions for calling the TypeScript compiler on the command line.
  * @author ComFreek
  * @license Apache License 2.0 <http://www.apache.org/licenses/LICENSE-2.0>
  * @copyright Copyright 2012 ComFreek
  */
class TSCompiler {
	/**
	  * The temporary directory used by TSCompiler::compileToStr()
	  * @var string
	  * @see TSCompiler::compileToStr()
	  */
	public static $TMP_DIR = 'tmp';

	/**
	  * Default options which shall be used in TSCompiler::buildCommand().
	  * @see TSCompiler::buildCommand()
	  */
	protected static $DEFAULT_OPTIONS = array(
	);

	/**
	  * Hide constructor because it's a static class
	  */
	private function __construct() {
	}

	/**
	  * Builds the command string.
	  *
	  * @param array $options Options. They do not conform to TypeScript's CLI options!
	  * Valid options are:
	  *  - inputFile: input *.ts file
	  *  - outputFile: output *.js file
	  * @return string The command string
	  */
	protected static function buildCommand(Array $options) {
		$cmd = 'tsc ';
		if (isset($options['outputFile'])) {
			$cmd .= '--out ' . escapeshellarg($options['outputFile']) . ' ';
		}
		$cmd .= escapeshellarg($options['inputFile']);
		return $cmd;
	}
	
	/**
	  * Compiles a given file.
	  * @param array $options Options. See TSCompiler::buildCommand() for available options.
	  * These will also be merged with TSCompiler::DEFAULT_OPTIONS
	  * @param array $errorInfo This indexed array will receive the stdin and stderr streams if the error stream was not empty.
	  * @return Returns TRUE on success and FALSE if the error stream was not empty, i.e. when an error occured.
	  * @see TSCompiler::buildCommand()
	  */
	public static function compile(Array $options, Array &$errorInfo=array()) {
		if (is_string($options)) {
			$options = array_merge(self::$DEFAULT_OPTIONS, array('inputFile' => $options));
		}
		else {
			$options = array_merge(self::$DEFAULT_OPTIONS, $options);
		}
		
		$descriptorspec = array(
			0 => array("pipe", "r"), // stdin
			1 => array("pipe", "w"), // stdout
			2 => array("pipe", "w")  // stderr
		);

		$process = proc_open(self::buildCommand($options), $descriptorspec, $pipes, dirname(__FILE__), null);

		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		
		proc_close($process);
		
		if (empty($stderr)) {
			return true;
		}
		else {
			$errorInfo = array($stdout, $stderr);
			return false;
		}
	}
	
	/**
	  * Compiles a given file and returns the result as a string.
	  * @param array $options Options. See TSCompiler::compile().
	  * @param array $errorInfo This indexed array will receive the stdin and stderr streams if the error stream was not empty.
	  * @return Returns TRUE on success and FALSE if the error stream was not empty, i.e. when an error occured.
	  * @see TSCompiler::compile()
	  */
	public static function compileToStr(Array $options, Array &$errorInfo=array()) {
		$outputFile = tempnam(self::$TMP_DIR, 'TS_');
		$options = array(
			'inputFile' => $options['inputFile'],
			'outputFile' => $outputFile
		);
		
		if (self::compile($options, $errorInfo)) {
			$data = file_get_contents($outputFile);
			unlink($outputFile);
			return $data;
		}
		else {
			unlink($outputFile);
			return false;
		}
	}
}