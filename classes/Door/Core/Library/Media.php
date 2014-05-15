<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Library;

/**
 * Description of Media
 *
 * @author serginho
 */
class Media extends \Door\Core\Library {
	

	protected $paths = array();
	
	public function add($prefix, $path, $priority = 0)
	{		
		$this->paths[] = array(
			'prefix' => trim($prefix, "/"),
			'path' => rtrim($path, "/"),
			'priority' => $priority
		);
	}
	
	public function get_path($file)
	{
		$file = trim($file, "/");
		
		$success_paths = array();
		
		foreach($this->paths as $path_config)
		{
			$prefix = $path_config['prefix'];
			if($prefix == "" || strpos($name, $prefix) === 0) 
			{
				$success_paths[] = $path_config;
			}
		}
				
		usort($success_paths, function($a, $b)
		{
			if ($a['sort'] == $b['sort'])
			{
				return 0;
			}
			else if ($a['sort'] > $b['sort'])
			{
				return -1;
			}
			else 
			{
				return 1;
			}
		});
		
		$file_path = null;
		
		foreach($success_paths as $path_config)
		{
			$path = $path_config['path'] . "/" . $file;
			
			if(file_exists($path))
			{
				$file_path = $path;
				break;
			}
		}
		
		return $file_path;
	}
	
	public function mime($filename)
	{

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $filename);
		finfo_close($finfo);
		return $mime;

	}
	
}
