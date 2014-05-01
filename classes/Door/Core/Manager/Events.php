<?php

namespace Door\Core\Manager;

/**
 * Description of Events
 *
 * @author serginho
 */
class Events {
	
	protected $listeners = array();
	
	public function listen($event, $callback)
	{
		$this->listeners[$event] = $callback;
	}
	
	public function run($event)
	{
		$args = func_get_args();
		array_shift($args);
		foreach($this->listeners[$event] as $callback)
		{			
			call_user_func_array($callback, $args);
		}
		call_user_func_array($callback, $args);
	}
	
}
