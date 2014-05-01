
<?php

namespace Door\Core;
/**
 * Description of Router
 *
 * @author serginho
 */
class Router {
	
	protected $routes = array();
	
	public function add_simple()
	{
		
	}
	
	public function add(Route $route, $context = 'main')
	{
		$this->routes[] = array(
			'route' => $route,
			'context' => $context
		);
	}
	
	public function get_controllers(Request $request)
	{
		$contexts = array();

		$controllers = array();
		
		foreach($this->routes as $route_config)
		{
			$context_name = $route_config['context'];
			
			/*@var $route Route*/
			$route = $route_config['route'];			
			
			$new_controllers = $route->get_controllers($request);
			if(count($new_controllers) > 0)
			{
				if(!isset($contexts[$context_name]))
				{
					$contexts[$context_name] = new Context();
				}
				
				$controllers += $new_controllers;
			}
		}
		
		return $controllers;
	}
	
}
