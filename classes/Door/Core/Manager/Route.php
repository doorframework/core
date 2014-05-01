<?php

namespace Door\Core;

/**
 * Description of Route
 *
 * @author serginho
 */
class Route {
	
	protected $name = null;
	
	protected $match = null;
	
	protected $controllers = null;
	
	
	
	/**
	 * @var array
	 */
	public $child_routes = array();
	
	
	public function __construct($path, $controller_class = null, $params = array(), $child_routes = array())
	{
		$this->path = $path;
		$this->controller_class = $controller_class;
		$this->params = $params;		
		$this->child_routes = $child_routes;
	}
	
}
