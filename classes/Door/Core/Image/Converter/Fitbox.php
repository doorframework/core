<?php
namespace Door\Core\Image\Converter;

/** 
 * Конвертер для пропорционального уменьшения или увеличения картинки до определенных
 * ширины и высоты.
 * @package Door/Core
 */
class Fitbox extends Door_Image_Converter {
	
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
	
	public function convert() {
		
		if($this->width == 0 && $this->height == 0)
		{
			throw new Exception("Ширина и высота не заданы");
		}
		if($this->width == 0) $this->width = 10000;
		if($this->height == 0) $this->height = 10000;
		
        if($this->strict)
        {            
            $this->image->resize($this->width, $this->height, Image::INVERSE);
            $this->image->crop($this->width, $this->height);
        }		
		else
		{
			$new_width = $this->width;
			$new_height = $this->height;
		
			if($this->max_scale != null)
			{
				$scale = max($new_width / $image->width, $new_height / $image->height);
				if($scale > $this->max_scale)
				{
					//Если увеличение больше максимального масштаба, то меняем
					//высоту и ширину, так, чтобы вписать в масштаб
					$new_width = $new_width / $scale * $this->max_scale;
					$new_height = $new_height / $scale * $this->max_scale;
				}
			}
			
			$this->image->resize($new_width, $new_height, Image::AUTO);
		}	
	}
		
}

?>
