<?php

namespace Door\Core\Controller;
use Door\Core\Controller;
use Door\Core\View;
use Door\Core\Helper\HTML;

/**
 * Layout controller. Can be only one layout controller for request.
 *
 * @author serginho
 */
abstract class Layout extends Controller{
	
	
	protected $title = null;
	protected $description = null;
	protected $keywords = null;
	
	public static $compile_scripts = false;
	
    const JS_EXT = "js";
    const CSS_EXT = "css";		
	
	/**
	 * @var Model
	 */
	protected $model;
	
	/**
	 * layout filename
	 * @var string
	 */
	protected $layout_name = "layout";
	
    /**
     * @var array
     */
    private $scripts = array();

    /**
     * @var array
     */
    private $styles = array();

    /**
     * Дополнительные теги, добавленые с помощью функции add_tag
     * @var array
     */
    private $additional_tags = array();	
	
	/**
	 * @var bool
	 */	
	protected $show_layout = true;
	
	/**
	 *
	 * @var View
	 */
	protected $layout = null;
	
	public function init(){
		
		if($this->show_layout)
		{
			$this->layout = $this->app->views->get($this->layout_name);
		}
		
		parent::init();
	}
	
	public function execute() {		
		if($this->show_layout)
		{			
			$this->layout->content = $this->response->body();
			$this->layout->headers = $this->render_headers();
			$this->response->body($this->layout);
		}				
	}		
	
	protected function render_headers()
	{
		$return_value = "";
				
		if($this->title != null)
		{
			$return_value .= "<title>".htmlspecialchars($this->title)."</title>\n";
		}
		
		if($this->description != null)
		{
			$return_value .= "<meta name='description' content='".htmlspecialchars($this->description)."'/>\n";
		}
		
		if($this->keywords != null)
		{
			$return_value .= "<meta name='keywords' content='".htmlspecialchars($this->keywords)."'/>\n";
		}
		
		$scripts = $this->get_scripts_uris($this->scripts, self::JS_EXT);
		foreach($scripts as $script)
		{
			$return_value .= $this->app->html->script($script)."\n";
		}	
		
		$styles = $this->get_scripts_uris($this->styles, self::CSS_EXT);
		foreach($styles as $style)
		{
			$return_value .= $this->app->html->style($style)."\n";
		}		
		
		$data = array(
			'layout' => $this,
			'headers' => &$return_value
		);
		Event::run("Controller_Layout.render_headers", $data);
		
		return $return_value;
	}
	
	protected function get_scripts_uris($scripts, $ext)
	{
		$uris = array();
		if(self::$compile_scripts)
		{
			$compiling_scripts = array();
			$no_compress_scripts = array();
			foreach($scripts as $script_config)
			{
				if($script_config['compile'] == true)
				{
					$compiling_scripts[] = $script_config['file'];
				}
				elseif($script_config['outer'] == true)
				{
					$uris[] = $script_config['file'];
				}
				else
				{
					$compiling_scripts[] = $script_config['file'];
					$no_compress_scripts[] = $script_config['file'];
				}
			}
			if(count($compiling_scripts) > 0)
			{
				$uris[] = Door::path_to_uri($this->compile_media($compiling_scripts, $ext, $no_compress_scripts));
			}
		}
		else
		{
			foreach($scripts as $script_config)
			{
				if($script_config['outer'] == true)
				{
					$uris[] = $script_config['file'];
				}				
				else
				{
					$uris[] = "media/".$script_config['file'].".".$ext;
				}
			}		
		}
		
		return $uris;
	}
	
	protected function add_outer_script($url)
	{
		$this->scripts[] = array(
			'file' => $url,
			'compile' => false,
			'outer' => true
		);		
	}
	
	protected function add_script($file, $compile = true)
	{
		$this->scripts[] = array(
			'file' => $file,
			'compile' => $compile,
			'outer' => false
		);
	}
	
	protected function add_outer_style($url)
	{
		$this->styles[] = array(
			'file' => $url,
			'compile' => false,
			'outer' => true
		);		
	}
	
	protected function add_style($file, $compile = true)
	{
		$this->styles[] = array(
			'file' => $file,
			'compile' => $compile,
			'outer' => false
		);
	}	
	
    protected function add_header_tag($name, array $attributes = null, $content = null)
    {
		$this->additional_tags[] = array(
			'name' => $name,
			'attributes' => $attributes,
			'content' => $content
		);
    }
	
