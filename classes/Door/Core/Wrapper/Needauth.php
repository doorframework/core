<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Wrapper;
/**
 * Description of Needauth
 *
 * @author serginho
 */
class Needauth extends \Door\Core\Wrapper {
	
	protected $login_role = 'login';
	
	protected $redirect_uri = "profile/login";
	
	public function before() {
		
		if( ! $this->app->auth->logged_in($this->login_role))
		{
			$this->redirect($this->redirect_uri);
		}
		
		parent::before();
	}
	
}
