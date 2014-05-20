<?php

namespace Door\Core;
use Door\Core\Helper\Arr;
use Door\Core\Worker;


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
	
	protected $data = array();
	
	protected $params = array();
	
	/**
	 *
	 * @var Response;
	 */
	protected $response = null;
	
	public function __construct($uri, Application $app) {
		
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
		$workers = $this->app->router->get_workers($this);
		$controller = Arr::get($workers, 'controller');
		$wrappers = Arr::get($workers, 'wrappers', array());
		
		if($controller != null)
		{
			foreach($wrappers as $wrapper)
			{				
				$wrapper->before();
				
				if($this->has_stopped())
				{
					break;
				}
			}			
			
			if( ! $this->has_stopped())
			{
				$controller->execute();													
			}
			
			if( ! $this->has_stopped())
			{
				foreach($wrappers as $wrapper)
				{				
					$wrapper->after();

					if($this->has_stopped())
					{
						break;
					}
				}	
			}		
			
			if($this->has_stopped())
			{
				if(strlen($this->response->content_length()) == 0)
				{
					$this->response->body('error occured');
				}
			}								
		}
		else
		{			
			$this->response->status(404);
			$this->response->body("resource not found");
		}
		
		return $this;
	}
	
	/**
	 * @return Response
	 */
	public function response()
	{
		return $this->response;
	}
	
	public function data($name = null, $value = null)
	{
		if($value === null && $name === null)
		{
			return $this->data;
		}
		if($value === null && $name !== null)
		{
			return Arr::get($this->data, $name);
		}
		
		$this->data[$name] = $value;
	}
	
	public function set_params(array $params)
	{
		$this->params = $params;
	}
	
	public function param($param)
	{
		return Arr::get($this->params, $param);
	}
	
	public function has_stopped()
	{
		return intval($this->response->status()) != 200
			|| $this->response->headers('Location') != null;
	}
	
	
	
}
