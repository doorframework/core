<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Library;
/**
 * Description of Auth
 *
 * @author serginho
 */
class Auth extends \Door\Core\Library {
	
	public $session_key = 'session';
	
	public $hash_key = null;
	
	public $hash_method = "md5";
	
	public $lifetime = 0;

	/**
	 * Checks if a session is active.
	 *
	 * @param   mixed    $role Role name string, role ORM object, or array with role names
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		// Get the user from the session
		$user = $this->get_user();

		if ( ! $user)
			return FALSE;

		if ($user instanceof \Door\Core\Model\User AND $user->loaded())
		{
			// If we don't have a roll no further checking is needed
			if ( ! $role)
				return TRUE;

			if (is_array($role))
			{
				// Get all the roles
				$roles = $this->app->models->factory('Role')
							->where('_id', 'IN', $role)
							->find_all()
							->as_array(NULL, '_id');

				// Make sure all the roles are valid ones
				if (count($roles) !== count($role))
					return FALSE;
			}
			else
			{
				if ( ! is_object($role))
				{
					// Load the role
					$this->app->models->factory('Role', $role);

					if ( ! $roles->loaded())
						return FALSE;
				}
				else
				{
					$roles = $role;
				}
			}

			return $user->has('roles', $roles);
		}
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   $username
	 * @param   string   $password
	 * @param   boolean  $remember  enable autologin
	 * @return  boolean
	 */
	protected function _login($user, $password, $remember)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = $this->app->models->factory("User");
			$user->where($user->unique_key($username), '=', $username)->find();
		}
		if (is_string($password))
		{
			// Create a hashed password
			$password = $this->hash($password);
		}

		// If the passwords match, perform a login
		if ($user->has('roles', $this->app->models->factory('Role', 'login')) AND $user->password === $password)
		{
			if ($remember === TRUE)
			{
				// Token data
				$data = array(
					'user_id'    => $user->pk(),
					'expires'    => time() + $this->lifetime
				);

				// Create a new autologin token
				$token = $this->app->models->factory('User_Token')
							->values($data)
							->create();

				// Set the autologin cookie
				$this->app->cookie->set('authautologin', $token->token);
			}

			// Finish the login
			$this->complete_login($user);

			return TRUE;
		}

		// Login failed
		return FALSE;
	}

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    $user                    username string, or user ORM object
	 * @param   boolean  $mark_session_as_forced  mark the session as forced
	 * @return  boolean
	 */
	public function force_login($user, $mark_session_as_forced = FALSE)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = $this->app->models->factory('User');
			$user->where($user->unique_key($username), '=', $username)->find();
		}

		if ($mark_session_as_forced === TRUE)
		{
			// Mark the session as forced, to prevent users from changing account information
			$this->app->session->set('auth_forced', TRUE);
		}

		// Run the standard completion
		$this->complete_login($user);
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  mixed
	 */
	public function auto_login()
	{
		if ($token = $this->app->cookie->get('authautologin'))
		{
			// Load the token and user
			$token = $this->app->models->factory('User_Token', array('token' => $token));

			if ($token->loaded() AND $token->user->loaded())
			{
				// Save the token to create a new unique token
				$token->save();

				// Set the new token
				$this->app->cookie->set('authautologin', $token->token, $token->expires - time());

				// Complete the login with the found data
				$this->complete_login($token->user);

				// Automatic login was successful
				return $token->user;
			}
		}

		return FALSE;
	}

	/**
	 * Gets the currently logged in user from the session (with auto_login check).
	 * Returns $default if no user is currently logged in.
	 *
	 * @param   mixed    $default to return in case user isn't logged in
	 * @return  mixed
	 */
	public function get_user($default = NULL)
	{
		$user = $this->app->session->get($this->session_key, $default);

		if ($user === $default)
		{
			// check for "remembered" login
			if (($user = $this->auto_login()) === FALSE)
				return $default;
		}

		return $user;
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   $user  username string, or user ORM object
	 * @return  string
	 */
	public function password($user)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = $this->app->models->factory('User');
			$user->where($user->unique_key($username), '=', $username)->find();
		}

		return $user->password;
	}


	/**
	 * Compare password with original (hashed). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$user = $this->get_user();

		if ( ! $user)
			return FALSE;

		return ($this->hash($password) === $user->password);
	}	

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   $username  Username to log in
	 * @param   string   $password  Password to check against
	 * @param   boolean  $remember  Enable autologin
	 * @return  boolean
	 */
	public function login($username, $password, $remember = FALSE)
	{
		if (empty($password))
			return FALSE;

		return $this->_login($username, $password, $remember);
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  $destroy     Completely destroy the session
	 * @param   boolean  $logout_all  Remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		// Set by force_login()
		$this->app->session->delete('auth_forced');

		if ($token = $this->app->cookie->get('authautologin'))
		{
			// Delete the autologin cookie to prevent re-login
			$this->app->cookie->delete('authautologin');

			// Clear the autologin token from the database
			$token = $this->app->models->factory('User_Token', array('token' => $token));

			if ($token->loaded() AND $logout_all)
			{
				// Delete all user tokens. This isn't the most elegant solution but does the job
				$tokens = $this->app->models->factory('User_Token')->where('user_id','=',$token->user_id)->find_all();
				
				foreach ($tokens as $_token)
				{
					$_token->delete();
				}
			}
			elseif ($token->loaded())
			{
				$token->delete();
			}
		}		
		
		if ($destroy === TRUE)
		{
			// Destroy the session completely
			$this->app->session->destroy();
		}
		else
		{
			// Remove the user from the session
			$this->app->session->delete($this->session_key);

			// Regenerate session_id
			$this->app->session->regenerate();
		}

		// Double check
		return ! $this->logged_in();
	}

	/**
	 * Creates a hashed hmac password from a plaintext password. This
	 * method is deprecated, [Auth::hash] should be used instead.
	 *
	 * @deprecated
	 * @param  string  $password Plaintext password
	 */
	public function hash_password($password)
	{
		return $this->hash($password);
	}

	/**
	 * Perform a hmac hash, using the configured method.
	 *
	 * @param   string  $str  string to hash
	 * @return  string
	 */
	public function hash($str)
	{
		if( ! isset($this->hash_key))
		{
			throw new Kohana_Exception('A valid hash key must be set in your auth.');
		}
			

		return hash_hmac($this->hash_method, $str, $this->hash_key);
	}

	protected function complete_login($user)
	{
		$user->complete_login();		
		// Regenerate session_id
		$this->app->session->regenerate();

		// Store username in session
		$this->app->session->set($this->session_key, $user);

		return TRUE;
	}
	
	
	
}
