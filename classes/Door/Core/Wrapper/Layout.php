<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Wrapper;
use Door\Core\Wrapper;

/**
 * Description of Layout
 *
 * @author serginho
 */
class Layout extends Wrapper{
	
	protected $layout_key = 'layout';
	
	protected $layout_filename = 'core/layout';
	
	
	public function before() {
		
		parent::before();
		
	}
	
	public function after()
	{
		
	}
	
}
