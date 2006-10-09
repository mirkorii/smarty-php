<?php

require_once "PEAR.php";

/**
 * Config_File class.
 *
 * @version 1.2.3
 * @author Andrei Zmievski <andrei@php.net>
 * @access public
 * 
 * Copyright: 2001 ispi of Lincoln, Inc.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * You may contact the author of Config_File by e-mail at:
 * andrei@php.net
 *
 * Or, write to:
 * Andrei Zmievski
 * Software Engineer, ispi
 * 237 S. 70th suite 220
 * Lincoln, NE 68510
 *
 * The latest version of Config_File can be obtained from:
 * http://www.phpinsider.com
 */

class Config_File extends PEAR {
	/* Options */
	/**
	 * Controls whether variables with the same name overwrite each other.
	 * 
	 * @access public
	 */
	var $overwrite		=	true;
	/**
	 * Controls whether config values of on/true/yes and off/false/no get
	 * converted to boolean values automatically.
	 *
	 * @access public
	 */
	var $booleanize		=	true;

	/* Private variables */
	var $_config_path	= "";
	var $_config_data	= array();
	var $_separator		= "";


	/**
	 * Constructs a new config file class.
	 *
	 * @param $config_path string (optional) path to the config files
	 * @access public
	 */
	function Config_File($config_path = NULL)
	{
		$this->PEAR();

		if (substr(PHP_OS, 1, 3) == "WIN")
			$this->_separator = "\\";
		else
			$this->_separator = "/";

		if (isset($config_path))
			$this->set_path($config_path);
	}


	/**
	 * Set the path where configuration files can be found.
	 *
	 * @param $config_path string  path to the config files
	 * @access public
	 */
	function set_path($config_path)
	{
		if (!is_string($config_path) ||
			($config_path != ""
			 && !is_dir($config_path))) {
			return new Config_File_Error("Bad config file path '$config_path'");
		}

		if ($config_path != "")
			$this->_config_path = $config_path . $this->_separator;
	}

	
	/**
	 * Retrieves config info based on the file, section, and variable name.
	 *
	 * @access public
	 * @param $file_name string config file to get info for
	 * @param $section_name string (optional) section to get info for
	 * @param $var_name string (optional) variable to get info for
	 * @return mixed a value or array of values
	 */
	function &get($file_name, $section_name = NULL, $var_name = NULL)
	{
		if (empty($file_name))
			return new Config_File_Error('Empty config file name');
		else {
			if ($this->_config_path != "")
				$file_name = $this->_config_path . $this->_separator . $file_name;
			if (!isset($this->_config_data[$file_name]))
				$this->load_file($file_name, false);
		}
		
		if (!empty($var_name)) {
			if (empty($section_name))
			{
				return $this->_config_data[$file_name]["vars"][$var_name];
			}
			else
				return $this->_config_data[$file_name]["sections"][$section_name]["vars"][$var_name];
		} else {
			if (empty($section_name))
				return (array)$this->_config_data[$file_name]["vars"];
			else
				return (array)$this->_config_data[$file_name]["sections"][$section_name]["vars"];
		}
	}
	

	/**
	 * Retrieves config info based on the key.
	 *
	 * @access public
	 * @param $file_name string config key (filename/section/var)
	 * @return mixed a value or array of values
	 */
	function &get_key($config_key)
	{
		list($file_name, $section_name, $var_name) = explode('/', $config_key, 3);
		$result = &$this->get($file_name, $section_name, $var_name);
		return $result;
	}

	/**
	 * Get all loaded config file names.
	 *
	 * @access public
	 * @return array an array of loaded config file names
	 */
	function get_file_names()
	{
		return array_keys($this->_config_data);
	}
	

	/**
	 * Get all section names from a loaded file.
	 *
	 * @access public
	 * @param  $file_name string config file to get section names from
	 * @return array an array of section names from the specified file
	 */
	function get_section_names($file_name)
	{
		if ($this->_config_path != "")
			$file_name = $this->_config_path . $this->_separator . $file_name;
		if (!isset($this->_config_data[$file_name]))
			return new Config_File_Error("Unknown config file '$file_name'");
		
		return array_keys($this->_config_data[$file_name]["sections"]);
	}
	

