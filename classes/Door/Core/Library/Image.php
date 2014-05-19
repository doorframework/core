<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Library;
use Door\Core\Model\Image as Model_Image;
use Imagine\Gd\Imagine;

/**
 * Library for Image manipulation
 *
 * @package Door\Core
 * @author serginho
 */
class Image extends \Door\Core\Library {
	
	CONST FOLDER = "upload/images/";	
	
	protected $presentations = array();
	
	protected $converters = array();
	
	protected $configs = array();
	
	protected $aliases = array();
	
	
	public function add_presentation($name, array $converters, array $config = array())
	{
		foreach($converters as & $item)
		{
			if( ! isset($item['converter']))
			{
				throw new Exception("no converter found in config");
			}
			if(isset($this->aliases[$item['converter']]))
			{
				$item['converter'] = $this->aliases[$item['converter']];
			}
		}
		
		$this->converters[$name] = $converters;
		
		$this->configs[$name] = $config;			
	}
	
	public function register_converter($name, $class_name)
	{
		$this->aliases[$name] = $class_name;
	}
	
	
	
	public function render($image_id, $presentation = 'default')
	{
				
	}
	
	public function url($image_id, $presentation = 'default')
	{
		
	}
	
	public function data($image_id, $presentation = 'default')
	{
		
	}
	
	/**
	 * 
	 * @param array $tmp_file
	 * @return Model_Image
	 */
	public function from_file($filename)
	{
		if(empty($filename) || !file_exists($filename))
        {
            return false;
        }
        
        $returnValue = false;

        $imagesize = getimagesize($filename);
        if($imagesize === false)
        {
            return false;
        }

        try
        {	
			$model_image = $this->app->models->factory("Image");
			$model_image->save();
			
			$image = new Imagine;
			$image->load($filename);

			$new_image = $this->commit_converters($image, "default");
			$this->save_image($new_image, 'default');	
			
			//for default we create and small presentation too
			$this->get_path('small');

            if($unlink){
                unlink($filename);
            }

            $returnValue = $this;
            
        }
        catch(Exception $e)
        {						
            Kohana::$log->add(Kohana_Log::ERROR, Kohana_Exception::text($e));
            throw $e;
        }

        return $returnValue;		
	}
	
	protected function commit_converters(Imagine $image, $presentation = "default")
	{
		
	}
	
	protected function calculate_path($image_id, $extension, $presentation = 'default')
	{
		return $this->app->docroot()."/".$this->calculate_url($image_id, $extension, $presentation);
	}
	
	protected function calculate_uri($image_id, $extension, $presentation = 'default')
	{
		if(strlen($image_id) < 10)
		{
			throw new Exception("bad image_id specified");
		}
		
		$a1 = substr($image_id, 0, 2);
		$a2 = substr($image_id, 2, 2);		
		
		return self::FOLDER."{$a1}/{$a2}/{$id}.{$presentation}.{$extension}";
	}
	
	public function calculate_url($image_id, $extension, $presentation = 'default')
	{
		return $this->app->url->site($this->calculate_uri($image_id, $extension, $presentation));
	}
	
}
