<?php

class Cible_LessVersion{

	const version = '1.7.0.1';			// The current build number of less.php
	const less_version = '1.7';			// The less.js version that this build should be compatible with
    const cache_version = '170';		// The parser cache version

}

/**
 * Utility for handling the generation and caching of css files
 *
 * @package Less
 * @subpackage cache
 *
 */
class Cible_LessCache{

	public static $cache_dir = false;		// directory less.php can use for storing data


	/**
	 * Save and reuse the results of compiled less files.
	 * The first call to Get() will generate css and save it.
	 * Subsequent calls to Get() with the same arguments will return the same css filename
	 *
	 * @param array $less_files Array of .less files to compile
	 * @param array $parser_options Array of compiler options
	 * @param boolean $use_cache Set to false to regenerate the css file
	 * @return string Name of the css file
	 */
	public static function Get( $less_files, $parser_options = array(), $use_cache = true ){


		//check $cache_dir
		if( isset($parser_options['cache_dir']) ){
			Cible_LessCache::$cache_dir = $parser_options['cache_dir'];
		}

		if( empty(Cible_LessCache::$cache_dir) ){
			throw new Exception('cache_dir not set');
		}

		self::CheckCacheDir();

		// generate name for compiled css file
		$less_files = (array)$less_files;
		$hash = md5(json_encode($less_files));
 		$list_file = Cible_LessCache::$cache_dir.'lessphp_'.$hash.'.list';


		if( $use_cache === true ){

	 		// check cached content
	 		if( file_exists($list_file) ){


				$list = explode("\n",file_get_contents($list_file));
				$compiled_name = self::CompiledName($list);
				$compiled_file = Cible_LessCache::$cache_dir.$compiled_name;
				if( file_exists($compiled_file) ){
					@touch($list_file);
					@touch($compiled_file);
					return $compiled_name;
				}
			}

		}

		$compiled = self::Cache( $less_files, $parser_options );
		if( !$compiled ){
			return false;
		}


		//save the file list
		$cache = implode("\n",$less_files);
		file_put_contents( $list_file, $cache );


		//save the css
		$compiled_name = self::CompiledName( $less_files );
		file_put_contents( Cible_LessCache::$cache_dir.$compiled_name, $compiled );


		//clean up
		self::CleanCache();

		return $compiled_name;
	}

	/**
	 * Force the compiler to regenerate the cached css file
	 *
	 * @param array $less_files Array of .less files to compile
	 * @param array $parser_options Array of compiler options
	 * @return string Name of the css file
	 */
	public static function Regen( $less_files, $parser_options = array() ){
		return self::Get( $less_files, $parser_options, false );
	}

	public static function Cache( &$less_files, $parser_options = array() ){


		// get less.php if it exists
		$file = dirname(__FILE__) . '/Less.php';
		if( file_exists($file) && !class_exists('Cible_LessParser') ){
			require_once($file);
		}

		$parser_options['cache_dir'] = Cible_LessCache::$cache_dir;
		$parser = new Cible_LessParser($parser_options);


		// combine files
		foreach($less_files as $file_path => $uri_or_less ){

			//treat as less markup if there are newline characters
			if( strpos($uri_or_less,"\n") !== false ){
				$parser->Parse( $uri_or_less );
				continue;
			}

			$parser->ParseFile( $file_path, $uri_or_less );
		}

		$compiled = $parser->getCss();


		$less_files = $parser->allParsedFiles();

		return $compiled;
	}


	private static function CompiledName( $files ){

		//save the file list
		$temp = array(Cible_LessVersion::cache_version);
		foreach($files as $file){
			$temp[] = filemtime($file)."\t".filesize($file)."\t".$file;
		}

		return 'lessphp_'.sha1(json_encode($temp)).'.css';
	}


	public static function SetCacheDir( $dir ){
		Cible_LessCache::$cache_dir = $dir;
	}

	public static function CheckCacheDir(){

		Cible_LessCache::$cache_dir = str_replace('\\','/',Cible_LessCache::$cache_dir);
		Cible_LessCache::$cache_dir = rtrim(Cible_LessCache::$cache_dir,'/').'/';

		if( !file_exists(Cible_LessCache::$cache_dir) ){
			if( !mkdir(Cible_LessCache::$cache_dir) ){
				throw new Cible_LessException_Parser('Less.php cache directory couldn\'t be created: '.Cible_LessCache::$cache_dir);
			}

		}elseif( !is_dir(Cible_LessCache::$cache_dir) ){
			throw new Cible_LessException_Parser('Less.php cache directory doesn\'t exist: '.Cible_LessCache::$cache_dir);

		}elseif( !is_writable(Cible_LessCache::$cache_dir) ){
			throw new Cible_LessException_Parser('Less.php cache directory isn\'t writable: '.Cible_LessCache::$cache_dir);

		}

	}


	public static function CleanCache(){
		static $clean = false;

		if( $clean ){
			return;
		}

		$files = scandir(Cible_LessCache::$cache_dir);
		if( $files ){
			$check_time = time() - 604800;
			foreach($files as $file){
				if( strpos($file,'lessphp_') !== 0 ){
					continue;
				}
				$full_path = Cible_LessCache::$cache_dir.'/'.$file;
				if( filemtime($full_path) > $check_time ){
					continue;
				}
				unlink($full_path);
			}
		}

		$clean = true;
	}

}