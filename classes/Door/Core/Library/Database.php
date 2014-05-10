<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Library;
use \MongoDB;
use \MongoClient;
/**
 * Description of Database
 *
 * @author serginho
 */
class Database extends \Door\Core\Library {
	
	protected $instances = array();
	
	protected $configs = array();
	
	protected $clients = array();
	
	public function set_connection(array $config, $name = 'default')
	{
		$this->configs[$name] = $config;
	}
	
	/**
	 * @param string $name
	 * @return MongoDB
	 * @throws Exception
	 */
	public function instance($name = 'default')
	{
		if( ! isset($this->instances[$name]))
		{
			if( ! isset($this->configs[$name]))
			{
				throw new Exception("config for database '$name' not found");
			}
			
			$config = $this->configs[$name];
			
			$client = new MongoClient($config['server'], Arr::get($config,'options', array()));
			$database = new MongoDB($client, $config['database']);
			
			$this->clients[$name] = $client;
			$this->instances[$name] = $database;			
		}
		
		return $this->instances[$name];
	}
	
	/**
	 * get client
	 * @param string $name
	 * @return MongoClient
	 */
	public function client($name = 'default')
	{
		return $this->app->arr->get($this->clients, $name);
	}
	
	/**
	 * get configuration
	 * @param string $name
	 * @return Array
	 */
	public function config($name = 'default')
	{
		return $this->app->arr->get($this->configs, $name);
	}
	
}
