<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Controller\Image;
use Door\Core\Controller;

/**
 * Description of UploadMultiple
 *
 * @alias core/controller
 * @author serginho
 */
class UploadMultiple extends Controller {
	
	protected $field = "image";

	public function execute()
	{				
		if(isset($_FILES[$this->field]))
		{
			$file_ids = array();
			
			foreach($_FILES[$this->field]['tmp_name'] as $filename)
			{
				$image_model = $this->app->image->from_file($filename);
				$file_ids[] = $image_model->pk();
			}
			
			$this->response->body(implode(",", $file_ids));
		}
	}
		
	
}