	/**
	 * Get all global or section variable names.
	 *
	 * @access public
	 * @param $file_name string config file to get info for
	 * @param $section_name string (optional) section to get info for
	 * @return array an array of variables names from the specified file/section
	 */
	function get_var_names($file_name, $section = NULL)
	{
		if (empty($file_name))
			return new Config_File_Error('Empty config file name');
		else if (!isset($this->_config_data[$file_name]))
			return new Config_File_Error("Unknown config file '$file_name'");
		
		if (empty($section))
			return array_keys($this->_config_data[$file_name]["vars"]);
		else
			return array_keys($this->_config_data[$file_name]["sections"][$section]["vars"]);
	}
	

	/**
	 * Clear loaded config data for a certain file or all files.
	 *
	 * @access public
	 * @param  $file_name string file to clear config data for
	 */
	function clear($file_name = NULL)
	{
		if ($file_name === NULL)
			$this->_config_data = array();
		else if (isset($this->_config_data[$file_name]))
			$this->_config_data[$file_name] = array();
	}


	/**
	 * Load a configuration file manually.
	 *
	 * @access public
	 * @param  $file_name string file name to load
	 * @param  $prepend_path boolean whether current config path should be prepended to the filename
	 */
	function load_file($file_name, $prepend_path = true)
	{
		global	$php_errormsg;

		if ($prepend_path && $this->_config_path != "")
			$config_file = $this->_config_path . $this->_separator . $file_name;
		else
			$config_file = $file_name;

		ini_set('track_errors', true);
		$fp = @fopen($config_file, "r");
		if (!is_resource($fp))
			return new Config_File_Error($php_errormsg);

		$contents = fread($fp, filesize($config_file));
		fclose($fp);

		$config_data = array();

		/* Get global variables first. */
		if (preg_match("/^(.*?)(\n\[|\Z)/s", $contents, $match))
			$config_data["vars"] = $this->_parse_config_block($match[1]);
		
		/* Get section variables. */
		$config_data["sections"] = array();
		preg_match_all("/^\[(.*?)\]/m", $contents, $match);
		foreach ($match[1] as $section) {
			if (preg_match("/\[".preg_quote($section)."\](.*?)(\n\[|\Z)/s", $contents, $match))
				$config_data["sections"][$section]["vars"] = $this->_parse_config_block($match[1]);
		}

		$this->_config_data[$config_file] = $config_data;
	}

	
	function _parse_config_block($config_block)
	{
		$vars = array();

		/* First we grab the multi-line values. */
		if (preg_match_all("/^([^=\n]+)=\s*\"{3}(.*?)\"{3}\s*$/ms", $config_block, $match, PREG_SET_ORDER)) {
			for ($i = 0; $i < count($match); $i++) {
				$this->_set_config_var($vars, trim($match[$i][1]), $match[$i][2], false);
			}
			$config_block = preg_replace("/^[^=\n]+=\s*\"{3}.*?\"{3}\s*$/ms", "", $config_block);
		}
		
		
		$config_lines = preg_split("/\n+/", $config_block);

		foreach ($config_lines as $line) {
			if (preg_match("/^\s*(\w+)\s*=(.*)/", $line, $match)) {
				$var_value = preg_replace('/^([\'"])(.*)\1$/', '\2', trim($match[2]));
				$this->_set_config_var($vars, trim($match[1]), $var_value, $this->booleanize);
			}
		}

		return $vars;
	}
	
	function _set_config_var(&$container, $var_name, $var_value, $booleanize)
	{
		if (!preg_match("/^[a-zA-Z_]\w*$/", $var_name))
			return new Config_File_Error("Bad variable name '$var_name'");

		if ($booleanize) {
			if (preg_match("/^on|true|yes$/", $var_value))
				$var_value = true;
			else if (preg_match("/^off|false|no$/", $var_value))
				$var_value = false;
		}
				
		if (!isset($container[$var_name]) || $this->overwrite)
			$container[$var_name] = $var_value;
		else {
			settype($container[$var_name], 'array');
			$container[$var_name][] = $var_value;
		}
	}
}

/** @exclude */
class Config_File_Error extends PEAR_Error {
	var	$error_message_prefix = 'Config_File: ';

	function Config_File_Error($message,
							   $code = 0,
							   $mode = PEAR_ERROR_PRINT,
							   $level = E_USER_NOTICE)
	{
		$this->PEAR_Error($message."\n", $code, $mode, $level);
	}
}

?>