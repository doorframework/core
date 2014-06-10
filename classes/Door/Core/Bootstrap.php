<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core;
 
/**
 * Helper for bootstrap Door framework
 *
 * @author serginho
 */
class Bootstrap {

	protected static $core_libraries = array(
		"router" => '/Door/Core/Library/Router',
		"views" => '/Door/Core/Library/Views',
		"media" => '/Door/Core/Library/Media',
		"html" => '/Door/Core/Library/HTML',
		"url" => '/Door/Core/Library/URL',
		"auth" => '/Door/Core/Library/Auth',
		"session" => '/Door/Core/Library/Session',
		"cookie" => '/Door/Core/Library/Cookie',
		"image" => '/Door/Core/Library/Image',
		"database" => '/Door/Core/Library/Database',
		"models" => '/Door/Core/Library/Models',
		"lang" => '/Door/Core/Library/Language'
	);	
	
	protected static $core_models = array(
		"User" => array(
			"class" => "/Door/Core/Model/User",
			"collection" => "users"
		),
		"Role" => array(
			"class" => "/Door/Core/Model/Role",
			"collection" => "roles"
		),
		"Image" => array(
			"class" => "/Door/Core/Model/Image",
			"collection" => "images"
		),
		"User_Token" => array(
			"class" => "/Door/Core/Model/User/Token",
			"collection" => "user_tokens"
		),
	);		
	
	public static function init(Application $app)
	{
		foreach(self::$core_libraries as $library => $class_name)
		{
			if( ! $app->library_exists($library))
			{
				$app->register_library($library, $class_name);
			}
		}		
		
		foreach(self::$core_models as $model => $cfg)
		{
			if( ! $app->models->model_registered($model))
			{
				$app->models->add($model, $cfg['class'], $cfg['collection']);
			}
		}
		
		$app->router->register_wrapper("core/needauth", "/Door/Core/Wrapper/Needauth");
		
		register_shutdown_function(array($app->session, 'write'));
		
		$app->router->register_controller("core/upload_image", "/Door/Core/Controller/Image/UploadOne");
		$app->router->register_controller("core/upload_images", "/Door/Core/Controller/Image/UploadMultiple");
		$app->router->register_controller("core/view_images", "/Door/Core/Controller/Image/View");
		$app->router->register_controller("core/delete", "/Door/Core/Controller/Delete");
		$app->router->register_controller("core/media", "/Door/Core/Controller/Media");
		$app->router->register_controller("core/redirect", "/Door/Core/Controller/Redirect");
		
		
	}
	
	
}
