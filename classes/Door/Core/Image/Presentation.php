<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Image;
/**
 * Description of Presentation
 *
 * @author serginho
 */
class Presentation {
	
	protected $name;
	
	protected $converters = array();
	
	protected $extension = null;
	
	protected $quality = null;
	
	public function __construct($name, $converters, $quality = 80, $extension = null) {
								
		$this->name = $name;	
		$this->extension = $extension;
		$this->quality = $quality;
		if($converters instanceof Converter)
		{
			$this->add_converter($converters);
		}
		else
		{
			array_map(array($this,'add_converter'), $converters);
		}	
		
	}
	
	public function name()
	{
		return $this->name;
	}
	
	public function converters()
	{
		return $this->converters;
	}
	
	public function extension()
	{
		return $this->extension;
	}
	
	public function quality()
	{
		return $this->quality;
	}
	
	
	public function add_converter(Converter $converter)
	{
		$this->converters[] = $converter;
	}
	
}
