<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Controller;
use Door\Core\Controller;
/**
 * Description of MultiAction
 *
 * @author serginho
 */
class MultiAction extends Controller {
	
	public function execute()
	{
		$action = $this->param('action');	
		
		if($action == null || strlen($action) == 0)
		{
			$action = 'index';
		}
		
		$method = "action_".$action;
		if(method_exists($this, $method))
		{
			$this->$method();
		}
		else
		{
			$this->response->status(404);
		}
	}
	
}
