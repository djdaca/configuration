<?php
/**
 * Simple INI Configuration Wrapper
 * 
 * @author Daniel Èekan <djdaca@gmail.com>
 * @version 1.0  
 **/
 
class Configuration
{
	
	/**
		@var array $_config
	*/
	private $_config = array();
	
	/**
		@var mixed instance of Configuration or NULL
	*/
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
		@return mixed
	*/
	public function __call($name, $arguments)
	{
		return $this->getSection( $name, isset($arguments[0])? $arguments[0] : NULL );
	}
	
	/**
		Easter for static get INI section
		
		@method string $name section name
		@param string $var config var
		@return mixed
	*/
	public static function __callStatic($name, $arguments)
	{
		return self::getInstance()->getSection( $name, isset($arguments[0])? $arguments[0] : NULL );
	}
	
	/**
		Get static instance of configuration
		
		@return \Configuration $_instance
	*/
	public static function getInstance()
	{
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
		Export config
		
		@return array
	*/
	public function export()
	{
		return $this->_config;
	}
	
	/**
		Import config
		
		@param $config
		@return void
	*/
	public function import($config)
	{
		$this->_config = $config;
	}
	
	/**
		Set INI config file
		
		@param string $file
		@return void
	*/
	public function addConfigFile($file)
	{
		if( file_exists($file) )
		{
			$this->_config = array_merge($this->_config, parse_ini_file($file, true));
			return true;
		}
		return false;
	}
	
	/**
		Get INI section
		
		@param string $name section name
		@param string $var config var
		@return mixed
	*/
	public function getSection($name, $var = NULL)
	{
		if (isset( $this->_config[$name] ) )
		{
			$section = $this->_config[$name];
			return ( $var && isset( $section[$var] ) )? $section[$var] : $section;
		}
		return NULL;
	}
	
	/**
		Set configuration section to array
		
		@param string $var config var
		@param string $value config value
		@param string $section config section
		@return void
	*/
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
	*/
	public function save($file)
	{
		$res = array();
		foreach($this->_config as $key => $val)
		{
			if(is_array($val))
			{
				$res[] = "[$key]";
				foreach($val as $skey => $sval)
				{ 
					$res[] = $skey ."=".(is_numeric($sval) ? $sval : '"'.$sval.'"');
				}
			} else {
				$res[] = $key."=".(is_numeric($val) ? $val : '"'.$val.'"');
			}
		}
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
		return file_put_contents($file, implode(PHP_EOL, $res));
	}
}