<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Library;
use \Door\Core\Route;
use \Door\Core\Request;

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
	 * @param   array   $regex          regex patterns for route keys
	 * @return  Route
	 */	
	public function add($name, $uri, $controller_class, $regex){
		
		return $this->routes[$name] = new Route($uri, $controller_class, $regex);
		
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
	
	public function get_controller(Request $request)
	{
		/*@var Route $route */
		foreach($this->routes as $route)
		{
			$params = $route->matches($request);
			if($params !== false)			
			{
				$controller_class = $route->controller();
				return new $controller_class($request, $params);
			}
		}
		
		return null;
	}
	
	
	
}
