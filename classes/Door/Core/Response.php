<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Door\Core;
use Door\Core\Helper\Arr;

/**
 * Description of Response
 *
 * @author serginho
 */
class Response {

	// HTTP status codes and messages
	public static $messages = array(
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',

		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		// 306 is deprecated but reserved
		307 => 'Temporary Redirect',

		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	);

	/**
	 * @var  integer     The response http status
	 */
	protected $status = 200;

	/**
	 * @var  array
	 */
	protected $headers;

	/**
	 * @var  string      The response body
	 */
	protected $body = '';

	/**
	 * @var  array       Cookies to be returned in the response
	 */
	protected $cookies = array();

	/**
	 * @var  string      The response protocol
	 */
	protected $protocol;

	/**
	 * Sets up the response object
	 *
	 * @param   array $config Setup the response object
	 * @return  void
	 */
	public function __construct($config)
	{
		$this->header = new Header;

		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				if ($key == 'header')
				{
					$this->headers($value);
				}
				else
				{
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * Outputs the body when cast to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->_body;
	}

	/**
	 * Gets or sets the body of the response
	 *
	 * @return  mixed
	 */
	public function body($content = NULL)
	{
		if ($content === NULL)
			return $this->_body;

		$this->_body = (string) $content;
		return $this;
	}

	/**
	 * Gets or sets the HTTP protocol. The standard protocol to use
	 * is `HTTP/1.1`.
	 *
	 * @param   string   $protocol Protocol to set to the request/response
	 * @return  mixed
	 */
	public function protocol($protocol = NULL)
	{
		if ($protocol)
		{
			$this->_protocol = strtoupper($protocol);
			return $this;
		}

		if ($this->_protocol === NULL)
		{
			$this->_protocol = HTTP::$protocol;
		}

		return $this->_protocol;
	}

	/**
	 * Sets or gets the HTTP status from this response.
	 *
	 *      // Set the HTTP status to 404 Not Found
	 *      $response = Response::factory()
	 *              ->status(404);
	 *
	 *      // Get the current status
	 *      $status = $response->status();
	 *
	 * @param   integer  $status Status to set to this response
	 * @return  mixed
	 */
	public function status($status = NULL)
	{
		if ($status === NULL)
		{
			return $this->_status;
		}
		elseif (array_key_exists($status, Response::$messages))
		{
			$this->_status = (int) $status;
			return $this;
		}
		else
		{
			throw new Kohana_Exception(__METHOD__.' unknown status value : :value', array(':value' => $status));
		}
	}

	/**
	 * Gets and sets headers to the [Response], allowing chaining
	 * of response methods. If chaining isn't required, direct
	 * access to the property should be used instead.
	 *
	 *       // Get a header
	 *       $accept = $response->headers('Content-Type');
	 *
	 *       // Set a header
	 *       $response->headers('Content-Type', 'text/html');
	 *
	 *       // Get all headers
	 *       $headers = $response->headers();
	 *
	 *       // Set multiple headers
	 *       $response->headers(array('Content-Type' => 'text/html', 'Cache-Control' => 'no-cache'));
	 *
	 * @param mixed $key
	 * @param string $value
	 * @return mixed
	 */
	public function headers($key = NULL, $value = NULL)
	{
		if($value === null)
		{
			if(is_array($key))
			{
				foreach($key as $k => $value)
				{
					$this->headers($k, $value);
				}
				return $this;
			}			
			else
			{
				return Arr::get($this->headers, $key);
			}
		}

		$this->headers[$key] = $value;
		return $this;
	}

	/**
	 * Returns the length of the body for use with
	 * content header
	 *
	 * @return  integer
	 */
	public function content_length()
	{
		return strlen($this->body());
	}

	/**
	 * Sends the response status and all set headers.
	 *
	 * @param   boolean     $replace    replace existing headers
	 * @param   callback    $callback   function to handle header output
	 * @return  mixed
	 */
	public function send_headers($replace = FALSE, $callback = NULL)
	{
		return $this->_header->send_headers($this, $replace, $callback);
	}

	/**
	 * Send file download as the response. All execution will be halted when
	 * this method is called! Use TRUE for the filename to send the current
	 * response as the file content. The third parameter allows the following
	 * options to be set:
	 *
	 * Type      | Option    | Description                        | Default Value
	 * ----------|-----------|------------------------------------|--------------
	 * `boolean` | inline    | Display inline instead of download | `FALSE`
	 * `string`  | mime_type | Manual mime type                   | Automatic
	 * `boolean` | delete    | Delete the file after sending      | `FALSE`
	 *
	 * Download a file that already exists:
	 *
	 *     $request->send_file('media/packages/kohana.zip');
	 *
	 * Download generated content as a file:
	 *
	 *     $request->response($content);
	 *     $request->send_file(TRUE, $filename);
	 *
	 * [!!] No further processing can be done after this method is called!
	 *
	 * @param   string  $filename   filename with path, or TRUE for the current response
	 * @param   string  $download   downloaded file name
	 * @param   array   $options    additional options
	 * @return  void
	 * @throws  Kohana_Exception
	 * @uses    File::mime_by_ext
	 * @uses    File::mime
	 * @uses    Request::send_headers
	 */
	public function send_file($filename, $download = NULL, array $options = NULL)
	{
		if ( ! empty($options['mime_type']))
		{
			// The mime-type has been manually set
			$mime = $options['mime_type'];
		}

		if ($filename === TRUE)
		{
			if (empty($download))
			{
				throw new Kohana_Exception('Download name must be provided for streaming files');
			}

			// Temporary files will automatically be deleted
			$options['delete'] = FALSE;

			if ( ! isset($mime))
			{
				// Guess the mime using the file extension
				$mime = File::mime_by_ext(strtolower(pathinfo($download, PATHINFO_EXTENSION)));
			}

			// Force the data to be rendered if
			$file_data = (string) $this->_body;

			// Get the content size
			$size = strlen($file_data);

			// Create a temporary file to hold the current response
			$file = tmpfile();

			// Write the current response into the file
			fwrite($file, $file_data);

			// File data is no longer needed
			unset($file_data);
		}
		else
		{
			// Get the complete file path
			$filename = realpath($filename);

			if (empty($download))
			{
				// Use the file name as the download file name
				$download = pathinfo($filename, PATHINFO_BASENAME);
			}

			// Get the file size
			$size = filesize($filename);

			if ( ! isset($mime))
			{
				// Get the mime type from the extension of the download file
				$mime = File::mime_by_ext(pathinfo($download, PATHINFO_EXTENSION));
			}

			// Open the file for reading
			$file = fopen($filename, 'rb');
		}

		if ( ! is_resource($file))
		{
			throw new Kohana_Exception('Could not read file to send: :file', array(
				':file' => $download,
			));
		}

		// Inline or download?
		$disposition = empty($options['inline']) ? 'attachment' : 'inline';

		// Calculate byte range to download.
		list($start, $end) = $this->_calculate_byte_range($size);

		if ( ! empty($options['resumable']))
		{
			if ($start > 0 OR $end < ($size - 1))
			{
				// Partial Content
				$this->_status = 206;
			}

			// Range of bytes being sent
			$this->_header['content-range'] = 'bytes '.$start.'-'.$end.'/'.$size;
			$this->_header['accept-ranges'] = 'bytes';
		}

		// Set the headers for a download
		$this->_header['content-disposition'] = $disposition.'; filename="'.$download.'"';
		$this->_header['content-type']        = $mime;
		$this->_header['content-length']      = (string) (($end - $start) + 1);

		if (Request::user_agent('browser') === 'Internet Explorer')
		{
			// Naturally, IE does not act like a real browser...
			if (Request::$initial->secure())
			{
				// http://support.microsoft.com/kb/316431
				$this->_header['pragma'] = $this->_header['cache-control'] = 'public';
			}

			if (version_compare(Request::user_agent('version'), '8.0', '>='))
			{
				// http://ajaxian.com/archives/ie-8-security
				$this->_header['x-content-type-options'] = 'nosniff';
			}
		}

		// Send all headers now
		$this->send_headers();

		while (ob_get_level())
		{
			// Flush all output buffers
			ob_end_flush();
		}

		// Manually stop execution
		ignore_user_abort(TRUE);

		if ( ! Kohana::$safe_mode)
		{
			// Keep the script running forever
			set_time_limit(0);
		}

		// Send data in 16kb blocks
		$block = 1024 * 16;

		fseek($file, $start);

		while ( ! feof($file) AND ($pos = ftell($file)) <= $end)
		{
			if (connection_aborted())
				break;

			if ($pos + $block > $end)
			{
				// Don't read past the buffer.
				$block = $end - $pos + 1;
			}

			// Output a block of the file
			echo fread($file, $block);

			// Send the data now
			flush();
		}

		// Close the file
		fclose($file);

		if ( ! empty($options['delete']))
		{
			try
			{
				// Attempt to remove the file
				unlink($filename);
			}
			catch (Exception $e)
			{
				// Create a text version of the exception
				$error = Kohana_Exception::text($e);

				if (is_object(Kohana::$log))
				{
					// Add this exception to the log
					Kohana::$log->add(Log::ERROR, $error);

					// Make sure the logs are written
					Kohana::$log->write();
				}

				// Do NOT display the exception, it will corrupt the output!
			}
		}

		// Stop execution
		exit;
	}

	/**
	 * Renders the HTTP_Interaction to a string, producing
	 *
	 *  - Protocol
	 *  - Headers
	 *  - Body
	 *
	 * @return  string
	 */
	public function render()
	{
		if ( ! $this->_header->offsetExists('content-type'))
		{
			// Add the default Content-Type header if required
			$this->_header['content-type'] = Kohana::$content_type.'; charset='.Kohana::$charset;
		}

		// Set the content length
		$this->headers('content-length', (string) $this->content_length());

		// If Kohana expose, set the user-agent
		if (Kohana::$expose)
		{
			$this->headers('user-agent', Kohana::version());
		}

		// Prepare cookies
		if ($this->_cookies)
		{
			if (extension_loaded('http'))
			{
				$this->_header['set-cookie'] = http_build_cookie($this->_cookies);
			}
			else
			{
				$cookies = array();

				// Parse each
				foreach ($this->_cookies as $key => $value)
				{
					$string = $key.'='.$value['value'].'; expires='.date('l, d M Y H:i:s T', $value['expiration']);
					$cookies[] = $string;
				}

				// Create the cookie string
				$this->_header['set-cookie'] = $cookies;
			}
		}

		$output = $this->_protocol.' '.$this->_status.' '.Response::$messages[$this->_status]."\r\n";
		$output .= (string) $this->_header;
		$output .= $this->_body;

		return $output;
	}

	/**
	 * Generate ETag
	 * Generates an ETag from the response ready to be returned
	 *
	 * @throws Request_Exception
	 * @return String Generated ETag
	 */
	public function generate_etag()
	{
	    if ($this->_body === '')
		{
			throw new Request_Exception('No response yet associated with request - cannot auto generate resource ETag');
		}

		// Generate a unique hash for the response
		return '"'.sha1($this->render()).'"';
	}

	/**
	 * Parse the byte ranges from the HTTP_RANGE header used for
	 * resumable downloads.
	 *
	 * @link   http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35
	 * @return array|FALSE
	 */
	protected function _parse_byte_range()
	{
		if ( ! isset($_SERVER['HTTP_RANGE']))
		{
			return FALSE;
		}

		// TODO, speed this up with the use of string functions.
		preg_match_all('/(-?[0-9]++(?:-(?![0-9]++))?)(?:-?([0-9]++))?/', $_SERVER['HTTP_RANGE'], $matches, PREG_SET_ORDER);

		return $matches[0];
	}

	/**
	 * Calculates the byte range to use with send_file. If HTTP_RANGE doesn't
	 * exist then the complete byte range is returned
	 *
	 * @param  integer $size
	 * @return array
	 */
	protected function _calculate_byte_range($size)
	{
		// Defaults to start with when the HTTP_RANGE header doesn't exist.
		$start = 0;
		$end = $size - 1;

		if ($range = $this->_parse_byte_range())
		{
			// We have a byte range from HTTP_RANGE
			$start = $range[1];

			if ($start[0] === '-')
			{
				// A negative value means we start from the end, so -500 would be the
				// last 500 bytes.
				$start = $size - abs($start);
			}

			if (isset($range[2]))
			{
				// Set the end range
				$end = $range[2];
			}
		}

		// Normalize values.
		$start = abs(intval($start));

		// Keep the the end value in bounds and normalize it.
		$end = min(abs(intval($end)), $size - 1);

		// Keep the start in bounds.
		$start = ($end < $start) ? 0 : max($start, 0);

		return array($start, $end);
	}

	
	
}
