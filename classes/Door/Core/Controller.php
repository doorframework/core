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
	 * @var Door
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
	
	final public function __construct(Application $app, Request $request, array $params = array())
	{
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
}