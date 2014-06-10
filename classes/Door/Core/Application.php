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
 * @param \Door\Core\Library\Auth $auth
 * @param \Door\Core\Library\Database $database
 * @param \Door\Core\Library\Cookie $cookie
 * @param \Door\Core\Library\Events $events
 * @param \Door\Core\Library\HTML $html
 * @param \Door\Core\Library\Image $image
 * @param \Door\Core\Library\Layout $layout
 * @param \Door\Core\Library\Language $lang
 * @param \Door\Core\Library\Media $media
 * @param \Door\Core\Library\Models $models
 * @param \Door\Core\Library\Router $router
 * @param \Door\Core\Library\Session $session 
 * @param \Door\Core\Library\URL $url
 * @param \Door\Core\Library\Views $views
 */
class Application extends Observer {

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
	
	private $initialized = false;
	
	protected $docroot = null;
	
	protected $modpath = null;
	
	protected $vendorpath = null;

	
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
	public function initial_request($uri = null)
	{
		if( $this->initial_request === null)
		{
			if($uri === null)
			{
				$uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "";
			}
			$this->initial_request = $this->request($uri);
		}
		
		return $this->initial_request;
	}	
	
	/**
	 * 
	 * @param string $uri
	 * @return \Door\Core\Request
	 */
	public function request($uri)
	{				
		if( ! $this->initialized)
		{
			$this->initialize();
		}
		
		return new Request(trim($uri, "/"), $this);
	}
	
	public function initialize()
	{
		//initialize modules
		$modpath = $this->modpath();
		foreach(glob($modpath."/*/init.php") as $filename)
		{
			self::init_module($this, $filename);
		}		
		
		//initialize libraries
		foreach($this->libraries as $library)
		{
			$library->init();
		}
		
		$this->run_event('init', $this);
		
		$this->initialized = true;
	}
	
	public function charset()
	{
		return $this->charset;
	}	
	
	/**
	 * Get or set path to public folder
	 * Set ex.: $app->docroot(__DIR__."/public");
	 * Get ex.: $docroot = $app->docroot();
	 * @param string $docroot
	 * @return string
	 */
	public function docroot($docroot = null)
	{
		return $this->get_path('docroot', $docroot);
	}
	
	private function get_path($name, $path = null)
	{
		if($path !== null)
		{
			$this->$name = $path;
			return;
		}		
		if($this->$name === null)
		{
			throw new Exception("you must specify $name in your application");
		}
		return $this->$name;
		
	}
	
	public function apppath($apppath = null)
	{
		return $this->get_path('apppath', $apppath);
	}	
	
	public function modpath($modpath = null)
	{
		return $this->get_path('modpath', $modpath);
	}	
	
	public function vendorpath($vendorpath = null)
	{
		return $this->get_path('vendor', $vendorpath);
	}	
		
	
	/**
	 * Is it correct id
	 * @param mixed $id
	 * @return boolean true if id instance of MongoId or length of id is 24
	 */
	public function is_id($id)
	{
		return ($id instanceof \MongoId) || strlen($id) == 24;
	}
	
	/**
	 * Is application in production
	 * @return boolean
	 */
	public function is_production()
	{
		return $this->environment == self::PRODUCTION;
	}
	
	private static function init_module(Application $app, $filename)
	{
		require $filename;
	}
	
	/**
	 * Find files in modules
	 * @param type $dir
	 * @param type $file
	 * @param type $ext
	 * @return type
	 */
	public function find_files($dir, $file, $ext)
	{
		$dir = str_replace("/", "", $dir);
		$file = str_replace(".","", $file);
		
		$path = $dir."/".$file.".".$ext;				
		
		$files = glob($this->modpath()."/*/".$path);
		
		$app_filename = $this->apppath()."/".$path;
		
		if(file_exists($app_filename))
		{
			array_unshift($files, $app_filename);
		}		
		
		return $files;
	}
	
	
	
}
