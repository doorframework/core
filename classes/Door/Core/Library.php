<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core;
use Exception;

/**
 * Base class for libraries
 *
 * @author serginho
 */
abstract class Library {
	
	/**
	 *
	 * @var \Door\Core\Application
	 */
	protected $app;
	
	public function __construct(Application $app) {
		
		$this->app = $app;
		
	}
	
	/**
	 * Override this function for your library
	 */
	public function init()
	{
		
	}
	
}
