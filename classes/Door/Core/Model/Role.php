<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Model;
/**
 * Description of Role
 *
 * @author serginho
 */
class Role extends \Door\Core\Model{
	
	protected function initialize()
	{	
		parent::initialize();
		
		$this->_fields += array(
			'name' => array(
				'type' => 'string'
			),	
			'description' => array(
				'type' => 'string'
			),	
		);
		
		$this->_relations += array(
			'users' => array(
				'model' => 'User',
				'type' => 'many_to_many',
				'store' => false,
				'foreignKey' => 'roles'
			),

		);				
	}	
	
}
