<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Library;

/**
 * Description of Layouts
 *
 * @author serginho
 */
class Layouts extends \Door\Core\Library{
		
	protected $layouts = array();
	protected $configs = array();
	
	protected $layout_instances = array();
	
	public function add($name, $class_name = null, $config = array())
	{
		if( $class_name === null)
		{
			$class_name = "/Door/Core/Layout";
		}
		
		$this->layouts[$name] = str_replace("/", "\\", $class_name);
		
		if(is_array($config) && !empty($config))
		{
			$this->configs[$name] = $config;
		}
		else
		{
			$this->configs[$name] = array();
		}
		
		
	}
	
	public function get($name = 'default')
	{
		if( ! isset($this->layouts[$name]))
		{
			$name = htmlspecialchars($name);
			throw new Exception("Layout '{$name}' not found");
		}
		
		$class = $this->layouts[$name];		
		return new $class($this->configs[$name]);
	}
	
	
}
