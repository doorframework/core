<?php

namespace Door\Core;
use Exception;

/**
 * Core application class for Door framework.
 * 
 * @package    Door
 * @category   Core
 * @author     Sachik Sergey
 * @license    MIT
 * 
 * @param \Door\Core\Library\Arr $arr
 * @param \Door\Core\Library\Database $database
 * @param \Door\Core\Library\Events $events
 * @param \Door\Core\Library\HTML $html
 * @param \Door\Core\Library\Image $image
 * @param \Door\Core\Library\Media $media
 * @param \Door\Core\Library\Models $models
 * @param \Door\Core\Library\Router $router
 * @param \Door\Core\Library\URL $url
 * @param \Door\Core\Library\Views $views
 */
class Door {

	// Release version and codename
	const VERSION  = '0.0.1';

	// Common environment type constants for consistency and convenience
	const PRODUCTION  = 10;
	const STAGING     = 20;
	const TESTING     = 30;
	const DEVELOPMENT = 40;
	
	/**
	 * Is application initialized
	 * @var bool
	 */
	private $initialized = false;
	
	/**
	 * Application charset
	 * @var string
	 */
	private $charset = 'utf-8';

	/**
	 * @var string 
	 */
	private $environment = self::DEVELOPMENT;	
	
	/**
	 * Registered libraries
	 * @var array
	 */
	private $registered_libraries = array();
	
	/**
	 * Library objects
	 * @var array
	 */
	private $libraries = array();
	

	
	/**
	 * Creating aplication. 
	 * @param int $environment
	 * @param string $charset
	 * @throws Exception
	 */
	public function __construct($environment = self::DEVELOPMENT, $charset = null){
					
		$this->environment = $environment;		
		
		if($charset !== null)
		{
			$this->charset = $charset;
		}				
	}					
	
	/**
	 * get environment of application
	 * @return string
	 */
	public function environment()
	{
		return $this->environment;
	}
	
	public function init()
	{
		$this->initialized = true;
	}
	
	public function unload()
	{
		$this->modules_objects = array();
		$this->libraries = array();
		$this->registered_libraries = array();
		
		$this->initialized = false;
	}
	
	public function register_library($name, $class_name)
	{
		$this->registered_libraries[$name] = str_replace("/", "\\", $class_name);
		
	}
	
	public function __get($name)
	{
		if( ! isset($this->libraries[$name]))
		{
			if( ! isset($this->registered_libraries[$name]))
			{
				throw new Exception("library $name not registered");
			}
			
			$lib = $this->registered_libraries[$name];			
			$this->libraries[$name] = new $lib($this);
			
		}		
		return $this->libraries[$name];		
	}
	
	/**
	 * Check if library exists
	 * @param string $name
	 * @return bool
	 */
	public function library_exists($name)
	{
		return isset($this->registered_libraries[$name]);
	}
	
	/**
	 * Get application charset
	 * @return string
	 */
	public function charset()
	{
		return $this->charset;
	}
	
	
	

	
	
	
	
}