	/**
	 * Компилирует медиа файлы
	 * @todo сделать возможность встройки картинок base64
	 * @param array $files
	 * @param type $extension
	 * @return boolean|string
	 * @throws Kohana_Exception 
	 */
	protected function compile_media(array $files, $extension, $no_compress_scripts = array())
	{
		if(count($files) == 0) return false;
		
		$files_sum = $files;
		sort($files_sum);
		
		
		$sum = crc32(implode($files_sum));
		unset($files_sum);
		$uri = "media/{$sum}.{$extension}";
		$minified_file = DOCROOT.$uri;	

		if( ! file_exists($minified_file))
		{
			
			$files_paths = array();

			foreach($files as $file)
			{
				$file_path = Kohana::find_file("media", $file, $extension);

				if($file_path === false)
				{
					if(file_exists($file))
					{
						$file_path = $file;
					} 
					else
					{
						throw new Kohana_Exception("media file '{file}.{extension}' not founded",
								array("{file}" => $file,"{extension}" => $extension));
					}
				}

				$files_paths[] = array(
					'file' => $file,
					'path' => $file_path
				);
			}			
			
			$sources = array();

			foreach($files_paths as $file_path_data)
			{
				$file_path = $file_path_data['path'];
				$file1 = $file_path_data['file'];
				if(file_exists($file_path))
				{
					if(array_search($file1, $no_compress_scripts) === false)
					{
						if($extension == self::JS_EXT)
						{
							$sources[] = "\n\n/*$file_path*/\n\n".JSMin::minify(file_get_contents($file_path));
						}
						else
						{
							$minified_css = CssMin::minify(file_get_contents($file_path));
							//Надо заменить относительные пути в css
							$dir_url = Door::path_to_uri(dirname($file_path));
							$dir_path = dirname($file_path);
							/*if(preg_match_all("/url\\(([^\\)]+)\\)/", $minified_css, $results))
							{
								for($i = 0; $i < count($results[0]); $i++)
								{
									$url = str_replace(array('"',"'"),"",$results[1][$i]);
									if(strpos($url, "data:") === false)
									{
										$from = $results[0][$i];
										$to = "url(/".$dir_url."/".$url.")";
										$minified_css = str_replace($from, $to, $minified_css);
									}
								}
							}*/
							if(preg_match_all("/url\\(([^\\)]+)\\)/", $minified_css, $results))
							{
								for($i = 0; $i < count($results[0]); $i++)
								{
									$url = str_replace(array('"',"'"),"",$results[1][$i]);

									if(strpos($url, "data:") === false && strpos($url, "http:") === false)
									{
										$path_to_media_file = $dir_path."/".$url;
										if(!file_exists($path_to_media_file))
										{
											continue;
										}
										$size = null;
										$from = $results[0][$i];
										$to = "";

										try
										{
											$size = getimagesize($path_to_media_file);
										}
										catch(Exception $e){}											

										if($size !== false && filesize($path_to_media_file) < 5120) //5kb
										{
											$to = "url(\"data:{$size['mime']};base64,".base64_encode(file_get_contents($path_to_media_file))."\")";											
										}
										else
										{
											$to = "url(/".$dir_url."/".$url.")";
										}

										$minified_css = str_replace($from, $to, $minified_css);
									}
								}
							}						
							while(preg_match_all("#url\\([^\\)]*/([^/]+)/\\.\\./#", $minified_css, $results))
							{
								for($i = 0; $i < count($results[0]); $i++)
								{
									$minified_css = str_replace("/".$results[1][$i]."/../", "/", $minified_css);
								}
							}
							$sources[] = "\n\n".$minified_css;
						}
					}
					else
					{
						$sources[] = file_get_contents($file_path); 
					}
				}
				else
				{
					throw new Kohana_Exception("file '{file}' not founded",
							array("{file}" => $file_path));
				}
			}
			$minified = implode("", $sources);
			unset($sources);
			file_put_contents($minified_file, $minified);
		}	
		
		return $minified_file;
	}	
	
	protected function set_model(Model $model)
	{
		$this->model = $model;
		
		if(!empty($model->descrption))
		{
			$this->description = $model->description;
		}
		
		if(!empty($model->title))
		{
			$this->title = $model->title;
		}
		elseif(!empty($model->name))
		{
			$this->title = $model->name;
		}
		
		if(!empty($model->keywords))
		{
			$this->keywords = $model->keywords;
		}
	}
	
}
