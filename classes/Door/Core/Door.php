<?php

namespace Door\Core;

/**
 * Contains the most low-level helpers methods in Kohana:
 *
 * - Environment initialization
 * - Locating files within the cascading filesystem
 * - Auto-loading and transparent extension of classes
 * - Variable and path debugging
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Door {

	// Release version and codename
	const VERSION  = '0.0.1';
	const CODENAME = 'frankenshtein';

	// Common environment type constants for consistency and convenience
	const PRODUCTION  = 10;
	const STAGING     = 20;
	const TESTING     = 30;
	const DEVELOPMENT = 40;

	public static $environment = self::DEVELOPMENT;	

	/**
	 *
	 * @var Loader
	 */
	public static $loader;
	
	/**
	 *
	 * @var Router
	 */
	public static $router;
	
	
}
