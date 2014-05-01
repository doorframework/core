<?php

namespace Door\Core;

/**
 * Description of Context
 *
 * @author serginho
 */
class Context {
	
	protected $data = array();
	
	public function __set($name, $value) {
		
		if(isset($value))
		{
			$this->data[$name] = $value;
		}			
		
	}
	
	public function __get($name) {

		if(array_key_exists($name, $this->data))
		{
			return $this->data[$name];
		}
		
		return null;
		
	}	
	
	public function __unset($name) {
		
		if(array_key_exists($name, $this->data))
		{
			unset($this->data[$name]);
		}
		
	}
	
	public function __isset($name) {
		
		return isset($this->data[$name]);
		
	}
	
}
