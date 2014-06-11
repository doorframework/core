<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Library;
use Door\Core\View;
use \Exception;
/**
 * Description of Views
 *
 * @author serginho
 */
class Views extends \Door\Core\Library {
	
	/*protected $paths = array();
	
	public function add($prefix, $path, $priority = 0)
	{		
		$this->paths[] = array(
			'prefix' => trim($prefix, "/"),
			'path' => rtrim($path, "/"),
			'priority' => $priority
		);
	}
	
	public function get_path($name)
	{
		$name = trim($name, "/");
		
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
			if ($a['priority'] == $b['priority'])
			{
				return 0;
			}
			else if ($a['priority'] > $b['priority'])
			{
				return -1;
			}
			else 
			{
				return 1;
			}
		});
		
		$view_path = null;
		
		foreach($success_paths as $path_config)
		{
			$path = $path_config['path'] . "/" . $name . ".php";
			
			if(file_exists($path))
			{
				$view_path = $path;
				break;
			}
		}
		
		return $view_path;
	}*/
	
	/**
	 * @param string $name
	 * @return View
	 */
	public function get($name, $data = null)
	{
		$files = $this->app->find_files("views", $name, "php");		
		
		if( ! is_array($files) || count($files) == 0)
		{
			$name = htmlspecialchars($name);
			throw new Exception("View {$name} not found");
		}
		
		$path = $files[0];
		
		if($data === null)
		{
			$data = array();
		}
		
		$data['app'] = $this->app;
		
		return new View($path, $data);				
	}
	
}
