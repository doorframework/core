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
	
	protected $model_name = null;
	
	protected $model_param = 'model';
	
	protected $model_id_param = "id";
	
	public function execute()
	{
		$model_name = $this->param($this->model_param);
		
		if($model_name == null)
		{
			$model_name = $this->model_name;
		}
		
		if($model_name == null)
		{
			throw new Exception('model name not specified');
		}
		
		$model_id = $this->param($this->model_id_param);
		
		if( ! $this->app->is_id($model_id))
		{
			throw new Exception('model id not specified');
		}
		
		$model = $this->app->models->factory($model, $model_id);
		
		if( ! $model->loaded())
		{
			throw new Exception("model not found");
		}
		
		$model->delete();
	}
	
}
