<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Library;
use Door\Core\Model\Image as Model_Image;
use Imagine\Gd\Imagine;
use Imagine\Gd\Image as Img;
use Door\Core\Image\Presentation;
use Door\Core\Image\Converter;
use Exception;

/**
 * Library for Image manipulation
 *
 * @package Door\Core
 * @author serginho
 */
class Image extends \Door\Core\Library {
	
	CONST FOLDER = "upload/images/";	
	
	protected $presentations = array();
	
	
	public function add(Presentation $presentation)
	{
		$name = $presentation->name();
		if(isset($this->presentations[$name]))
		{
			throw new Exception('already registered');
		}
		
		$this->presentations[$name] = $presentation;
		
		return $this;
	}
	
	public function render($image_id, $presentation_name = 'default')
	{
		$data = $this->data($image_id, $presentation_name);
		return $this->app->html->image($data['url'], array(
			'width' => $data['width'],
			'height' => $data['height'],
			'alt' => $data['title']
		));
	}
	
	/**
	 * 
	 * @param type $image_id
	 * @param type $presentation
	 * @return string
	 */
	public function url($image_id, $presentation_name = 'default')
	{
		$data = $this->data($image_id, $presentation_name);
		return $data['url'];
	}
	
	/**
	 * 
	 * @param type $image_id
	 * @param type $presentation
	 * @return array
	 * @throws Exception
	 */
	public function data($image_id, $presentation_name = 'default')
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
		
		if($presentation_name == 'default')
		{
			return array(
				'url' => $this->calculate_url($image_id, $image->extension, $presentation_name),
				'path' => $this->calculate_path($image_id, $image->extension, $presentation_name),
				'width' => $image->width,
				'height' => $image->height,
				'title' => $image->title,
				'description' => $image->description
			);
		}
		
		if( ! isset($this->presentations[$presentation_name]))
		{
			throw new Exception("presentation $presentation_name not found");
		}
		
		$presentation_data = $image->get_presentation($presentation_name);
		
		if($presentation_data !== null)
		{
			$path = $this->calculate_path($image_id, $presentation_data['extension'], $presentation_name);
			if(!file_exists($path))
			{
				$presentation_data = null;
			}
		}	
		
		
		if($presentation_data === null)
		{
			$default_file_path = $this->calculate_path($image_id, $image->extension);
			if( !file_exists($default_file_path))
			{
				throw new Exception("default image for image#{$image_id} not found");
			}
			
			$imagine = new Imagine;
			$image_gd = $imagine->open($default_file_path);
			
			$presentation = $this->presentations[$presentation_name];
			
			$new_image_gd = $this->commit_converters($image_gd, $presentation);						
			
			$extension = $presentation->extension() == null ? $image->extension : $presentation->extension();
			
			$path = $this->calculate_path($image_id, $extension, $presentation_name);
			
			$dirname = dirname($path);
			if( !file_exists($dirname))
			{
				mkdir($dirname,0777,true);
			}								
			
			$options = array();
			if($presentation->quality() !== null)
			{
				$options['quality'] = $presentation->quality();
			}			
			
			$new_image_gd->save($path, $options);
			
			$presentation_data = array(
				'width' => $new_image_gd->getSize()->getWidth(),
				'height' => $new_image_gd->getSize()->getHeight(),
				'extension' => $extension
			);
			
			$image->add_presentation($presentation_name, $presentation_data);
		}		
		
		return array(
			'url' => $this->calculate_url($image_id, $presentation_data['extension'], $presentation_name),
			'path' => $this->calculate_path($image_id, $presentation_data['extension'], $presentation_name),
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
		$image = $imagine->open($filename);

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
		
		copy($filename, $path);		
		//$image->save($path);			

        return $model_image;	
	}

	/**
	 * 
	 * @param \Imagine\Gd\Imagine $image
	 * @param type $presentation
	 * @return Img
	 */
	protected function commit_converters(Img $image, Presentation $presentation)
	{		
		foreach($presentation->converters() as $converter)
		{
			$image = $this->do_convert($image, $converter);
		}
		return $image;
	}
	
	protected function do_convert(Img $image, Converter $converter)
	{
		return $converter->convert($image);	
	}
	
	protected function calculate_path($image_id, $extension, $presentation_name = 'default')
	{
		return $this->app->docroot()."/".$this->calculate_uri($image_id, $extension, $presentation_name);
	}
	
	protected function calculate_uri($image_id, $extension, $presentation_name = 'default')
	{
		if(strlen($image_id) < 10)
		{
			throw new Exception("bad image_id specified");
		}
		
		$a1 = substr($image_id, 0, 2);
		$a2 = substr($image_id, 2, 2);		
		
		return self::FOLDER."{$a1}/{$a2}/{$image_id}.{$presentation_name}.{$extension}";
	}
	
	public function calculate_url($image_id, $extension, $presentation_name = 'default')
	{
		return $this->app->url->site($this->calculate_uri($image_id, $extension, $presentation_name));
	}
	
}
