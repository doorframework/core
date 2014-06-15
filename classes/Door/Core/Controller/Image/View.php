<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Controller\Image;

/**
 * Controller for rendering images
 * Works in to variants: List of images and one image
 * 
 * For one image in $_POST array need to be key "image_id" with id of image
 * For list of images in $_POST array need to be key "images" with string list
 * of images ids separated by ','
 * 
 * Configuration parameters
 *  - list_view : View name for list of images (default null)
 *		if null it will return list of image tags with all parameters
 *  - item_view : View name for one image (default null)
 *		if null it will return img tag with all parameters
 *  - list_image : Image presentation for images in list (default 'default')
 *  - item_image : Image presentation for one image (default 'default')
 * 
 * Parameters, passed to view
 *  - list_view
 *		$image_ids - Array of image_ids
 *		$image_presentation - Image presentation for images in list
 *  - item_view
 *		$image_id - image id
 *		$image_presentation -  Image presentation for image
 *
 * 
 * @alias core/images/view
 * @author serginho
 */
class View extends \Door\Core\Controller {
	
	protected $list_view = null;
	
	protected $item_view = null;
	
	protected $list_image = "default";
	
	protected $item_image = 'default';
	
	public function execute() 
	{		
		if(isset($_POST['image_id']))
		{
			$image_id = $_POST['image_id'];
			if($this->app->is_id($image_id))
			{
				$this->show_image($image_id);
			}
		}
		elseif(isset($_POST['images']))
		{
			$image_ids_post = explode(",", $_POST['images']);
			$image_ids = array();
			
			foreach($image_ids_post as $image_id_post)
			{
				if($this->app->is_id($image_id_post))
				{
					$image_ids[] = $image_id_post;
				}
			}
			
			if(count($image_ids) > 0)
			{
				$this->show_list($image_ids);
			}			
		}
	}
	
	protected function show_image($image_id)
	{
		if($this->item_view == null)
		{
			$this->response->body($this->app->image->render($image_id, $this->item_image));
		}
		else
		{
			$view = $this->app->views->get($this->item_view,array(
				'image_id' => $image_id,
				'image_presentation' => $this->item_image
			));
			$this->response->body($view->render());
		}
	}
	
	protected function show_list(array $image_ids)
	{
		if($this->list_view == null)
		{
			$return_value = array();
			foreach($image_ids as $image_id)
			{
				$return_value[] = $this->app->image->render($image_id, $this->list_image);
			}			
			$this->response->body(implode("", $return_value));
		}
		else
		{
			$view = $this->app->views->get($this->list_view,array(
				'image_ids' => $image_ids,
				'image_presentation' => $this->list_image
			));
			$this->response->body($view->render());
		}		
	}
	
}
