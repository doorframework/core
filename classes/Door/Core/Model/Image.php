<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Model;
use \Door\Core\Helper\Arr;

/**
 * Description of Image
 *
 * @author serginho
 * @param string $mime
 * @param string $extension
 * @param string $title
 * @param string $description
 * @param array $presentations Array of presentations
 * array(
 *		'presentation_alias' => array(
 *			'extension' => 'jpg'
 *			'width' => 100
 *			'height' => 200
 *		),
 *		....
 * )
 * 
 * 
 */
class Image extends \Door\Core\Model {
	
	public function initialize() {		
		
		parent::initialize();
		
		$this->_fields += array(
			'extension' => array(
				'type' => 'string'
			),	
			'title' => array(
				'type' => 'string'
			),	
			'description' => array(
				'type' => 'string'
			),	
			'width' => array(
				'type' => 'integer'
			),	
			'height' => array(
				'type' => 'integer'
			),	
			'presentations' => array(
				'type' => 'array'
			)
		);		
	}
	
	public function get_presentation($name)
	{
		return Arr::get($this->presentations, $name);
	}
	
	public function add_presentation($name, $config)
	{
		if( ! isset($config['extension']))
		{
			throw new Exception("Extension not provided");
		}
		
		if( ! isset($config['width']) || intval($config['width']) <= 0)
		{
			throw new Exception("Bad width");
		}
		
		if( ! isset($config['height']) || intval($config['height']) <= 0)
		{
			throw new Exception("Bad height");
		}
		
		$config['width'] = intval($config['width']);
		$config['height'] = intval($config['height']);
		
		$this->_object['presentations'][$name] = $config;
		$this->mark_dirty('presentations');
		$this->save();
	}
	
	public function remove_presentation($name)
	{
		if(isset($this->presentations[$name]))
		{
			unset($this->_object['presentations'][$name]);
		}
		$this->mark_dirty('presentations');
		$this->save();
	}
	
		
	
}
