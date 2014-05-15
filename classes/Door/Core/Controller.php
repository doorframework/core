<?php

namespace Door\Core;
use Door\Core\Helper\Arr;

/**
 * Description of Controller
 *
 * @author serginho
 */
abstract class Controller {
		
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
	 * default params for controller
	 * @var array
	 */
	protected $defaults = array();
	
	/**
	 * @var array
	 */
	private $params = array();
	
	final public function __construct(Application $app, Request $request, array $params = array(), array $config = array())
	{
		foreach($this->defaults as $key => $value)
		{
			if( !array_key_exists($key, $params))
			{
				$params[$key] = $value;
			}
		}
		
		foreach($config as $key => $value)
		{
			if(property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
		
		$this->request = $request;
		$this->params = $params;
		$this->app = $app;
		$this->response = $request->response();
		$this->init();
	}
	
	/**
	 * Override to add your initialization
	 */
	protected function init()
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
	
	public abstract function execute();	
	
	protected function redirect($uri)
	{
		$this->response->headers("Location", $this->app->url->site($uri));
	}
}