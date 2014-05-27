<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Library;
use Door\Core\Route;
use Door\Core\Request;
use Door\Core\Wrapper;

/**
 * Description of Router
 *
 * @author serginho
 */
class Router extends \Door\Core\Library {
	
	/**
	 *
	 * @var array
	 */
	protected $routes = array();
	
	/**
	 *
	 * @var array
	 */
	protected $controller_aliases = array();
	
	/**
	 *
	 * @var array
	 */
	protected $controller_configs = array();
	
	/**
	 *
	 * @var array
	 */
	protected $wrapper_aliases = array();
	
	/**
	 *
	 * @var array
	 */
	protected $wrapper_configs = array();
	
	/**
	 *
	 * @var array
	 */
	protected $wrappers = array();
	
	
	/**
	 * Stores a named route and returns it. The "action" will always be set to
	 * "index" if it is not defined.
	 *
	 *     $app->router->add('default', "/a/b/c", "/Controller/Index")
	 *         ->defaults(array(
	 *             'abc' => 'cde',
	 *         ));
	 *
	 * @param   string  $name           route name
	 * @param   string  $uri            URI pattern
	 * @param   string  $controller     Controller class name or alias
	 * @param   array   $regex          regex patterns for route keys
	 * @return  Route
	 */	
	public function add($name, $uri, $controller, array $regex = array()){
		
		$controller_config = array();
		
		if(isset($this->contoller_aliases[$controller]))
		{
			$controller_config = $this->controller_configs[$controller];
			$controller = $this->contoller_aliases[$controller];			
		}
		
		$route = new Route($uri, $controller, $regex);
		
		$route->controller_config($controller_config);
		
		$this->routes[$name] = $route;
		
		return $route;		
	}	

	
	/**
	 * Get the name of a route.
	 *
	 *     $name = Route::name($route)
	 *
	 * @param   Route   $route  instance
	 * @return  string
	 */
	public static function name(Route $route)
	{
		return array_search($route, $this->routes);
	}	
	
	/**
	 * 
	 * @param type $name
	 * @return Route
	 * @return null
	 */
	public function route($name)
	{
		if( ! isset($this->routes[$name]))
		{
			return null;
		}
		return $this->routes[$name];
	}
	
	public function get_workers(Request $request)
	{
		/*@var Route $route */
		foreach($this->routes as $route)
		{
			$params = $route->matches($request);
			if($params !== false)			
			{
				$request->set_params($params);
				$controller_config = $route->controller_config();
				$controller_class = $route->controller();
				$controller = new $controller_class($this->app, $request, $controller_config);
				
				$wrappers = array();
				$wrappers_data = $route->wrappers();
				
				$uri = $request->uri();
				foreach($this->wrappers as $wrapper_config)
				{
					if(strpos($uri, $wrapper_config['prefix']) === 0)
					{
						$wrappers_data[] = $wrapper_config;
					}
				}
				
				usort($wrappers_data, function($a, $b)
				{
					if ($a['weight'] == $b['weight'])
					{
						return 0;
					}
					else if ($a['weight'] > $b['weight'])
					{
						return -1;
					}
					else 
					{
						return 1;
					}
				});			
				
				foreach($wrappers_data as $wrapper_data)
				{
					$wrappers[] = $this->create_wrapper($request, $wrapper_data['wrapper'], $wrapper_data['config']);
				}
				
				return array(
					"controller" => $controller,
					"wrappers" => $wrappers
				);
				
				
			}
		}
		
		return null;
	}
	
	public function register_controller($alias, $controller, $config = array())
	{
		if(isset($this->controller_aliases[$controller]))
		{
			$config += $this->controller_configs[$controller];
			$controller = $this->controller_aliases[$controller];			
		}
		
		$this->controller_aliases[$alias] = $controller;
		
		$this->controller_configs[$alias] = $config;
				
	}
	
	public function register_wrapper($alias, $wrapper, $config = array())
	{
		if(isset($this->wrapper_aliases[$wrapper]))
		{
			$config += $this->wrapper_configs[$wrapper];
			$wrapper = $this->wrapper_aliases[$wrapper];			
		}
		
		$wrapper = str_replace("/", "\\", $wrapper);
		
		$this->wrapper_aliases[$alias] = $wrapper;
		
		$this->wrapper_configs[$alias] = $config;
				
	}
	
	/**
	 * 
	 * @param \Door\Core\Request $request
	 * @param type $wrapper
	 * @param type $config
	 * @return Wrapper
	 */
	protected function create_wrapper(Request $request, $wrapper, $config = array())
	{
		if(isset($this->wrapper_aliases[$wrapper]))
		{
			$config = $config + $this->wrapper_configs[$wrapper];
			$wrapper = $this->wrapper_aliases[$wrapper];			
		}
		
		return new $wrapper($this->app, $request, $config);			
	}
	
	public function wrap($prefix, $wrapper, $config = array(), $weight = 0)
	{
		$this->wrappers[] = array(
			'prefix' => $prefix,
			'wrapper' => $wrapper,
			'config' => $config,
			'weight' => $weight
		);
	}
	
	
	
}
