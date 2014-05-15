<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Model\User;
/**
 * Description of Token
 *
 * @author serginho
 */
class Token extends \Door\Core\Model {
	
	protected $_created_column = "created";
	
	public function initialize() {
		
		parent::initialize();
		
		$this->_fields += array(
			'user_id' => array(
				'type' => 'string'
			),
			'token' => array(
				'type' => 'string'
			),
			'created' => array(
				'type' => 'date'
			),
			'expires' => array(
				'type' => 'date'
			),		
			'user_agent' => array(
				'type' => 'string'
			),		
		);
		
		$this->_relations += array(
			'user' => array(
				'model' => 'User',
				'type' => 'many_to_one',
				'field' => 'user_id',
				'foreignKey' => 'tokens'
			)
		);			
		
	}	
	
	
}
