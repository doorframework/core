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
	 * @var \Door\Core\Door
	 */
	protected $app;
	
	public function __construct(Door $app) {
		
		$this->app = $app;
		
	}
	
	/**
	 * Override this function for your library
	 */
	public function init()
	{
		
	}
	
}
