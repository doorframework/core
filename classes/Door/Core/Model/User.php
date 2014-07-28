<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Model;
use Door\Core\Valid;
use \Exception;

/**
 * Description of User
 *
 * @author serginho
 * @param $name
 * @param $username
 * @param $password
 * @param $email
 * @param $created
 * @param $updated
 * @param $last_login
 * @param $logins
 * @param $roles_ids
 * @param \Door\Core\Database\Relation $roles
 * @param \Door\Core\Database\Relation $tokens
 */
class User extends \Door\Core\Model{
	
	protected function initialize()
	{
		parent::initialize();
		
		$this->_fields += array(
			'name' => array(
				'type' => 'string'
			),
			'username' => array(
				'type' => 'string'
			),
			'password' => array(
				'type' => 'string'
			),
			'email' => array(
				'type' => 'email'
			),		
			'created' => array(
				'type' => 'date'
			),		
			'updated' => array(
				'type' => 'date'
			),		
			'last_login' => array(
				'type' => 'date'
			),
			'logins' => array(
				'type' => 'integer'
			),
			'roles_ids' => array(
				'type' => 'array'
			)			
		);
		
		$this->_relations += array(
			'roles' => array(
				'model' => 'Role',
				'type' => 'many_to_many',
				'field' => 'roles_ids',
				'foreignKey' => 'users'
			),
			'tokens' => array(
				'model' => 'User_Token',
				'type' => 'one_to_many',
				'foreignKey' => 'users'
			),
		);		
		
		/*$this->_rules += array(
			'username' => array(
				array('not_empty'),
				array('max_length', array(':value', 32)),
				array(array(":model", 'unique'), array('username', ':value')),
			),
			'email' => array(
				array('not_empty'),
				array('email'),
				array(array(':model', 'unique'), array('email', ':value')),
			),
		);*/		
	}
	
	protected $_created_column = 'created';
	
	protected $_updated_column = 'updated';
	
	public function unique_key($value)
	{
		return Valid::email($value) ? 'email' : 'username';
	}

	/**
	 * Tests if a unique key value exists in the database.
	 *
	 * @param   mixed    the value to test
	 * @param   string   field name
	 * @return  boolean
	 */
	public function unique_key_exists($value, $field = NULL)
	{		
		if ($field === NULL)
		{
			// Automatically determine field by looking at the value
			$field = $this->unique_key($value);
		}

		return $this->get_collection()->count(array($field => $value)) > 0;
	}	
	
	public function set($column, $value) {
		
		if($column == 'password'){
			
			$value = $this->app()->auth->hash($value);
		}
		
		parent::set($column, $value);
		
	}
	
	/**
	 * Complete the login for a user by incrementing the logins and saving login timestamp
	 *
	 * @return void
	 */
	public function complete_login()
	{
		if ($this->loaded())
		{
			// Update the number of logins
			$this->logins = $this->logins + 1;

			// Set the last login date
			$this->last_login = time();
			// Save the user
			$this->save();
		}
	}		
	
	public function has_role($role_name)
	{
		$role = $this->app()->models->factory('Role', array('name' => $role_name));
		
		if( ! $role->loaded())
		{
			throw new Exception("role {$role_name} not found");
		}
		
		return $this->has('roles', $role);
	}
	
}
