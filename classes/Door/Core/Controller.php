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
	 * @var array
	 */
	private $params = array();
	
	final public function __construct(Application $app, Request $request, array $config = array())
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
	protected function param($name, $default = null)
	{
		return $this->request->param($name, $default);
	}
	
	protected function view($name, $data = null)
	{
		return $this->app->views->get($name, $data);
	}
	
	public function execute()
	{
		$action = $this->param('action','index');	
		
		$method = "action_".$action;
		if(method_exists($this, $method))
		{
			$this->$method();
		}
		elseif($this->response->content_length() == 0)
		{
			$this->response->status(404);
		}
	}
	
	protected function redirect($uri)
	{
		$this->response->headers("Location", $this->app->url->site($uri));
	}
}