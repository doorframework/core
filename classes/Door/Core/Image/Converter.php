<?php
namespace Door\Core\Image;
use Imagine\Gd\Image;

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
	
	/**
	 * 
	 * @return Image
	 */
	public function get_image()
	{
		return $this->image;
	}	
	
	public abstract function convert();


}
