<?php

namespace Door\Core;


/**

 *
 * @package    Door Core
 * @category   Base
 * @author     Sachik Sergey
 * @copyright  (c) 2014 Sachik Sergey
 */
class Route {
	

	/**
	 * Matches a URI group and captures the contents
	 */
	const REGEX_GROUP   = '\(((?:(?>[^()]+)|(?R))*)\)';

	/**
	 * Defines the pattern of a <segment>
	 */
	const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';

	/**
	 * What can be part of a <segment> value
	 */
	const REGEX_SEGMENT = '[^/.,;?\n]++';
	
	/**
	 * What must be escaped in the route regex
	 */	
	const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';	
	
	protected $uri;
	
	protected $controller_class = null;
	
	protected $regex = array();
	
	protected $compiled_regex = null;
	
	protected $defaults = array();
	
	protected $controller_config = array();
			
	protected $wrappers = array();

	public function __construct($uri, $controller_class, array $regex = array())
	{
		$this->uri = trim($uri, "/");
		
		$this->controller_class = str_replace("/", "\\", $controller_class);
		
		$this->regex = $regex;
		
		$this->compiled_regex = $this->compile();
	}
	
	protected function compile()
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for : ( ) < >
		$expression = preg_replace('#'.self::REGEX_ESCAPE.'#', '\\\\$0', $this->uri);

		if (strpos($expression, '(') !== FALSE)
		{
			// Make optional parts of the URI non-capturing and optional
			$expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
		}

		// Insert default regex for keys
		$expression = str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_SEGMENT.')'), $expression);

		if ($this->regex)
		{
			$search = $replace = array();
			foreach ($this->regex as $key => $value)
			{
				$search[]  = "<$key>".self::REGEX_SEGMENT;
				$replace[] = "<$key>$value";
			}

			// Replace the default regex with the user-specified regex
			$expression = str_replace($search, $replace, $expression);
		}

		return '#^'.$expression.'$#uD';		
	}
	
	/**
	 * Tests if the route matches a given Request. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 *
	 *     // Params: controller = users, action = edit, id = 10
	 *     $params = $route->matches(Request::factory('users/edit/10'));
	 *
	 * This method should almost always be used within an if/else block:
	 *
	 *     if ($params = $route->matches($request))
	 *     {
	 *         // Parse the parameters
	 *     }
	 *
	 * @param   Request $request  Request object to match
	 * @return  array             on success
	 * @return  FALSE             on failure
	 */
	public function matches(Request $request)
	{
		// Get the URI from the Request
		$uri = trim($request->uri(), '/');

		if ( ! preg_match($this->compiled_regex, $uri, $matches))
			return FALSE;

		$params = array();
		foreach ($matches as $key => $value)
		{
			if (is_int($key))
			{
				// Skip all unnamed keys
				continue;
			}

			// Set the value for all matched keys
			$params[$key] = $value;
		}

		return $params;
	}	
	
	public function deafults(array $params)
	{
		foreach($params as $key => $value)
		{
			$this->defaults[$key] = $value;
		}
	}
	
	/**
	 * 
	 * @param array $params
	 * @return array
	 * @return \Door\Core\Route
	 */
	public function controller_config()
	{
		return $this->controller_config;
	}
	
	/**
	 * 
	 * @param array $params
	 * @return \Door\Core\Route
	 */
	public function add_config(array $params)
	{
		$this->controller_config = $params + $this->controller_config;
		return $this;
	}
			
	
	public function reset_config()
	{
		$this->controller_config = array();
	}
	
	/**
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array   $params URI parameters
	 * @return  string
	 * @throws  Kohana_Exception
	 * @uses    Route::REGEX_GROUP
	 * @uses    Route::REGEX_KEY
	 */
	public function uri(array $params = NULL)
	{
		$defaults = $this->defaults;

		/**
		 * Recursively compiles a portion of a URI specification by replacing
		 * the specified parameters and any optional parameters that are needed.
		 *
		 * @param   string  $portion    Part of the URI specification
		 * @param   boolean $required   Whether or not parameters are required (initially)
		 * @return  array   Tuple of the compiled portion and whether or not it contained specified parameters
		 */
		$compile = function ($portion, $required) use (&$compile, $defaults, $params)
		{
			$missing = array();

			$pattern = '#(?:'.Route::REGEX_KEY.'|'.Route::REGEX_GROUP.')#';
			$result = preg_replace_callback($pattern, function ($matches) use (&$compile, $defaults, &$missing, $params, &$required)
			{
				if ($matches[0][0] === '<')
				{
					// Parameter, unwrapped
					$param = $matches[1];

					if (isset($params[$param]))
					{
						// This portion is required when a specified
						// parameter does not match the default
						$required = ($required OR ! isset($defaults[$param]) OR $params[$param] !== $defaults[$param]);

						// Add specified parameter to this result
						return $params[$param];
					}

					// Add default parameter to this result
					if (isset($defaults[$param]))
						return $defaults[$param];

					// This portion is missing a parameter
					$missing[] = $param;
				}
				else
				{
					// Group, unwrapped
					$result = $compile($matches[2], FALSE);

					if ($result[1])
					{
						// This portion is required when it contains a group
						// that is required
						$required = TRUE;

						// Add required groups to this result
						return $result[0];
					}

					// Do not add optional groups to this result
				}
			}, $portion);

			if ($required AND $missing)
			{
				throw new Kohana_Exception(
					'Required route parameter not passed: :param',
					array(':param' => reset($missing))
				);
			}

			return array($result, $required);
		};

		list($uri) = $compile($this->uri, TRUE);

		return $uri;
	}	
	
	/**
	 * @return string
	 */
	public function controller()
	{
		return $this->controller_class;
	}
	
	public function wrap($wrapper, $config = array(), $weight = 0)
	{
		$this->wrappers[] = array(
			'wrapper' => $wrapper,
			'config' => $config,
			'weight' => $weight
		);
	}
	
	public function wrappers()
	{
		return $this->wrappers;
	}
}
