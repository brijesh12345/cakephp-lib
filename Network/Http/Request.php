<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 3.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Network\Http;

use Cake\Error;

/**
 * Implements methods for HTTP requests.
 *
 * Used by Cake\Network\Http\Client to contain request information
 * for making requests.
 */
class Request {

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const METHOD_PATCH = 'PATCH';
/**
 * HTTP Version being used.
 *
 * @var string
 */
	protected $_version = '1.1';

	protected $_method;

	protected $_content;

	protected $_url;

	protected $_cookies = [];

/**
 * Headers to be sent.
 *
 * @var array
 */
	protected $_headers = [
		'Connection' => 'close',
		'User-Agent' => 'CakePHP'
	];

/**
 * Get/Set the HTTP method.
 *
 * @param string|null $method The method for the request.
 * @return mixed Either this or the current method.
 * @throws Cake\Error\Exception On invalid methods.
 */
	public function method($method = null) {
		if ($method === null) {
			return $this->_method;
		}
		$name = __CLASS__ . '::METHOD_' . strtoupper($method);
		if (!defined($name)) {
			throw new Error\Exception(__d('cake_dev', 'Invalid method type'));
		}
		$this->_method = $method;
		return $this;
	}

/**
 * Get/Set the url for the request.
 *
 * @param string|null $url The url for the request. Leave null for get
 * @return mixed Either $this or the url value.
 */
	public function url($url = null) {
		if ($url === null) {
			return $this->_url;
		}
		$this->_url = $url;
		return $this;
	}

/**
 * Get/Set headers into the request.
 *
 * You can get the value of a header, or set one/many headers.
 * Headers are set / fetched in a case insensitive way.
 *
 * ### Getting headers
 *
 * `$request->header('Content-Type');`
 *
 * ### Setting one header
 *
 * `$request->header('Content-Type', 'application/json');`
 *
 * ### Setting multiple headers
 *
 * `$request->header(['Connection' => 'close', 'User-Agent' => 'CakePHP']);`
 *
 * @param string|array $name The name to get, or array of multiple values to set.
 * @param string $value The value to set for the header.
 * @return mixed Either $this when setting or header value when getting.
 */
	public function header($name = null, $value = null) {
		if ($value === null && is_string($name)) {
			$name = $this->_normalizeHeader($name);
			return isset($this->_headers[$name]) ? $this->_headers[$name] : null;
		}
		if ($value !== null && !is_array($name)) {
			$name = [$name => $value];
		}
		foreach ($name as $key => $val) {
			$key = $this->_normalizeHeader($key);
			$this->_headers[$key] = $val;
		}
		return $this;
	}

/**
 * Get all headers
 *
 * @return array
 */
	public function headers() {
		return $this->_headers;
	}

/**
 * Normalize header names to Camel-Case form.
 *
 * @param string $name The header name to normalize.
 * @return string Normalized header name.
 */
	protected function _normalizeHeader($name) {
		$parts = explode('-', $name);
		$parts = array_map('strtolower', $parts);
		$parts = array_map('ucfirst', $parts);
		return implode('-', $parts);
	}

/**
 * Get/set the content or body for the request.
 *
 * @param string|null $content The content for the request. Leave null for get
 * @return mixed Either $this or the content value.
 */
	public function content($content = null) {
		if ($content === null) {
			return $this->_content;
		}
		$this->_content = $content;
		return $this;
	}

/**
 * Get/Set cookie values.
 *
 * ### Getting a cookie
 *
 * `$request->cookie('session');`
 *
 * ### Setting one cookie
 *
 * `$request->cookie('session', '123456');`
 *
 * ### Setting multiple headers
 *
 * `$request->cookie(['test' => 'value', 'split' => 'banana']);`
 *
 * @param string $name The name of the cookie to get/set
 * @param string|null $value Either the value or null when getting values.
 * @return mixed Either $this or the cookie value.
 */
	public function cookie($name, $value = null) {
		if ($value === null && is_string($name)) {
			return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
		}
		if (is_string($name) && is_string($value)) {
			$name = [$name => $value];
		}
		foreach ($name as $key => $val) {
			$this->_cookies[$key] = $val;
		}
		return $this;
	}

/**
 * Get all cookies
 *
 * @return array
 */
	public function cookies() {
		return $this->_cookies;
	}

	public function version() {
		return $this->_version;
	}

}
