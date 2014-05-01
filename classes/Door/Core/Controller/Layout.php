<?php

namespace Door\Core\Controller;
use Door\Core\Controller;

/**
 * Layout controller. Can be only one layout controller for request.
 *
 * @author serginho
 */
abstract class Layout extends Controller{
	
	abstract public function render();
	
}
