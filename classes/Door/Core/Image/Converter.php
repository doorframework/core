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
	
	final public function convert(Image $image)
	{
		$this->image = $image;
		$this->go();		
		$image = $this->image;
		unset($this->image);		
		return $image;
	}
		
	protected abstract function go();

}
