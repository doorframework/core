<?php

namespace Door\Core;
use Door\Core\Database\Relation;
use Door\Core\Helper\Arr;
use Door\Core\Database\Type;
use MongoCollection;
use MongoDB;
use MongoDate;
use MongoId;
use Exception;

/**
 * Description of Model
 * @param string $_id
 * @author serginho
 */
abstract class Model{
	
	/**
	 *
	 * @var Door
	 */
	protected $_app = null;
	
	/**
	 * instance of database
	 * @var MongoDB
	 */
	protected $_db = null;
	
	/**
	 * Collection Name
	 * @var string
	 */
	protected $_collection = null;
	
	protected $_fields = array();
	
	protected $_relations = array();
	
	protected $_object = array();
	
	protected $_model_name = "";
	
	private static $_init_cache = array();
	
	protected $_changed = array();
	
	protected $_criteria = array();
	
	protected $_created_column = NULL;
	
	protected $_updated_column = null;
	
	protected $_rules = array();
	
	/**
	 *
	 * @var Validation
	 */
	protected $_validation = null;	
	
	/**
	 * override this function to add your fields and relations
	 */
	protected function initialize()
	{
		if(! isset($this->_fields['_id'])){
			$this->_fields['_id'] = array(
				'type' => Database\Type::MONGOID
			);
		}
		
		foreach($this->_fields as $key => $value) 
		{
			if(is_string($value))
			{
				$this->_fields[$key] = array(
					'type' => $value
				);
			}
		}
	}
	
	/**
	 * @param Application $app
	 * @param string $db
	 * @param string $model_name
	 * @param string $collection
	 * @param string $id
	 */
	final public function __construct(Application $app, $db, $model_name, $collection, $id = null)
	{
		$this->_collection = $collection;
		$this->_model_name = $model_name;
		
		$this->_db = $db;	
		$this->_app = $app;
		
		if( ! array_key_exists($this->_model_name, self::$_init_cache))
		{
			$this->initialize();
			
			self::$_init_cache[$this->_model_name] = array(
				"_fields" => $this->_fields,
				"_relations" => $this->_relations,
				"_rules" => $this->_rules
			);
		}
		else
		{
			foreach(self::$_init_cache[$this->_model_name] as $key => & $value)
			{
				$this->$key = $value;
			}
		}
		
		if($id !== null){
			
			if(is_array($id)){
				foreach($id as $key => $value){
					$this->where($key,'=',$value);
				}
				$this->find();
			} elseif($this->app()->is_id($id)) {
				$this->find($id);
			}
			
		}
		
		$this->after_construct();
	}
	
	protected function after_construct()
	{
		
	}
	
	
	public function __set($column, $value)
	{
		$this->set($column, $value);
	}
	
	public function set($column, $value)
	{
		if(array_key_exists($column, $this->_fields)){			
			
			$current_value = $this->get($column);
			$changed = ($value != $current_value);			
			
			switch(strtolower($this->_fields[$column]['type'])){
				case Type::DATE :
					if( !is_object($value)){
						$value = new MongoDate(intval($value));
					}					
					break;
				case Type::INTEGER :
					if($value !== null){
						$value = intval($value);
					}
					break;
				case Type::STRING :					
					if($value !== null){
						$value = (string)$value;
					}
					break;
				case Type::ARR :
					if( !is_array($value)){
						$value = null;
					}
					break;
				case Type::BOOLEAN :
					if(strtolower($value) == 'on'){
						$value = true;
					} elseif(strtolower($value) == 'off'){
						$value = false;
					} else {
						$value = (boolean)$value;
					}
					break;
				case Type::MONGOID :
					if(!is_object($value)){
						$value = new MongoId($value);
					}
					break;
			}		
			
			if($changed){
				$this->_object[$column] = $value;
				$this->_changed[$column] = $column;
			}
			
		} elseif(array_key_exists($column, $this->_relations)){
			
			$relation = $this->_relations[$column];
			switch($relation['type']){
				case Relation::MANY_TO_ONE:
					if( !($value instanceof Model)){
						
						$value = $this->app()->models->factory($relation['model'], $value);
						
						if( $value->loaded())
						{
							$this->{$relation['field']} = $value->pk();
						}											
					}					
					
					break;
				case Relation::MANY_TO_MANY:
					if( ! is_array($value))
					{
						$value = explode(",", trim($value," ,"));
					}
					$this->$column->from_array($value);										
					break;
				case Relation::ONE_TO_MANY:
				
				default:
					throw new Exception('can`t set relation :relation', array($relation['type']));
			}
			
		} else {
			throw new \Exception("column $column not found");
		}
		
		
	}
		
