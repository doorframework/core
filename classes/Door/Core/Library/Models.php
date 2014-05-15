<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Library;
use \Door\Core\Model as Model;
use \Exception;

/**
 * Description of Models
 *
 * @author serginho
 */
class Models extends \Door\Core\Library {
	
	protected $registered_models = array();
	
	protected $models = array();
	
	protected $collections = array();
	
	public function add($model_name, $class_name, $collection, $db = 'default')
	{
		$this->registered_models[$model_name] = array(
			'class_name' => str_replace("/", "\\", $class_name),
			'collection' => $collection,
			'db' => $db
		);
		
		$this->models[$model_name] = str_replace("/", "\\", $class_name);
		$this->collections[$model_name] = $collection;
	}
	
	/**
	 * @param string $model_name
	 * @param string $id
	 * @return Model
	 * @throws Exception
	 */
	public function factory($model_name, $id = null)
	{
		if( ! $this->model_registered($model_name))
		{
			throw new Exception("model {$model_name} not found");
		}
		
		$data = $this->registered_models[$model_name];
		$class_name = $data['class_name'];	
				
		return new $class_name($this->app, $data['db'], $model_name, $data['collection'], $id);	 		
	}
	
	public function model_registered($model_name)
	{
		return isset($this->registered_models[$model_name]);
	}
	
	public function models()
	{
		return $this->models;
	}
			
	
	
}
