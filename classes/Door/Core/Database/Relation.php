<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Door\Core\Database;
use \Door\Core\Model;
use \Exception;
use \Door\Core\Helper\Arr;
/**
 * Description of Relation
 * @package Door/Core
 * @author serginho
 */
class Relation {
	
	const ONE_TO_MANY = "one_to_many";
	const MANY_TO_MANY = "many_to_many";
	const MANY_TO_ONE = "many_to_one";
	
	const COLLECTION = "door_relations";
	
	protected $model_name1;
	protected $model_name2;
	protected $field1;
	protected $field2;
	protected $relation1;
	protected $relation2;
	
	/**
	 * @var Model
	 */
	protected $model1;
	
	/**
	 * @var Model
	 */
	protected $model2;
	
	/**
	 * 
	 * @param Model $model
	 * @param string $relation
	 */
	public function __construct(Model $model, $relation_name) {
		
		$this->model1 = $model;
		$this->field1 = $relation_name;		
		
		$this->relation1 = Arr::get($this->model1->get_relations(), $relation_name);
		
		if(isset($this->relation1['foreignKey'])){
			
			$this->field2 = $this->relation1['foreignKey'];
			$this->model2 = $this->model1->app()->models->factory($this->relation1['model']);
			$this->relation2 = Arr::get($this->model2->get_relations(), $this->field2);
			
		}
	}
	
	/**
	 * 
	 * @return Model
	 */
	public function find_all()
	{
		$return_value = $this->model1->app()->models->factory($this->relation1['model']);
		$return_value->where("_id",'in', $this->get_ids());
		return $return_value;
	}
	
	public function add(Model $model)			
	{
		
		if($model->get_model_name() !== $this->relation1['model']){
			
			throw new Exception("wrong model added");
			
		}
		
		if( ! $model->loaded()){
			throw new Exception("model not loaded");
		}		
						
		if(Arr::get($this->relation1, 'store') !== false){


			if(isset($this->relation1['field'])){

				$field = $this->relation1['field'];
				$array = $this->model1->$field;
				if( ! is_array($array))
				{
					$array = array();
				}
				if(array_search($model->pk(), $array) === false){
					$array[] = $model->pk();
					$this->model1->$field = $array;
				}				

			} else {

				$collection = $this->model1->db()->selectCollection(self::COLLECTION);
				
				$id = "{$this->model1->get_model_name()}:{$this->model1->pk()}:{$this->field1}";
				
				$collection->update(
						array("_id" => $id,
							'model' => $this->model1->get_model_name(),
							'model_id' => $this->model1->pk(),
							'field' => $this->field1), 
						array('$addToSet' => array('ids' => $model->_id)),
						array("upsert" => true)
					);

			}

		}
		
		if(isset($this->relation2) 
				&& Arr::get($this->relation2, 'store') !== false 
				&& $this->is_recursive_call() == false)
		{
			$model->{$this->field2}->add($this->model1);
		}
		
	
	}
	
	public function remove($model_id)
	{
		
		$model_id = null;
		if($model instanceof Model){
			$model_id = $model->id;
		} else {
			$model = $this->model1->app()->models->factory($this->relation1['model']);
			$model->find($model_id);
		}
		
		if($model->get_model_name() !== $this->relation1['model']){
			
			throw new Exception("wrong model added");
			
		}
		
		if( ! $model->loaded()){
			throw new Exception("model not loaded");
		}
		
		if(Arr::get($this->relation1, 'store') !== false){
			if(isset($this->relation1['field'])){

				$field = $this->relation1['field'];
				$array = $this->model1->$field;
				if( ! is_array($array))
				{
					$array = array();
				}
				
				$new_array = array();
				
				foreach($array as $item){
					if($item != $model_id){
						$new_array[] = $item;
					}
				}
				
				$this->model1->$field = $new_array;

			} else {

				$collection = $this->model1->db()->selectCollection(self::COLLECTION);
				
				$id = "{$this->model1->get_model_name()}:{$this->model1->pk()}:{$this->field1}";
				
				$collection->update(
						array("_id" => $id), 
						array('$pull' => array('ids' => $model->_id))
					);
			}
		}
		
		if(isset($this->relation2) 
				&& Arr::get($this->relation2, 'store') !== false 
				&& $this->is_recursive_call() == false)
		{
			$model->{$this->field2}->remove($this->model1, true);
		}		
	}
	