	public function mark_dirty($column)
	{
		if( ! isset($this->_fields[$column]))
		{
			throw new Exception("field $column not found");
		}
		
		$this->_changed[$column] = $column;		
	}
	
	public function __isset($column) {
		
		if(isset($this->_fields[$column])){
			
			return isset($this->_object[$column]);
			
		}
		
		
		
		
		return false;
		
	}
	
	public function __get($column)
	{
		return $this->get($column);
	}
	
	/**
	 * Handles getting of column
	 * Override this method to add custom get behavior
	 *
	 * @param   string $column Column name
	 * @throws Kohana_Exception
	 * @return mixed
	 */	
	public function get($column)
	{
		if($column == 'id')
		{
			$column = '_id';
		}
		
		if(array_key_exists($column, $this->_fields))
		{
			$return_value = null;
			
			if(array_key_exists($column, $this->_object))
			{
				$return_value = $this->_object[$column];
				
				switch(strtolower($this->_fields[$column]['type'])){															
					case "date":
						if($return_value instanceof MongoDate){
							$return_value = $return_value->sec;
						}						
						break;
					case "mongoid":
						if($return_value instanceof MongoId){
							$return_value = (string)$return_value;
						}			
						break;
				}
			}				
			
			//if field was not set, return null
			return $return_value;
		}
		
		if(isset($this->_relations[$column]))
		{
			$relation = $this->_relations[$column];
						
			
			if($relation['type'] == 'many_to_many'){
				
				return new Relation(
						$this, 
						$column);									
				
			} elseif($relation['type'] == 'one_to_many') { 
				
				$foreign_relations = $this->get_relations($relation['model']);			
				$foreign_relation = $foreign_relations[$relation['foreignKey']];
				
				$return_value = $this->app()->models->factory($relation['model']);
				$return_value->where($foreign_relation['field'],'=', $this->pk());
				return $return_value;
				
			} elseif($relation['type'] == 'many_to_one') {
				
				$id = $this->get($relation['field']);
				if( $id === null) return null;
				
				return $this->app()->models->factory($relation['model'])->find($id);
				
			}
		}
		
		throw new Exception("column $column not found in model $this->_model_name");
	}
	
	/**
	 * 
	 * @return MongoCollection
	 */
	public function get_collection()
	{
		return $this->db()->selectCollection($this->_collection);
	}
	
	public function get_model_name()
	{
		return $this->_model_name;
	}
	
	public function get_fields($model_name = null)
	{
		if($model_name === null){
			return $this->_fields;
		}
		
		if(! isset(self::$_init_cache[$model_name])){
			$this->app()->models->factory($model_name);
		}
		
		self::$_init_cache[$model_name]['_fields'];
	}
	
	public function get_relations($model_name = null)
	{
		if($model_name === null){
			return $this->_relations;
		}
		
		if(! isset(self::$_init_cache[$model_name])){
			$this->app()->models->factory($model_name);
		}
		
		return self::$_init_cache[$model_name]['_relations'];
		
	}
	
	public function get_collection_name()
	{
		return $this->_collection;
	}
	
