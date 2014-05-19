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
		"database" => '/Door/Core/Library/Database',
		"models" => '/Door/Core/Library/Models',
		"lang" => '/Door/Core/Library/Lang'
	);	
	
	public static function register_core_libraries(Application $app)
	{
		foreach(self::$core_libraries as $library => $class_name)
		{
			if( ! $app->library_exists($library))
			{
				$app->register_library($library, $class_name);
			}
		}		
		
		register_shutdown_function(array($app->session, 'write'));
	}
	
	
}
