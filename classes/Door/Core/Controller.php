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
	
	/**
	 * @var Layout
	 */
	protected $layout = null;
	
	/**
	 * @var string
	 */
	protected $layout_name = null;
	
	public function __construct(Door $app, Request $request, array $params = array())
	{
		$this->request = $request;
		$this->params = $params;
		$this->app = $app;
		$this->response = $request->response();
		
		if($this->layout_name !== false)
		{
			//$this->layout = $app->lay
		}
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