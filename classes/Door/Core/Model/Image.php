<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Model;

/**
 * Description of Image
 *
 * @author serginho
 */
class Image extends \Door\Core\Model {

	CONST FOLDER = "upload/images/";
	
	public function initialize() {		
		
		$this->_fields += array(
			'title' => array(
				'type' => 'string'
			),	
			'description' => array(
				'type' => 'string'
			),	
			'sizes' => array(
				'type' => 'array'
			)
		);
		parent::initialize();
	}
		
	
}
