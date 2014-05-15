<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Controller;
/**
 * Description of Redirect
 *
 * @author serginho
 */
class Redirect extends \Door\Core\Controller {
	
	protected $redirect_uri = "";
	
	public function execute() {
		
		if($this->request->uri() != $this->redirect_uri)
		{
			$this->redirect($this->redirect_uri);
		}
		
	}
	
}
