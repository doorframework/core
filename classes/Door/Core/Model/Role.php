<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

/**
 * Description of Role
 *
 * @author serginho
 */
class Role {
	
	protected function initialize()
	{
		$this->_fields += array(
			"_id" => array(
				"type" => "string"
			)
		);
		
		parent::initialize();
		
		$this->_fields += array(
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
