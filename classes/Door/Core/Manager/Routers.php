<?php

namespace Door\Core\Manager;
use Door\Router;
/**
 * Description of Routes
 *
 * @author serginho
 */
class Routers {	
	
	protected $routers = array();
	
	public function add(Router $router, $context = 'default')
	{
		$this->routers[$context][] = $router;
	}
	
	public function get_contexts()
	{
		return array_keys($routers);
	}
	
	public function get_controllers($uri, $context = 'default')
	{
		$return_value = array();
		
		if(isset($this->routers[$context]))
		{
			$controllers = array();
			foreach($this->routers[$context] as $router)
			{
				$return_value += $router->get_controllers();
			}
			
			$return_value = $this->routers[$context];
		}
		
		return $return_value;
	}
	
	public function find($path)
	{
		$return_value = null;
		
		if(strlen($path) > 0)
		{
			$path_arr = explode("/", $path);
			$current_node = $this->root;
			foreach($path_arr as $path_item)
			{
				if(!isset($current_node->child_routes[$path_item]))
				{
					$current_node = null;
					break;
				}
				
				$current_node = $current_node->child_routes[$path_item];
				
			}
		}
		
		return $return_value;
	}
	
}
