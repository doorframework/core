<?php

namespace Door\Core;
/**
 * Description of Controller
 *
 * @author serginho
 */
abstract class Controller {
	
	/**
	 *
	 * @var Request
	 */
	protected $request;
	
	/**
	 *
	 * @var Context
	 */
	protected $context;
	
	protected $path_index;
	
	protected $views = array();
	
	public function __construct(Request $request, array $config = array())
	{
	}
	
	final public function set_request(Request $request)
	{
		$this->request = $request;
	}
	
	final public function set_context(Context $context)
	{
		$this->context = $context;
	}
	
	final protected function get_view($name)
	{
		
	}
	
	abstract public function run();	
}
