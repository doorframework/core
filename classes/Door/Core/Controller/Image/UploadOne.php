<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Controller\Image;
use Door\Core\Controller;

/**
 * Upload one image
 * body of response is image id
 *
 * @author serginho
 */
class UploadOne extends Controller {


	public function execute()
	{
		
		if(count($_FILES) > 0)
		{
			foreach($_FILES as $file)
			{
				$filename = $file['tmp_name'];
				$image_model = $this->app->image->from_file($filename);
								
				if(isset($_GET['CKEditorFuncNum']))
				{
					$url = $this->app->image->url($image_model->pk());
					// Required: anonymous function reference number as explained above.
					$funcNum = $_GET['CKEditorFuncNum'] ;
					// Optional: instance name (might be used to load a specific configuration file or anything else).
					$CKEditor = $_GET['CKEditor'] ;
					// Optional: might be used to provide localized messages.
					$langCode = $_GET['langCode'] ;

					$this->response->body("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url');</script>");
				}
				else
				{
					$this->response->body($image_model->pk());
				}
				break;
			}
		}
	}
		
	
}
