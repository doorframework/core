<?php

namespace Door\Core\Route;
use Door\Core\Route;
use Door\Core\Request;

/**
 * Route by pattern.
 *
 * @author serginho
 */
class Pattern extends Route {
	
	protected $uri = null;
	
	/**
	 * @param string $pattern path pattern, like:
	 * /user/*
	 * 
	 * @param string $controller controller name
	 */
	public function __construct($pattern, $controller, $action) {
		
		$this->pattern = $pattern;
		parent::__construct($controller);
		
	}
	
	public function check(Request $request) {
		
		return $request->uri() == $this->uri;
		
	}
	
}
