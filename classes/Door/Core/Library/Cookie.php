<?php defined('SYSPATH') OR die('No direct script access.');


namespace Door\Core\Library;
/**
 * Cookie helper.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Cookie extends \Door\Core\Library {

	/**
	 * @var  string  Magic salt to add to the cookie
	 */
	public $salt = NULL;

	/**
	 * @var  integer  Number of seconds before the cookie expires
	 */
	public $expiration = 0;

	/**
	 * @var  string  Restrict the path that the cookie is available to
	 */
	public $path = '/';

	/**
	 * @var  string  Restrict the domain that the cookie is available to
	 */
	public $domain = NULL;

	/**
	 * @var  boolean  Only transmit cookies over secure connections
	 */
	public $secure = FALSE;

	/**
	 * @var  boolean  Only transmit cookies over HTTP, disabling Javascript access
	 */
	public $httponly = FALSE;

	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 *     $theme = $this->get('theme', 'blue');
	 *
	 * @param   string  $key        cookie name
	 * @param   mixed   $default    default value to return
	 * @return  string
	 */
	public function get($key, $default = NULL)
	{
		if ( ! isset($_COOKIE[$key]))
		{
			// The cookie does not exist
			return $default;
		}

		// Get the cookie value
		$cookie = $_COOKIE[$key];

		// Find the position of the split between salt and contents
		$split = strlen($this->salt($key, NULL));

		if (isset($cookie[$split]) AND $cookie[$split] === '~')
		{
			// Separate the salt and the value
			list ($hash, $value) = explode('~', $cookie, 2);

			if ($this->salt($key, $value) === $hash)
			{
				// Cookie signature is valid
				return $value;
			}

			// The cookie signature is invalid, delete it
			$this->delete($key);
		}

		return $default;
	}

	/**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 *     // Set the "theme" cookie
	 *     $this->set('theme', 'red');
	 *
	 * @param   string  $name       name of cookie
	 * @param   string  $value      value of cookie
	 * @param   integer $expiration lifetime in seconds
	 * @return  boolean
	 * @uses    $this->salt
	 */
	public function set($name, $value, $expiration = NULL)
	{
		if ($expiration === NULL)
		{
			// Use the default expiration
			$expiration = $this->expiration;
		}

		if ($expiration !== 0)
		{
			// The expiration is expected to be a UNIX timestamp
			$expiration += time();
		}

		// Add the salt to the cookie value
		$value = $this->salt($name, $value).'~'.$value;

		return setcookie($name, $value, $expiration, $this->path, $this->domain, $this->secure, $this->httponly);
	}

	/**
	 * Deletes a cookie by making the value NULL and expiring it.
	 *
	 *     $this->delete('theme');
	 *
	 * @param   string  $name   cookie name
	 * @return  boolean
	 */
	public function delete($name)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return setcookie($name, NULL, -86400, $this->path, $this->domain, $this->secure, $this->httponly);
	}

	/**
	 * Generates a salt string for a cookie based on the name and value.
	 *
	 *     $salt = $this->salt('theme', 'red');
	 *
	 * @param   string  $name   name of cookie
	 * @param   string  $value  value of cookie
	 * @return  string
	 */
	public function salt($name, $value)
	{
		// Require a valid salt
		if ( ! $this->salt)
		{
			throw new Kohana_Exception('A valid cookie salt is required. Please set $app->cookie->salt in your bootstrap. For more information check the documentation');
		}

		// Determine the user agent
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		return sha1($agent.$name.$value.$this->salt);
	}

}
