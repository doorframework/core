<?php

namespace Door\Core\Route;
use Door\Core\Route;
use Door\Core\Request;

/**
 * Exact route. Passes only if uri is identical
 *
 * @author serginho
 */
class Exact extends Route {
	
	protected $uri = null;
	
	public function __construct($uri, $controller, $action) {
		
		$this->uri = $uri;
		parent::__construct($controller, $action);
		
	}
	
	public function check(Request $request) {
		
		return $request->uri() == $this->uri;
		
	}
	
}
