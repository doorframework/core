<?php

namespace Door\Core;
use Door\Core\Helper\Arr;

/**
 * Description of Controller
 *
 * @author serginho
 */
abstract class Wrapper {
		
	/**
	 * @var Request
	 */
	protected $request;		
	
	/**
	 *
	 * @var Application
	 */
	protected $app;
	
	/**
	 *
	 * @var Response
	 */
	protected $response;
	
	/**
	 *
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * default params for controller
	 * @var array
	 */
	protected $defaults = array();
	
	final public function __construct(Application $app, Request $request, Controller $controller, array $config = array())
	{
		
		foreach($config as $key => $value)
		{
			if(property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
		
		$this->request = $request;
		$this->app = $app;
		$this->response = $request->response();
		$this->controller = $controller;
		$this->init();
	}
	
	/**
	 * Override to add your initialization
	 */
	protected function init()
	{
		
	}
	
	public function before()
	{
		
	}
	
	public function after()
	{
		
	}
	
	/**
	 * get request parameter
	 * @return mixed
	 */
	protected function param($name)
	{
		return Arr::get($this->params, $name);
	}
	
	protected function view($name, $data = null)
	{
		return $this->app->views->get($name, $data);
	}
	
	protected function redirect($uri)
	{
		$this->response->headers("Location", $this->app->url->site($uri));
	}
}