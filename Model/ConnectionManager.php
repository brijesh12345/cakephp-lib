<?php
/**
 * Datasource connection manager
 *
 * Provides an interface for loading and enumerating connections defined in app/config/database.php
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.model
 * @since         CakePHP(tm) v 0.10.x.1402
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('DataSource', 'Model/Datasource');

/**
 * Manages loaded instances of DataSource objects
 *
 * @package       cake
 * @subpackage    cake.cake.libs.model
 */
class ConnectionManager {

/**
 * Holds a loaded instance of the Connections object
 *
 * @var DATABASE_CONFIG
 * @access public
 */
	public $config = null;

/**
 * Holds instances DataSource objects
 *
 * @var array
 * @access protected
 */
	protected $_dataSources = array();

/**
 * Contains a list of all file and class names used in Connection settings
 *
 * @var array
 * @access protected
 */
	protected $_connectionsEnum = array();

/**
 * Constructor.
 *
 */
	function __construct() {
		include_once CONFIGS . 'database.php';
		if (class_exists('DATABASE_CONFIG')) {
			$this->config = new DATABASE_CONFIG();
			$this->_getConnectionObjects();
		}
	}

/**
 * Gets a reference to the ConnectionManger object instance
 *
 * @return object Instance
 */
	public static function &getInstance() {
		static $instance = array();

		if (!$instance) {
			$instance[0] = new ConnectionManager();
		}

		return $instance[0];
	}

/**
 * Gets a reference to a DataSource object
 *
 * @param string $name The name of the DataSource, as defined in app/config/database.php
 * @return object Instance
 */
	public static function &getDataSource($name) {
		$_this = ConnectionManager::getInstance();

		if (!empty($_this->_dataSources[$name])) {
			$return = $_this->_dataSources[$name];
			return $return;
		}

		if (empty($_this->_connectionsEnum[$name])) {
			trigger_error(__("ConnectionManager::getDataSource - Non-existent data source %s", $name), E_USER_ERROR);
			$null = null;
			return $null;
		}
		$conn = $_this->_connectionsEnum[$name];
		$class = $conn['classname'];

		if ($_this->loadDataSource($name) === null) {
			trigger_error(__("ConnectionManager::getDataSource - Could not load class %s", $class), E_USER_ERROR);
			$null = null;
			return $null;
		}
		$_this->_dataSources[$name] = new $class($_this->config->{$name});
		$_this->_dataSources[$name]->configKeyName = $name;

		$return = $_this->_dataSources[$name];
		return $return;
	}

/**
 * Gets the list of available DataSource connections
 *
 * @return array List of available connections
 */
	public static function sourceList() {
		$_this = ConnectionManager::getInstance();
		return array_keys($_this->_dataSources);
	}

/**
 * Gets a DataSource name from an object reference.
 *
 * **Warning** this method may cause fatal errors in PHP4.
 *
 * @param object $source DataSource object
 * @return string Datasource name, or null if source is not present
 *    in the ConnectionManager.
 */
	public static function getSourceName(&$source) {
		$_this = ConnectionManager::getInstance();
		foreach ($_this->_dataSources as $name => $ds) {
			if ($ds == $source) {
				return $name;
			}
		}
		return '';
	}

/**
 * Loads the DataSource class for the given connection name
 *
 * @param mixed $connName A string name of the connection, as defined in app/config/database.php,
 *                        or an array containing the filename (without extension) and class name of the object,
 *                        to be found in app/models/datasources/ or cake/libs/model/datasources/.
 * @return boolean True on success, null on failure or false if the class is already loaded
 */
	public static function loadDataSource($connName) {
		$_this = ConnectionManager::getInstance();

		if (is_array($connName)) {
			$conn = $connName;
		} else {
			$conn = $_this->_connectionsEnum[$connName];
		}

		if (class_exists($conn['classname'], false)) {
			return false;
		}

		$plugin = $package = null;
		if (!empty($conn['plugin'])) {
			$plugin .= '.';
		}
		if (!empty($conn['package'])) {
			$package = '/' . $conn['package'];
		}

		App::uses($conn['classname'], $plugin . 'Model/Datasource' . $package);
		if (!class_exists($conn['classname'])) {
			trigger_error(__('ConnectionManager::loadDataSource - Unable to import DataSource class %s', $class), E_USER_ERROR);
			return null;
		}
		return true;
	}

/**
 * Return a list of connections
 *
 * @return array An associative array of elements where the key is the connection name
 *               (as defined in Connections), and the value is an array with keys 'filename' and 'classname'.
 */
	public static function enumConnectionObjects() {
		$_this = ConnectionManager::getInstance();
		return $_this->_connectionsEnum;
	}

/**
 * Dynamically creates a DataSource object at runtime, with the given name and settings
 *
 * @param string $name The DataSource name
 * @param array $config The DataSource configuration settings
 * @return object A reference to the DataSource object, or null if creation failed
 */
	public static function &create($name = '', $config = array()) {
		$_this = ConnectionManager::getInstance();

		if (empty($name) || empty($config) || array_key_exists($name, $_this->_connectionsEnum)) {
			$null = null;
			return $null;
		}
		$_this->config->{$name} = $config;
		$_this->_connectionsEnum[$name] = $_this->__connectionData($config);
		$return = $_this->getDataSource($name);
		return $return;
	}

/**
 * Gets a list of class and file names associated with the user-defined DataSource connections
 *
 * @return void
 */
	protected function _getConnectionObjects() {
		$connections = get_object_vars($this->config);

		if ($connections != null) {
			foreach ($connections as $name => $config) {
				$this->_connectionsEnum[$name] = $this->__connectionData($config);
			}
		} else {
			throw new MissingConnectionException(array('class' => 'ConnectionManager'));
		}
	}

/**
 * Returns the file, class name, and parent for the given driver.
 *
 * @return array An indexed array with: filename, classname, plugin and parent
 */
	private function __connectionData($config) {
		$package = $classname = $plugin = null;

		list($plugin, $classname) = pluginSplit($config['datasource']);
		if (strpos($classname, '/') !== false) {
			$package = dirname($classname);
			$classname = basename($classname);
		}
		return compact('package', 'classname', 'plugin');
	}

/**
 * Destructor.
 *
 */
	function __destruct() {
		if (Configure::read('Session.defaults') == 'database' && function_exists('session_write_close')) {
			session_write_close();
		}
	}
}