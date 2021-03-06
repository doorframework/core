<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Controller;
use Door\Core\Controller;
/**
 * Description of Delete
 *
 * @author serginho
 */
class Delete extends Controller {
	
	protected $model = null;	
	
	protected $model_id_param = "id";
	
	protected $return_uri = null;
	
	public function execute()
	{
		
		$model_id = $this->param($this->model_id_param);
		
		if( ! $this->app->is_id($model_id))
		{
			throw new Exception('model id not specified');
		}
		
		$model = $this->app->models->factory($this->model, $model_id);
		
		if( ! $model->loaded())
		{
			throw new Exception("model not found");
		}
		
		$model->delete();
		
		if($this->return_uri !== null){
			$this->redirect($this->return_uri);
		} elseif(isset($_SERVER['HTTP_REFERER'])) {
			$this->redirect($_SERVER['HTTP_REFERER']);
		} else {
			$this->redirect("");
		}
		
	}
	
}
