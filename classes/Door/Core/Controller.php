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
	
	private $params = array();
	
	public function __construct(Request $request, array $params = array())
	{
		$this->request = $request;
		$this->params = $params;
	}
	
	/**
	 * get request parameter
	 * @return mixed
	 */
	protected function param($name)
	{
		return Arr::get($this->params, $name);
	}
	
	public abstract function execute();	
}