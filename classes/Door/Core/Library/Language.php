<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core\Library;

/**
 * Description of Lang
 *
 * @author serginho
 */
class Language extends \Door\Core\Library {
	
	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	protected $lang = 'en-us';

	/**
	 * @var  string  source language: en-us, es-es, zh-cn, etc
	 */
	protected $source = 'en-us';

	/**
	 * @var  array  cache of loaded languages
	 */
	protected $cache = array();
	
	protected $paths = array();

	/**
	 * Get and set the target language.
	 *
	 *     // Get the current language
	 *     $lang = $this->lang();
	 *
	 *     // Change the current language to Spanish
	 *     $this->lang('en-us');
	 *
	 * @param   string  $lang   new language setting
	 * @return  string
	 * @since   3.0.2
	 */
	public function lang($lang = NULL)
	{
		if ($lang)
		{
			// Normalize the language
			$this->lang = strtolower(str_replace(array(' ', '_'), '-', $lang));
		}

		return $this->lang;
	}
	
	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned. No parameters are replaced.
	 *
	 *     $hello = $this->get('Hello friends, my name is :name');
	 *
	 * @param   string  $string text to translate
	 * @param   string  $lang   target language
	 * @return  string
	 */
	public function get($string, $lang = NULL)
	{
		if ( ! $lang)
		{
			// Use the global target language
			$lang = $this->lang;
		}

		// Load the translation table for this language
		$table = $this->load($lang);

		// Return the translated string if it exists
		return isset($table[$string]) ? $table[$string] : $string;
	}
	
	/**
	 * Add directory with translations
	 * 
	 * ex. $app->lang->add_path(__DIR__."/modules/my_module/lang");
	 * 
	 * @param string $path path to directory with translations
	 * @return \Door\Core\Library\Lang
	 */
	public function add_path($path)
	{
		$this->paths[] = $path;
		
		return $this;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 *     // Get all defined Spanish messages
	 *     $messages = $this->load('es-es');
	 *
	 * @param   string  $lang   language to load
	 * @return  array
	 */
	protected function load($lang)
	{
		if (isset($this->_cache[$lang]))
		{
			return $this->_cache[$lang];
		}

		// New translation table
		$table = array();

		// Split the language: language, region, locale, etc
		$parts = explode('-', $lang);

		do
		{
			// Create a path for this set of parts
			$lang_path = implode(DIRECTORY_SEPARATOR, $parts);
			
			$t = array();
			
			foreach($this->paths as $path)
			{
				$filename = $path."/".$lang_path.".php";
				
				if(file_exists($filename))
				{
					$t = array_merge($t, self::load($filename));
				}
			}
			
			// Append the sub table, preventing less specific language
			// files from overloading more specific files
			$table += $t;

			// Remove the last part
			array_pop($parts);
		}
		while ($parts);

		// Cache the translation table locally
		return $this->_cache[$lang] = $table;
	}
	
	protected static function load_file($file)
	{
		return include $file;
	}

}
