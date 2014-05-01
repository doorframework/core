<?php

namespace Door\Core;

/**
 * Description of Loader
 *
 * @author serginho
 */
class Loader {
	
	protected $namespaces = array();
	
	protected $sorted = false;
	
	
	public function register_namespace($namespace, $path)
	{
		$this->namespaces[$namespace] = $path;
	}
	
	public function register_namespaces(array $namespaces)
	{
		foreach($namespaces as $namespace => $path)
		{
			$this->register_namespace($namespace, $path);
		}
	}
	
	public function autoload($class)
	{
		if(substr($class, 0, 1) == "\\")
		{
			$class = substr($class, 1);
		}
		
		
		if( ! $this->sorted)
		{
			//sort namespaces by length
			$new_namespaces = array();
			$keys = array_keys($this->namespaces);
			usort($keys, function($a,$b){
				return strlen($a)-strlen($b);
			});
			foreach($keys as $key)
			{
				$new_namespaces[$key] = $this->namespaces[$key];
			}
			$this->namespaces = $new_namespaces;					
			$this->sorted = true;
		}
		
		foreach($this->namespaces as $namespace => $path)
		{
			if($namespace == $class && strpos(".php", $path) !== false) 
			{
				require_once $path;
				break;
			}
			elseif(strpos($class, $namespace) === 0)
			{
				$class_path = str_replace("\\","/",substr($class, strlen($namespace) + 1));
				$file_path = $path.$class_path.".php";				
				
				if( ! file_exists($file_path))
				{
					throw new \Exception("class $class not found in $path");
				}
				
				require_once $file_path;
				
				break;
			}
		}	
	}
	
}
