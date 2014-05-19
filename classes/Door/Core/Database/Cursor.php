<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Door\Core\Database;
use Door\Core\Model;

/**
 * Description of Cursor
 * @package Door/Core
 * @author serginho
 */
class Cursor extends \MongoCursor {
	
	/**
	 *
	 * @var Model
	 */
	protected $model = null;
	
	protected $model_name = null;
	
	public function __construct(Model $model, \MongoClient $connection, $ns, array $query = array(), array $fields = array()) {
		
		$this->model_name = $model->get_model_name();
		$this->model = $model;
		parent::__construct($connection, $ns, $query, $fields);
	}
	
	/**
	 * @return Model
	 */
	public function current()
	{
		return $this->load_model(parent::current());
	}
	
	/**
	 * @return Model
	 */
	public function getNext() {
		return $this->load_model(parent::getNext());
	}	
	
	/**
	 * 
	 * @param type $data
	 * @return Model
	 */
	protected function load_model($data)
	{
		$model = $this->model->app()->models->factory($this->model_name);
		$model->from_array($data);
		return $model;
	}
	
	public function as_array($key = null, $value = null)
	{
		$return_value = array();
		
		while($data = parent::getNext()){
			
			if($key === null && $value === null){
				
				$return_value[] = $this->load_model($data);
				
			} elseif($key === null) {
				
				if(array_key_exists($value, $data)){
					$return_value[] = $data[$value];
				}
				
			} elseif($value === null){
				
				if(array_key_exists($key, $data)){
					$return_value[$data[$key]] = $this->load_model($data);
				}				
				
			}
			
		}
		
		return $return_value;
	}
	
}