	public function from_array(array $values)
	{
		$this->_object = $values;		
	}
	
	public function values(array $values, array $expected = null, $reset = false)
	{
		// Default to expecting everything except the primary key
		if ($expected === NULL)
		{
			$expected = array_keys($this->_fields);

			// Don't set the primary key by default
			unset($values["_id"]);
		}		
		
		if($reset){
			$this->_object = array();			
		}
		
		foreach($values as $key => $value)
		{
			if(isset($this->_fields[$key]) || isset($this->_relations[$key]))
			{
				$this->$key = $value;
			}
		}
		
		
		
		if($reset){
			$this->_changed = array();
		}				
	}
	
	public function pk()
	{
		return Arr::get($this->_object, '_id');
	}
	
	/**
	 * 
	 * @return MongoDB
	 */
	public function db()
	{
		return $this->app()->database->instance($this->_db);
	}
	
	/**
	 * 
	 * @param type $key
	 * @param type $value
	 * @param string $op
	 * @return \Door_Model
	 */
	public function where($key, $op, $value)
	{
		$op = strtolower($op);
		
		switch($op){		
			case "=":
				$this->_criteria[$key] = $value;
				break;
			case ">":
				$this->_criteria[$key]['$gt'] = $value;
				break;
			case ">=":
				$this->_criteria[$key]['$gte'] = $value;
				break;
			case "<":
				$this->_criteria[$key]['$lt'] = $value;
				break;
			case "<=":
				$this->_criteria[$key]['$lte'] = $value;
				break;
			case "like":
				$this->_criteria[$key] = new MongoRegex($value);
				break;
			case "in": 
				$this->_criteria[$key]['$in'] = $value;
				break;				
		
		}
		
		
		return $this;
	}
	
	/**
	 * 
	 * @param array $values
	 * @return \Door_Model
	 */
	public function or_where(array $values)
	{
		if(!isset($this->_criteria['$or'])){
			$this->_criteria['$or'] = array();
		}
		
		$this->_criteria['$or'] += $values;
		
		return $this;
	}
	
	/**
	 * 
	 * @return \Door\Core\Database\Cursor
	 */
	public function find_all()
	{
		$db_instance_name = $this->app()->database->get_name($this->db());
		$config = $this->app()->database->config($db_instance_name);
		
		return new \Door\Core\Database\Cursor(
				$this, 
				$this->app()->database->client($db_instance_name), 
				$config['database'].".".$this->_collection, 
				$this->_criteria, 
				array_keys($this->_fields));
	}
	
	/**
	 * 
	 * @param type $id
	 * @return \Door_Model
	 */
	public function find($id = null)
	{
		if($id !== null){
			if(is_array($id)){
				
				$this->_criteria = $id;
				
			} else {
				
				$this->_criteria = array(
					'_id' => $id
				);				
			}
		}
		//var_dump($this->_fields['_id']);
		if(isset($this->_criteria['_id']) && strtolower($this->_fields['_id']['type']) == 'mongoid'){
			$this->_criteria['_id'] = new MongoId($this->_criteria['_id']);
		}
		
		$data = $this->db()->selectCollection($this->_collection)
				->findOne(
						$this->_criteria,
						array_keys($this->_fields));

		if(is_array($data)){
			
			$this->_object = $data;
			$this->_criteria = array();
			
		}
		
		return $this;
		
		
	}
	
	public function loaded()
	{
		return isset($this->_object['_id']);
	}
	
	public function as_array()
	{
		$return_value = array();
		foreach(array_keys($this->_fields) as $field){
			$return_value[$field] = $this->$field;
		}
		return $return_value;		
	}
	
	public function save()
	{		
		if(isset($this->_id))
		{
			$this->update();
		}
		else
		{
			$this->create();
		}

	}
	
