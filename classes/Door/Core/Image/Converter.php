<?php
namespace Door\Core\Image;

/**
 * Базовый класс для всех конвертеров изображений
 * @package Door/Core
 */
abstract class Converter {
	
	/**
	 * Картинка
	 * @var Image
	 */
	protected $image;

	public function __construct(Image $image, array $config) {
		
		foreach($config as $key => $value)
		{
			if(property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
		$this->image = $image;
		
	}
	
	public function get_image()
	{
		return $this->image;
	}
	
	/**
	 *
	 * @param string $class
	 * @param Image $image
	 * @param array $config 
	 * @return Door_Image_Converter
	 */
	public static function factory($class, Image $image, array $config)
	{
		if(class_exists("Door_Image_Converter_".$class))
		{
			$class = "Door_Image_Converter_".$class;
		}		
		
		if( ! class_exists($class))
		{
			throw new Exception("converter '$class' not founded");
		}
		
		return new $class($image, $config);
	}
	
	public abstract function convert();


}
