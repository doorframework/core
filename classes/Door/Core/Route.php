<?php

namespace Door\Core;


/**
 * Base class for routes
 *
 * @author serginho
 */
abstract class Route {
	
	/**
	 * Controller name. Must be overriden.
	 * @var type 
	 */
	protected $controller = null;	
	
	protected $action = null;
	
	public function __construct($controller, $action) {
		
		$this->controller = $controller;
		$this->action = $action;
		
	}
	
	
	/**
	 * @return bool true if route pass
	 * 
	 */
	abstract public function check(Request $request);
	
	/**
	 * @return string controller name
	 */
	public function get_controller()
	{
		return $this->controller;
	}
	
}
