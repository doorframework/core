<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Library;
use Door\Core\Model\Image as Model_Image;
use Imagine\Gd\Imagine;
use Door\Core\Helper\Arr;
use Imagine\Gd\Image as Img;

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
		$data = $this->data($image_id, $presentation);
		return $this->app->html->image($data['url'], array(
			'width' => $data['width'],
			'height' => $data['height'],
			'alt' => $data['title']
		));
	}
	
	public function url($image_id, $presentation = 'default')
	{
		$data = $this->data($image_id, $presentation);
		return $data['url'];
	}
	
	public function data($image_id, $presentation = 'default')
	{
		if($image_id == null)
		{
			return null;
		}
		
		$image = $this->app->models->factory("Image", $image_id);
		
		if(! $image->loaded())
		{
			throw new Exception("Image not found");
		}
		
		if($presentation == 'default')
		{
			return array(
				'url' => $this->calculate_url($image_id, $image->extension),
				'path' => $this->calculate_path($image_id, $image->extension),
				'width' => $image->width,
				'height' => $image->height,
				'title' => $image->title,
				'description' => $image->description
			);
		}
		
		$presentation_data = $image->get_presentation($presentation);
		
		if($presentation_data === null)
		{
			$default_file_path = $this->calculate_path($image_id, $image->extension);
			if( !file_exists($default_file_path))
			{
				throw new Exception("default image for image#{$image_id} not found");
			}
			
			$imagine = new Imagine;
			$image_gd = $imagine->load($default_file_path);
			
			$new_image_gd = $this->commit_converters($image_gd, $presentation);
			
			$extension = Arr::get($this->configs[$presentation], 'extension', $image->extension);
			
			$path = $this->calculate_path($image_id, $extension);
			
			$dirname = dirname($path);
			if( !file_exists($dirname))
			{
				mkdir($dirname,0777,true);
			}			
			
			$new_image_gd->save($path);
			
			$presentation_data = array(
				'width' => $new_image_gd->getSize()->getWidth(),
				'height' => $new_image_gd->getSize()->getHeight(),
				'extension' => $extension
			);
			
			$image->add_presentation($presentation_data);
		}		
		
		return array(
			'url' => $this->calculate_url($image_id, $presentation_data['extension']),
			'path' => $this->calculate_path($image_id, $presentation_data['extension']),
			'width' => $presentation_data['width'],
			'height' => $presentation_data['height'],
			'title' => $image->title,
			'description' => $image->description			
		);		
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
        
        $imagesize = getimagesize($filename);
        if($imagesize === false)
        {
            return false;
        }

		$imagine = new Imagine;
		$image = $imagine->load($filename);

		$mime = $imagesize['mime'];
		$extension = $this->app->media->ext_by_mime($mime);			

		$model_image = $this->app->models->factory("Image");
		$model_image->extension = $extension;
		$model_image->width = $image->getSize()->getWidth();
		$model_image->height = $image->getSize()->getHeight();
		$model_image->save();

		$path = $this->calculate_path($model_image->pk(), $extension);
		
		$dirname = dirname($path);
		if( !file_exists($dirname))
		{
			mkdir($dirname,0777,true);
		}
		
		$image->save($path);			

        return $model_image;	
	}

	/**
	 * 
	 * @param \Imagine\Gd\Imagine $image
	 * @param type $presentation
	 * @return Img
	 */
	protected function commit_converters(Imagine $image, $presentation = "default")
	{
		if( ! isset($this->converters[$presentation]))
		{
			throw new Exception("Presentation {$presentation} not found");
		}
		
		foreach($this->converters[$presentation] as $converter_config)
		{
			$converter_class = $converter_config['converter'];
			$converter = new $converter_class($image, $converter_config);
			$converter->convert();
			$image = $converter->get_image();
		}
		return $image;
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