	/**
	 * @param mixed $model string|array|Model
	 * @return boolean
	 */
	public function has($model)
	{
		if( ! is_array($model)){
			$model_id = ($model instanceof Model) ? $model->pk() : $model;
		} else {
			$model_id = $model;
		}
						
		if(Arr::get($this->relation1, 'store') !== false){
			
			if(isset($this->relation1['field'])){

				$field = $this->relation1['field'];
				$array = $this->model1->$field;
				
				if( !is_array($array)){
					return false;
				}
				if(is_array($model_id)){
					return count(array_intersect($array, $model_id)) > 0;
				} else {
					return array_search($model_id, $array) !== false;
				}
				
				

			} else {

				$collection = $this->model1->db()->selectCollection(self::COLLECTION);
				
				$id = "{$this->model1->get_model_name()}:{$this->model1->pk()}:{$this->field1}";
				
				return $collection->count(array(
					'_id' => $id,
					"ids" => $model_id
				)) > 0;
			}						
		}
		
		
		if(isset($this->relation2) && Arr::get($this->relation2, 'store') !== false){
			
			if(isset($this->relation2['field'])){
																																						
				return $this->model2
					->get_collection()
					->count(array(
						"_id" => $model_id,
						$this->relation2['field'] => $this->model1->pk())) > 0;				
				
			} else {
				
				$collection = $this->model2->db()->selectCollection(self::COLLECTION);
				
				$id = "{$this->model2->get_model_name()}:{$this->model2->pk()}:{$this->field2}";
				
				return $collection->count(array(
					'_id' => $id,
					"ids" => $model_id
				)) > 0;														
				
			}						
		}		
		
		return false;
	}
	
	/**
	 * @return Model
	 */
	public function get_model()
	{
		$model_name = $this->relation2['model'];
		$model = new $model_name;
		$model->where('_id', 'in', $this->get_ids());
		return $model;
	}
	
	/**
	 * @return array
	 * @throws Kohana_Exception
	 */
	public function get_ids()
	{
		if(Arr::get($this->relation1, 'store') !== false){
			
			if(isset($this->relation1['field'])){
								
				$field = $this->relation1['field'];
				$return_value = $this->model1->$field;
				if( ! is_array($return_value)){
					$return_value = array();
				}				
				return $return_value;
				
			} else {
				
				$id = "{$this->model1->get_model_name()}:{$this->model1->pk()}:{$this->field1}";
				
				$ids = Arr::get(
						$this->model1
							->db()
							->selectCollection(self::COLLECTION)
							->findOne(array("_id" => $id)), 
						'ids',
						array()
					);
				
				if(!is_array($ids)){
					$ids = array();
				}
				
				return $ids;							
				
			}
			
		} elseif(isset($this->relation2) && Arr::get($this->relation2, 'store') !== false){
			
			if(isset($this->relation2['field'])){
								
				$ids = array();								
				$result = $this->model2
					->get_collection()
					->find(
							array($this->relation2['field'] => $this->model1->pk()), 
							array('_id'));
				
				foreach($result as $item)
				{
					if(isset($item['_id']))
					{
						$ids[] = $item['_id'];
					}
				}

				return $ids;
				
			} else {
				
				$ids = array();								
				$result = $this->model2
					->db()
					->selectCollection(self::COLLECTION)
					->find(array(
							"ids" => $id, 
							'model' => $this->relation2['model'], 
							'field' => $this->field2), 
							array('model_id'));
				
				foreach($result as $item)
				{
					if(isset($item['model_id']))
					{
						$ids[] = $item['model_id'];
					}
				}

				return $ids;						
				
			}												
		} else {
			
			throw new Kohana_Exception("can`t get ids for this relation");
		}
	}
	
	/**
	 * 
	 * @return integer
	 * @throws Kohana_Exception
	 */
	public function count()
	{
		if(Arr::get($this->relation1, 'store') !== false){
			
			if(isset($this->relation1['field'])){
								
				$field = $this->relation1['field'];
				$arr = $this->model1->$field;
				return count($arr);
				
			} else {
				
				$id = "{$this->model1->get_model_name()}:{$this->model1->pk()}:{$this->field1}";
				
				$self_collection = self::COLLECTION;
				
				$code = "function(){return db.{$self_collection}.find({'_id':'{$id}'}).limit(1)[0].ids.length}";
				
				$return_value = 0;
				
				try{
					
					$return_value = intval($this->model1->db()->execute($code));					
					
				} catch (Exception $ex) {}
				
				return $return_value;
			}
			
		} elseif(isset($this->relation2) && Arr::get($this->relation2, 'store') !== false){
			
			if(isset($this->relation2['field'])){
																																						
				return $this->model2
					->get_collection()
					->count(array($this->relation2['field'] => $this->model1->pk()));				
				
			} else {
				
				return $this->model2
					->db()
					->selectCollection(self::COLLECTION)
					->count(array(
							"ids" => $this->model1->pk(),
							'model' => $this->relation2['model'],
							'field' => $this->field2));				
			}						
		} else {
			throw new Kohana_Exception("can`t get ids for this relation");
		}		
	}
	
	public function count_all()
	{
		return $this->count();
	}
	
	protected function is_recursive_call()
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
		$return_value = false;
		
		if(count($trace) == 3)
		{
			$return_value = 
					$trace[0]['file'] == $trace[1]['file']
					&& $trace[0]['function'] == $trace[1]['function'];
		}
				
		return $return_value;
	}
	
}
