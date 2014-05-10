<?php

namespace Door\Core;
use Door\Request\Exception;

/**
 * Description of Request
 *
 * @author serginho
 */
class Request {
	
	protected $uri;
	
	/**
	 * @var Door
	 */
	protected $app;
	
	protected $path = array();	
	
	/**
	 *
	 * @var Response;
	 */
	protected $response = null;
	
	public function __construct($uri, Door $app) {
		
		$uri = trim($uri, "/");
		
		$this->uri = $uri;
		if(strlen($uri) > 0)
		{
			$this->path = explode("/", $uri);
		}
		
		$this->app = $app;
		
		$this->response = new Response();				
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
	
	/**
	 * @return Response
	 */
	public function response()
	{
		return $this->response;
	}
	
	
	
}
