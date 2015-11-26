<?php
/**
 * Simple INI Configuration Wrapper
 * 
 * @author Daniel �ekan <djdaca@gmail.com>
 * @version 1.0  
 **/
 
class Configuration
{
	
	/**
		@var array $_config
	**/
	private $_config = array();
	
	/**
		@var mixed instance of Configuration or NULL
	**/
	private static $_instance;
	
	/**
		Construct method of Configuration class
		
		@param array $config
	*/
	public function __construct($config = array())
	{
		$this->import($config);
	}
	
	/**
		Easter for get instance of INI section
		
		@method string $name section name
		@param string $var config var
		@param mixed $default default value from config
		@return mixed
	**/
	public function __call($name, $arguments)
	{
		$var = isset($arguments[0])? $arguments[0] : NULL;
		$default = isset($arguments[1])? $arguments[1] : NULL;
		return $this->getSection( $name, $var, $default );
	}
	
	/**
		Easter for static get INI section
		
		@param string $name section name
		@param string $arguments config var
		@param mixed $default default value from config
		@return mixed
	**/
	public static function __callStatic($name, $arguments)
	{
		$var = isset($arguments[0])? $arguments[0] : NULL;
		$default = isset($arguments[1])? $arguments[1] : NULL;
		return self::getInstance()->getSection( $name, $var, $default );
	}
	
	/**
		Get static instance of configuration
		
		@return \Configuration $_instance
	**/
	public static function getInstance($config = array())
	{
		if( self::$_instance === NULL ) {
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}
	
	/**
		Export config
		
		@return array
	**/
	public function export()
	{
		return $this->_config;
	}
	
	/**
		Import config
		
		@param $config
		@return void
	**/
	public function import($config)
	{
		$this->_config = $config;
	}
	
	/**
		Set INI config file
		
		@param string $file
		@return void
	**/
	public function addConfigFile($file)
	{
		if( file_exists($file) )
		{
			$array = parse_ini_file($file, true);
			$section = array();
			foreach($array as $key => $row) {
				$section[$key] = $this->_ini2extend($row);
			}
			$this->_config = array_merge($this->_config, $section);
			return true;
		}
		return false;
	}
	
	/**
		Get INI section
		
		@param string $name section name
		@param string $var config var
		@return mixed
	**/
	public function getSection($name, $var = NULL, $default = NULL)
	{
		if (isset( $this->_config[$name] ) )
		{
			$section = $this->_config[$name];
			if( $var ) {
				return  isset( $section[$var] )? $section[$var] : $default;
			}
			if( is_array($default) && $default ) {
				$section = array_merge($default, $section);
			}
			return $section;
		} elseif( strpos($name, ':') !== false )
		{
			// možný alias
			list($alias, $type) = explode(':', $name);
			foreach( array_keys($this->_config) as $key ) {
				if( preg_match('/^'.preg_quote($alias).':(.*)$/', $key, $result) ) {
					return $this->getSection($result[1].':'.$type, $var, $default);
				}
			}
		}
		return $default;
	}
	
	/**
		Set configuration section to array
		
		@param string $var config var
		@param string $value config value
		@param string $section config section
		@return void
	**/
	public function set($var, $value, $section = NULL)
	{
		if( $section && isset( $this->_config[$section] ) )
		{
			$this->_config[$section][$var] = $value;
		} else {
			$this->_config[$var] = $value;
		}
	}
	
	/**
		Write configuration array to file
		
		@param string $var config var
		@param string $value config value
		@param string $section config section
		@return void
	**/
	public function save($file)
	{
		if (!defined('PHP_EOL')) {
			switch (strtoupper(substr(PHP_OS, 0, 3))) {
				// Windows
				case 'WIN':
					define('PHP_EOL', "\r\n");
				break;
				// Mac
				case 'DAR':
					define('PHP_EOL', "\r");
				break;
				// Unix
				default:
					define('PHP_EOL', "\n");
			}
		}
		return file_put_contents($file, $this->_section2ini($this->_config));
	}
	
	private function _ini2extend(array $a)
	{
		$out = array();
		foreach ($a as $k => $v) {
			if (is_array($v)) {
				$a[$k] = $this->_ini2extend($v);
			}
			$x = explode('.', $k);
			if (!empty($x[1])) {
				$x = array_reverse($x, true);
				if (isset($out[$k])) {
					unset($out[$k]);
				}
				if (!isset($out[$x[0]])) {
					$out[$x[0]] = array();
				}
				$first = true;
				foreach ($x as $xv) {
					if ($first === true) {
						$b = $a[$k];
						$first = false;
					}
					$b = array($xv => $b);
				}
				$out[$x[0]] = array_merge_recursive($out[$x[0]], $b[$x[0]]);
			} else {
				$out[$k] = $a[$k];
			}
		}
		return $out;
	}
	
	private function _section2ini(array $a, $parent = NULL)
	{
		$out = '';
		foreach ($a as $s => $c) {
			$out .= '[' . $s . ']' . PHP_EOL;
			$out .= $this->_array2ini($c);
			$out .= PHP_EOL;
		}
		return $out;
	}
	
	private function _array2ini(array $a, $parent = NULL)
	{
		$out = '';
		foreach ($a as $k => $v)
		{
			if( $parent && $this->_isArrSeq($a) && count($a) > 1 ) {
				$k = $parent . '[]';
			} elseif( $parent ) {
				$k = $parent.'.'.$k;
			}
			if (is_array($v))
			{
				$out .= $this->_array2ini($v, $k);
			} else {
				$out .= $k." = " ;
				if (is_numeric($v) || is_float($v)) {
					$out .= "$v";
				} elseif (is_bool($v)) {
					$out .= ($v===true) ? 1 : 0;
				} elseif (is_string($v)) {
					$out .= "'".addcslashes($v, "'")."'";
				} else {
					$out .= "$v";
				}
				$out .= PHP_EOL;
			}
		}
		return $out;
	}
	
	private function _isArrSeq($a)
	{
		return array_keys($a) == range(0, count($a) - 1);
	}
}