	public function update()
	{
		if( ! $this->check())
		{
			throw new Exception("Validation failed");
		}
		
		$this->app()->models->run_event('beforupdate', $this);
		
		if(isset($this->_updated_column)){
			$this->set($this->_updated_column, time());
		}					
		
		$this->get_collection()->update(
			array("_id" => $this->_object['_id']),
			$this->_object,
			array("upsert" => true)				
		);		
		
		$this->app()->models->run_event('update', $this);
	}
	
	public function create()
	{
		if( ! $this->check()){
			throw new Exception("Validation failed");
		}
		
		$this->app()->models->run_event('beforecreate', $this);
		
		if(isset($this->_created_column)){
			$this->set($this->_created_column, time());
		}				
		
		if(isset($this->_updated_column)){
			$this->set($this->_updated_column, time());
		}		
		
		$this->_object['_id'] = new MongoId;
		
		
		$this->get_collection()->insert($this->_object);		
		
		$this->app()->models->run_event('create', $this);
		
	}
	
	public function has($alias, $far_key)
	{
		return $this->$alias->has($far_key);
	}
	
	/**
	 * 
	 * @return \Door\Core\Validation
	 */
	public function validation()
	{
		if ( ! isset($this->_validation))
		{
			// Initialize the validation object
			$this->_validation();
		}

		return $this->_validation;
	}	
	
	/**
	 * Validates the current model's data
	 *
	 * @param  Validation $extra_validation Validation object
	 * @return bool
	 */
	public function check(Validation $extra_validation = NULL)
	{
		// Determine if any external validation failed
		$extra_errors = ($extra_validation AND ! $extra_validation->check());

		// Always build a new validation object
		$this->_validation();

		$array = $this->_validation;

		if($array->check() === FALSE OR $extra_errors)
		{
			return false;
		}

		return true;
	}	
	
	/**
	 * Initializes validation rules, and labels
	 *
	 * @return void
	 */
	protected function _validation()
	{
		// Build the validation object with its rules
		$this->_validation = Validation::factory($this->_object)
			->bind(':model', $this)
			->bind(':changed', $this->_changed);

		foreach ($this->rules() as $field => $rules)
		{
			$this->_validation->rules($field, $rules);
		}

		// Use column names by default for labels
		$columns = array_keys($this->_fields);

		// Merge user-defined labels
		$labels = array_merge(array_combine($columns, $columns), $this->labels());

		foreach ($labels as $field => $label)
		{
			$this->_validation->label($field, $label);
		}
	}	
	
	public function delete()
	{	
		if( ! $this->loaded())
		{
			throw new Exception("can`t delete not loaded model");
		}
				
		$this->app()->models->run_event('beforedelete', $this);
		
		$this->get_collection()->remove(array("_id" => $this->pk()));						
			
		$this->app()->models->run_event('delete', $this);
		
		$this->reset();
	}
	
	public function reset()
	{
		$this->_object = array();
		$this->_validation = null;
		$this->_changed = array();
	}
	
	/**
	 * 
	 * @param string $field
	 * @param string $value
	 * @return bool
	 */
	public function unique($field, $value)
	{
		$criteria = array(
			$field => $value,
			'_id' => array(
				'$ne' => $this->pk()
			)				
		);
		
		return $this->get_collection()->count($criteria) == 0;
	}
	
	public function rules()
	{
		return $this->_rules;
	}
	
	public function fields()
	{
		return $this->_fields;
	}	
	
	public function labels()
	{
		$return_value = array();
		foreach(array_keys($this->_fields) as $field){
			$return_value[$field] = $this->label($field);
		}		
		return $return_value;
	}
	
	public function label($field)
	{	
		$key1 = "{$this->_model_name}.{$field}";

		$val = $this->app()->lang->get($key1);
		
		if($val == $key1){
			$val = $this->app()->lang->get($field);
		}

		return $val;
	}
	
	/**
	 * 
	 * @return Application
	 */
	public function app()
	{
		return $this->_app;
	}
	
	
	
}
