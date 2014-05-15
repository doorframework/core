<?php defined('SYSPATH') OR die('No direct script access.');

namespace Door\Core\Library;

/**
 * Base session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Session extends \Door\Core\Library {


	/**
	 * @var  int  cookie lifetime
	 */
	public $lifetime = 0;

	/**
	 * @var  array  session data
	 */
	public $data = array();

	/**
	 * @var  bool  session destroyed?
	 */
	public $destroyed = FALSE;

	public function init() {
		
		
		$this->read($id);
		
		parent::init();
	}

	/**
	 * Session object is rendered to a serialized string. If encryption is
	 * enabled, the session will be encrypted. If not, the output string will
	 * be encoded.
	 *
	 *     echo $session;
	 *
	 * @return  string
	 * @uses    Encrypt::encode
	 */
	public function __toString()
	{
		// Serialize the data array
		$data = $this->serialize($this->_data);

		$data = $this->encode($data);

		return $data;
	}

	/**
	 * Returns the current session array. The returned array can also be
	 * assigned by reference.
	 *
	 *     // Get a copy of the current session data
	 *     $data = $session->as_array();
	 *
	 *     // Assign by reference for modification
	 *     $data =& $session->as_array();
	 *
	 * @return  array
	 */
	public function & as_array()
	{
		return $this->_data;
	}

	/**
	 * Get the current session cookie name.
	 *
	 *     $name = $session->name();
	 *
	 * @return  string
	 * @since   3.0.8
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Get a variable from the session array.
	 *
	 *     $foo = $session->get('foo');
	 *
	 * @param   string  $key        variable name
	 * @param   mixed   $default    default value to return
	 * @return  mixed
	 */
	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
	}

	/**
	 * Get and delete a variable from the session array.
	 *
	 *     $bar = $session->get_once('bar');
	 *
	 * @param   string  $key        variable name
	 * @param   mixed   $default    default value to return
	 * @return  mixed
	 */
	public function get_once($key, $default = NULL)
	{
		$value = $this->get($key, $default);

		unset($this->_data[$key]);

		return $value;
	}

	/**
	 * Set a variable in the session array.
	 *
	 *     $session->set('foo', 'bar');
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  value
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;

		return $this;
	}

	/**
	 * Set a variable by reference.
	 *
	 *     $session->bind('foo', $foo);
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  referenced value
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->_data[$key] =& $value;

		return $this;
	}

	/**
	 * Removes a variable in the session array.
	 *
	 *     $session->delete('foo');
	 *
	 * @param   string  $key,...    variable name
	 * @return  $this
	 */
	public function delete($key)
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			unset($this->_data[$key]);
		}

		return $this;
	}

	/**
	 * Loads existing session data.
	 *
	 *     $session->read();
	 *
	 * @param   string  $id session id
	 * @return  void
	 */
	public function read($id = NULL)
	{
		$data = NULL;

		try
		{
			if (is_string($data = $this->_read($id)))
			{
				$data = $this->_decode($data);

				// Unserialize the data
				$data = $this->_unserialize($data);
			}
			else
			{
				// Ignore these, session is valid, likely no data though.
			}
		}
		catch (Exception $e)
		{
			// Error reading the session, usually a corrupt session.
			throw new Session_Exception('Error reading session data.', NULL, Session_Exception::SESSION_CORRUPT);
		}

		if (is_array($data))
		{
			// Load the data locally
			$this->_data = $data;
		}
	}

	/**
	 * Generates a new session id and returns it.
	 *
	 *     $id = $session->regenerate();
	 *
	 * @return  string
	 */
	public function regenerate()
	{
		return $this->_regenerate();
	}

	/**
	 * Sets the last_active timestamp and saves the session.
	 *
	 *     $session->write();
	 *
	 * [!!] Any errors that occur during session writing will be logged,
	 * but not displayed, because sessions are written after output has
	 * been sent.
	 *
	 * @return  boolean
	 * @uses    Kohana::$log
	 */
	public function write()
	{
		if (headers_sent() OR $this->_destroyed)
		{
			// Session cannot be written when the headers are sent or when
			// the session has been destroyed
			return FALSE;
		}

		// Set the last active timestamp
		$this->_data['last_active'] = time();

		try
		{
			return $this->_write();
		}
		catch (Exception $e)
		{
			// Log & ignore all errors when a write fails
			Kohana::$log->add(Log::ERROR, Kohana_Exception::text($e))->write();

			return FALSE;
		}
	}

	/**
	 * Completely destroy the current session.
	 *
	 *     $success = $session->destroy();
	 *
	 * @return  boolean
	 */
	public function destroy()
	{
		if ($this->_destroyed === FALSE)
		{
			if ($this->_destroyed = $this->_destroy())
			{
				// The session has been destroyed, clear all data
				$this->_data = array();
			}
		}

		return $this->_destroyed;
	}

	/**
	 * Restart the session.
	 *
	 *     $success = $session->restart();
	 *
	 * @return  boolean
	 */
	public function restart()
	{
		if ($this->_destroyed === FALSE)
		{
			// Wipe out the current session.
			$this->destroy();
		}

		// Allow the new session to be saved
		$this->_destroyed = FALSE;

		return $this->_restart();
	}

	/**
	 * Serializes the session data.
	 *
	 * @param   array  $data  data
	 * @return  string
	 */
	protected function _serialize($data)
	{
		return serialize($data);
	}

	/**
	 * Unserializes the session data.
	 *
	 * @param   string  $data  data
	 * @return  array
	 */
	protected function _unserialize($data)
	{
		return unserialize($data);
	}

	/**
	 * Encodes the session data using [base64_encode].
	 *
	 * @param   string  $data  data
	 * @return  string
	 */
	protected function _encode($data)
	{
		return base64_encode($data);
	}

	/**
	 * Decodes the session data using [base64_decode].
	 *
	 * @param   string  $data  data
	 * @return  string
	 */
	protected function _decode($data)
	{
		return base64_decode($data);
	}

	/**
	 * @return  string
	 */
	public function id()
	{
		return session_id();
	}

	/**
	 * @param   string  $id  session id
	 * @return  null
	 */
	protected function _read($id = NULL)
	{		
		// Sync up the session cookie with Cookie parameters
		session_set_cookie_params($this->lifetime, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);

		// Do not allow PHP to send Cache-Control headers
		session_cache_limiter(FALSE);

		// Set the session cookie name
		session_name($this->_name);

		if ($id)
		{
			// Set the session id
			session_id($id);
		}

		// Start the session
		session_start();

		// Use the $_SESSION global for storing data
		$this->_data =& $_SESSION;

		return NULL;
	}

	/**
	 * @return  string
	 */
	protected function _regenerate()
	{
		// Regenerate the session id
		session_regenerate_id();

		return session_id();
	}

	/**
	 * @return  bool
	 */
	protected function _write()
	{
		// Write and close the session
		session_write_close();

		return TRUE;
	}

	/**
	 * @return  bool
	 */
	protected function _restart()
	{
		// Fire up a new session
		$status = session_start();

		// Use the $_SESSION global for storing data
		$this->_data =& $_SESSION;

		return $status;
	}

	/**
	 * @return  bool
	 */
	protected function _destroy()
	{
		// Destroy the current session
		session_destroy();

		// Did destruction work?
		$status = ! session_id();

		if ($status)
		{
			// Make sure the session cannot be restarted
			Cookie::delete($this->_name);
		}

		return $status;
	}


}
