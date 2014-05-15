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
 * @param \Door\Core\Library\Layout $layout
 * @param \Door\Core\Library\Media $media
 * @param \Door\Core\Library\Models $models
 * @param \Door\Core\Library\Router $router
 * @param \Door\Core\Library\URL $url
 * @param \Door\Core\Library\Views $views
 */
class Application {

	// Release version
	const VERSION  = '0.0.1';

	// Common environment type constants for consistency and convenience
	const PRODUCTION  = 10;
	const STAGING     = 20;
	const TESTING     = 30;
	const DEVELOPMENT = 40;		

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
	
	protected $charset = 'utf-8';
	
	/**
	 *
	 * @var Request
	 */
	private $initial_request = null;

	
	/**
	 * Creating aplication. 
	 * @param int $environment
	 */
	public function __construct($environment = self::DEVELOPMENT){					
		$this->environment = $environment;		
	}					
	
	/**
	 * get environment of application
	 * @return string
	 */
	public function environment()
	{
		return $this->environment;
	}
	
	public function unload()
	{
		$this->modules_objects = array();
		$this->libraries = array();
		$this->registered_libraries = array();
		$this->initial_request = null;
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
	
	public function set_initial_request(Request $request)
	{
		$this->initial_request = $request;
	}
	
	/**
	 * 
	 * @return Request
	 */
	public function initial_request()
	{
		if( $this->initial_request !== null)
		{
			return $this->initial_request;
		}
		$uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "";
		return $this->request($uri);
	}	
	
	/**
	 * 
	 * @param string $uri
	 * @return \Door\Core\Request
	 */
	public function request($uri)
	{				
		return new Request(trim($uri, "/"), $this);
	}
	
	public function charset()
	{
		return $this->charset;
	}
	
	
	

	
	
	
	
}
