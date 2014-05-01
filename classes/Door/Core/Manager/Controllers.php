<?php

namespace Door\Core\Manager;
/**
 * Description of Controllers
 *
 * @author serginho
 */
class Controllers {
	
	protected $controllers = array();
	
	public function add($name, $class, $params)		
	{
		
	}
			
	
	public function register($name, $class, $params = array())
	{
		if(isset($this->controllers[$name]))
		{
			throw new Exception("controller $name already registered");									
		}				
		
		$this->controllers[$name] = array(
			'class' => $class,
			'params' => $params
		);
	}	
	
	public function __isset($name) {
		
		return isset($this->controllers[$name]);
		
	}
	
	public function __get($name)
	{
		return $this->controllers[$name];
	}
	
	
	
}
