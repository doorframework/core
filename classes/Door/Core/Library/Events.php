<?php defined('SYSPATH') or die('No direct script access.');

namespace Door\Core\Library;

/**
 * Process queuing/execution class. Allows an unlimited number of callbacks
 * to be added to 'events'. Events can be run multiple time, and can also
 * process event-specific data.
 * 
 * This library has been ported from Kohana 2.x purely for handling custom
 * event stacks. Kohana 3.x does not have a hooks system so all events must
 * be implemented.
 *
 * @package    Kohana
 * @category   Cache
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Events extends \Door\Core\Library{

	/**
	 * @var  array containing events and callbacks
	 */
	protected $events = array();

	/**
	 * @var  array containing events that have run
	 */
	protected $has_run = array();

	/**
	 * Add a callback to the event stack
	 *
	 * @param   string   name 
	 * @param   array    callback  (http://php.net/callback)
	 * @param   boolean  unique prevents duplicate events
	 * @return  boolean
	 */
	public function add($name, array $callback, $unique = FALSE)
	{
		if ( ! isset($this->events[$name]))
		{
			// Create an empty event for undefined events
			$this->events[$name] = array();
		}
		elseif ($unique and in_array($callback, $this->events[$name], TRUE))
		{
			// Event already exists
			return FALSE;
		}

		// Add the event
		$this->events[$name][] = $callback;

		return TRUE;
	}

	/**
	 * Add a callback to an event queue, before a given event.
	 *
	 * @param   string   name 
	 * @param   array    existing 
	 * @param   array    callback 
	 * @return  boolean
	 */
	public function add_before($name, array $existing, array $callback)
	{
		if (empty($this->events[$name]) or FALSE === ($key = array_search($existing, $this->events[$name], TRUE)))
		{
			// No need to insert, just add
			return $this->add($name, $callback);
		}

		// Insert the event immediately before the existing event
		return $this->insert_event($name, $key, $callback);
	}

	/**
	 * Add a callback to an event queue, after a given event.
	 *
	 * @param   string   name 
	 * @param   array    existing 
	 * @param   array    callback 
	 * @return  boolean
	 */
	public function add_after($name, array $existing, array $callback)
	{
		if (empty($this->events[$name]) or FALSE === ($key = array_search($existing, $this->events[$name], TRUE)))
		{
			// No need to insert, just add
			return $this->add($name, $callback);
		}

		// Insert the event immediately after the existing event
		return $this->insert_event($name, $key++ , $callback);
	}

	/**
	 * Replaces an event with another event.
	 *
	 * @param   string   name 
	 * @param   array    existing 
	 * @param   array    callback 
	 * @return  boolean
	 */
	public function replace($name, array $existing, array $callback)
	{
		if (empty($this->events[$name]) or FALSE === ($key = array_search($existing, $this->events[$name], TRUE)))
			return FALSE;

		if ( ! in_array($callback, $this->events[$name], TRUE))
		{
			$this->events[$name][$key] = $callback;
			return TRUE;
		}

		// Remove event from the stack
		unset($this->events[$name][$key]);

		// Reset the array to preserve ordering
		$this->events[$name] = array_values($this->events[$name]);

		return TRUE;
	}

	/**
	 * Get all callbacks for an event.
	 *
	 * @param   string   name 
	 * @return  array
	 */
	public function get($name)
	{
		return empty($this->events[$name]) ? array() : $this->events[$name];
	}

	/**
	 * Clear some or all callbacks from an event.
	 *
	 * @param   string   name 
	 * @param   array    callback 
	 * @return  void
	 */
	public function clear($name = NULL, array $callback = NULL)
	{
		if (NULL === $name and NULL === $callback)
		{
			// Clear all events
			$this->events = array();
			return;
		}

		if (NULL === $callback)
		{
			// Clear named events
			$this->events[$name] = array();
			return;
		}

		// If the name does not exist or the callback cannot be found, return
		if ( ! isset($this->events[$name]) or FALSE === ($key = array_search($callback, $this->events[$name], TRUE)))
			return;

		// Unset the callback
		unset($this->events[$name][$key]);

		// Reset the array to preserve ordering
		$this->events[$name] = array_values($this->events[$name]);

		return;
	}

	/**
	 * Execute all of the callbacks attached to an event.
	 *
	 * @param   string   name 
	 * @param   mixed    data 
	 * @return  void
	 */
	public function run($name, & $data = NULL)
	{
		// Event has been run
		$this->has_run[$name] = TRUE;

		if (empty($this->events[$name]))
			return;

		$this->$data = & $data;
		$callbacks = $this->get($name);

		foreach ($callbacks as $callback)
			call_user_func_array($callback, array(&$data));

		$clear_data = NULL;
		$this->$data = & $clear_data;

		return;
	}

	/**
	 * Check if an event has run
	 *
	 * @param   string   name 
	 * @return  boolean
	 */
	public function has_run($name)
	{
		return isset($this->has_run[$name]);
	}

	/**
	 * Inserts a new event at a specfic key location.
	 *
	 * @param   string   name 
	 * @param   string   key 
	 * @param   array    callback 
	 * @return  boolean
	 */
	protected function insert_event($name, $key, array $callback)
	{
		if (in_array($callback, $this->events[$name], TRUE))
			return FALSE;

		$this->events[$name] = array_merge(
			array_slice($this->events[$name], 0, $key),
			array($callback),
			array_slice($this->events[$name], $key)
		);

		return TRUE;
	}

}
