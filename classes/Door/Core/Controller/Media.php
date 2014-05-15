<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Controller;
use Door\Core\Helper\File;

/**
 * Description of Media
 *
 * @author serginho
 */
class Media extends \Door\Core\Controller{
	
	public function execute() {
		
		
		
		$path = $this->param('path');
		
		$file_path = $this->app->media->get_path($path);
		
		if($file_path == null)
		{
			$this->request->response()->status(404);
			return;
		}
		
		$extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
		$mime_type = $this->app->media->mime($file_path);		

		if($extension == 'js' || $extension == 'css'){
			$mime_type .= '; charset=utf-8';
		}

		//Копируем файл в папку media самого сайта
		if(false)
		{
			$ds = DIRECTORY_SEPARATOR;
			$arr = explode("{$ds}media{$ds}", $filename);
			array_shift($arr);
			$new_file = DOCROOT."media{$ds}" . implode("{$ds}media{$ds}", $arr);

			$path_info = pathinfo($new_file);
			if(!file_exists($path_info['dirname']))
			{    
				//Бывает такая штука, что между проверкой и созданием папки
				//папка уже создана. Поэтому сделан нижеследущий трюк					
				try
				{
					mkdir($path_info['dirname'], 0777, true);
				}
				catch(ErrorException $ex)
				{}
				if(!file_exists($path_info['dirname']))
				{
					throw new Exception('can`t create directory: '.$path_info['dirname']);
				}

			}
			copy($filename, $new_file);
		}
		//readfile($filename);
		
		$this->response->send_file($file_path, null, array("inline" => true, 'mime_type' => $mime_type));		
		
	}
	
}
