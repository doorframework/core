<?php

namespace Door\Core;
use Door\Request\Exception;

/**
 * Description of Request
 *
 * @author serginho
 */
abstract class Request {
	
	protected $uri;
	
	/**
	 * @var Router
	 */
	protected $router;
	
	/**
	 * @var  array   parameters from the route
	 */
	protected $params = array();	
	
	protected $path = array();
	
	public function __construct($uri, Router $router = null) {
		
		//remove first slash
		if(substr($uri, 0, 1) == "/")
		{
			$uri = substr($uri, 1);
		}
		
		$this->uri = $uri;
		if(strlen($uri) > 0)
		{
			$this->path = explode("/", $uri);
		}
		
		$this->router = ($router === null) ? Door::$router : $router;
		
	}
	
	public function param($name, $value = null)
	{
		if($value === null)
		{
			if(isset($this->params[$name]))
			{
				return $this->params[$name];
			}
		}
		else
		{
			$this->params[$name] = $value;
		}
		
		return null;
	}
	
	public function path($index = null)
	{
		if($index === null)
		{
			return $this->path;
		}
		
		if(array_key_exists($index, $this->path))
		{
			return $this->path[$index];
		}
		
		return null;
	}
	
	public function path_length()
	{
		return count($this->path);
	}
	
	public function uri()
	{
		return $this->uri;
	}
	
	public function execute()
	{
		
		
		return $this;
	}
	
	
	
}
