<?php
namespace Door\Core\Image\Converter;
use Door\Core\Image\Converter;
use Imagine\Image\Point;
use Imagine\Image\Box;

/** 
 * Конвертер для пропорционального уменьшения или увеличения картинки до определенных
 * ширины и высоты.
 * @package Door/Core
 */
class Fitbox extends Converter {
	
	/**
	 * Ширина
	 * @var int 
	 */
	protected $width = 0;
	
	/**
	 * Высота
	 * @var int
	 */
	protected $height = 0;
	
	/**
	 * Увеличивать ли картинки в случае, если их размеры меньше заданных
	 * @var bool
	 */
	protected $make_large = true;
	
	/**
	 * Работает при $make_large = true
	 * Максимальное увеличение картинки, более которого оно не будет увеличиваться.
	 * если 2, то картинка максимум увеличится в два раза.
	 * @var float
	 */
	protected $max_scale = null;	
	
	/**
	 * Если true, то часть картинки обрежется, для того чтобы картинка ровно вписалась
	 * в отведенные рамки
	 * @var bool
	 */	
	protected $strict = false;		
	
	/**
	 * @param int $width 
	 * @param int $height
	 * @param bool $strict
	 * @param bool $make_large
	 * @param float $max_scale
	 */
	public function __construct($width, $height, $strict = false, $make_large = true, $max_scale = null) {
		
		$this->width = $width;
		$this->height = $height;
		$this->strict = $strict;
		$this->make_large = $make_large;
		$this->max_scale = $max_scale;
		
	}
	
	public function go() {

        if($this->strict)
        {         
			$this->go_strict();
        }		
		else
		{
			$this->go_no_strict();
		}	
	}
	
	protected function go_strict()
	{
		$new_width = $this->width;
		$new_height = $this->height;	

		$image_size = $this->image->getSize();
		$image_width = $image_size->getWidth();
		$image_height = $image_size->getHeight();

		$ratio = $image_width / $image_height;

		if ($new_width / $new_height > $ratio)
		{
			$new_height = $image_height * $new_width / $image_width;
		}
		else
		{
			$new_width = $image_width * $new_height / $image_height;
		}						


		$size = new Box($new_width, $new_height);												
		$this->image->resize($size);			

		$new_image_size = $this->image->getSize();
		$offset_x = round(($new_image_size->getWidth() - $this->width) / 2);
		$offset_y = round(($new_image_size->getHeight() - $this->height) / 2);

		$point = new Point($offset_x, $offset_y);
		$this->image->crop($point, new Box($this->width, $this->height));		
	}
	
	protected function go_no_strict()
	{
		$image_size = $this->image->getSize();
		$image_width = $image_size->getWidth();
		$image_height = $image_size->getHeight();			

		$new_width = $this->width;
		$new_height = $this->height;

		$ratio = $image_width / $image_height;

		if ($new_width / $new_height > $ratio)
		{
			$new_width = $image_width * $new_height / $image_height;				
		}
		else
		{
			$new_height = $image_height * $new_width / $image_width;
		}		


		if($this->max_scale != null)
		{
			$image_size = $this->image->getSize();
			$scale = max($new_width / $image_width, $image_height);
			if($scale > $this->max_scale)
			{
				//Если увеличение больше максимального масштаба, то меняем
				//высоту и ширину, так, чтобы вписать в масштаб
				$new_width = $new_width / $scale * $this->max_scale;
				$new_height = $new_height / $scale * $this->max_scale;
			}
		}

		$box_size = new Box($new_width, $new_height);			
		$this->image->resize($box_size);					
	}
		
}

?>
