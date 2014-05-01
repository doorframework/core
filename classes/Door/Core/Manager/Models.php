<?php

namespace Door\Core\Manager;
/**
 * Description of Models
 *
 * @author serginho
 */
class Models {
	
	protected $models = array();
	
	public function add($model, $class)
	{
		if(isset($this->models[$model]))
		{
			throw new Exception("model $model already registered");
		}
		
		$this->models[$model] = $class;
	}
	
	public function add_array($models)
	{
		foreach($models as $key => $value)
		{
			$this->add($key, $value);
		}
	}
	
	public function __isset($name) {
		return isset($this->models[$name]);
	}
	
	public function __get($name)
	{
		return $this->models[$name];
	}
	
}
