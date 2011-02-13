<?php
/**
* Smarty Internal Plugin Smarty Template  Base
*
* This file contains the basic shared methodes for template handling
*
* @package Smarty
* @subpackage Template
* @author Uwe Tews
*/


/**
* Class with shared template methodes
*/
class Smarty_Internal_TemplateBase extends Smarty_Internal_Data {

	// lazy loaded objects
	public $wrapper = null;
	public $utility = null;

	/**
	* fetches a rendered Smarty template
	*
	* @param string $template the resource handle of the template file or template object
	* @param mixed $cache_id cache id to be used with this template
	* @param mixed $compile_id compile id to be used with this template
	* @param object $ |null $parent next higher level of Smarty variables
	* @return string rendered template output
	*/
	public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false)
	{
		if ($template === null && $this instanceof $this->template_class) {
			$template = $this;
		}
		if (!empty($cache_id) && is_object($cache_id)) {
			$parent = $cache_id;
			$cache_id = null;
		}
		if ($parent === null) {
			// get default Smarty data object
			$parent = $this;
		}
		// create template object if necessary
		($template instanceof $this->template_class)? $_template = $template :
		$_template = $this->createTemplate ($template, $cache_id, $compile_id, $parent, false);
		if (isset($this->smarty->error_reporting)) {
			$_smarty_old_error_level = error_reporting($this->smarty->error_reporting);
		}
		// check URL debugging control
		if (!$this->smarty->debugging && $this->smarty->debugging_ctrl == 'URL') {
			if (isset($_SERVER['QUERY_STRING'])) {
				$_query_string = $_SERVER['QUERY_STRING'];
			} else {
				$_query_string = '';
			}
			if (false !== strpos($_query_string, $this->smarty->smarty_debug_id)) {
				if (false !== strpos($_query_string, $this->smarty->smarty_debug_id . '=on')) {
					// enable debugging for this browser session
					setcookie('SMARTY_DEBUG', true);
					$this->smarty->debugging = true;
				} elseif (false !== strpos($_query_string, $this->smarty->smarty_debug_id . '=off')) {
					// disable debugging for this browser session
					setcookie('SMARTY_DEBUG', false);
					$this->smarty->debugging = false;
				} else {
					// enable debugging for this page
					$this->smarty->debugging = true;
				}
			} else {
				if (isset($_COOKIE['SMARTY_DEBUG'])) {
					$this->smarty->debugging = true;
				}
			}
		}
		// get rendered template
		// disable caching for evaluated code
		if ($_template->source->recompiled) {
			$_template->caching = false;
		}
		// checks if template exists
		if (!$_template->source->exists) {
			throw new SmartyException("Unable to load template {$_template->source->type} '{$_template->source->name}'");
		}
		// read from cache or render
		if (!($_template->caching == Smarty::CACHING_LIFETIME_CURRENT || $_template->caching == Smarty::CACHING_LIFETIME_SAVED) || !$_template->cached->valid) {
			// render template (not loaded and not in cache)
			if (!$_template->source->uncompiled) {
				$_smarty_tpl = $_template;
				if ($_template->source->recompiled) {
					if ($this->smarty->debugging) {
						Smarty_Internal_Debug::start_compile($_template);
					}
					$code = $_template->compiler->compileTemplate($_template);
					if ($this->smarty->debugging) {
						Smarty_Internal_Debug::end_compile($_template);
					}
					if ($this->smarty->debugging) {
						Smarty_Internal_Debug::start_render($_template);
					}
					ob_start();
					eval("?>" . $code);
					unset($code);
				} else {
					if (!$_template->compiled->exists || ($_template->smarty->force_compile && !$_template->compiled->isCompiled)) {
						$_template->compileTemplateSource();
					}
					if ($this->smarty->debugging) {
						Smarty_Internal_Debug::start_render($_template);
					}
					if (!$_template->compiled->loaded) {
						include($_template->compiled->filepath);
						if ($_template->mustCompile) {
							// recompile and load again
							$_template->compileTemplateSource();
							include($_template->compiled->filepath);
						}
						$_template->compiled->loaded = true;
					}
					ob_start();
					$_template->properties['unifunc']($_template);
				}
			} else {
				if ($_template->source->uncompiled) {
					if ($this->smarty->debugging) {
						Smarty_Internal_Debug::start_render($_template);
					}
					ob_start();
					$_template->source->renderUncompiled($_template);
				} else {
					throw new SmartyException("Resource '$_template->source->type' must have 'renderUncompiled' methode");
				}
			}
			$_output = ob_get_clean();
			if (!$_template->source->recompiled && empty($_template->properties['file_dependency'][$_template->source->uid])) {
				$_template->properties['file_dependency'][$_template->source->uid] = array($_template->source->filepath, $_template->source->timestamp,$_template->source->type);
			}
			if ($_template->parent instanceof Smarty_Internal_Template) {
				$_template->parent->properties['file_dependency'] = array_merge($_template->parent->properties['file_dependency'], $_template->properties['file_dependency']);
				foreach($_template->required_plugins as $code => $tmp1) {
					foreach($tmp1 as $name => $tmp) {
						foreach($tmp as $type => $data) {
							$_template->parent->required_plugins[$code][$name][$type] = $data;
						}
					}
				}
			}
			if ($this->smarty->debugging) {
				Smarty_Internal_Debug::end_render($_template);
			}
			// write to cache when nessecary
			if (!$_template->source->recompiled && ($_template->caching == Smarty::CACHING_LIFETIME_SAVED || $_template->caching == Smarty::CACHING_LIFETIME_CURRENT)) {
				if ($this->smarty->debugging) {
					Smarty_Internal_Debug::start_cache($_template);
				}
				$_template->properties['has_nocache_code'] = false;
				// get text between non-cached items
				$cache_split = preg_split("!/\*%%SmartyNocache:{$_template->properties['nocache_hash']}%%\*\/(.+?)/\*/%%SmartyNocache:{$_template->properties['nocache_hash']}%%\*/!s", $_output);
				// get non-cached items
				preg_match_all("!/\*%%SmartyNocache:{$_template->properties['nocache_hash']}%%\*\/(.+?)/\*/%%SmartyNocache:{$_template->properties['nocache_hash']}%%\*/!s", $_output, $cache_parts);
				$output = '';
				// loop over items, stitch back together
				foreach($cache_split as $curr_idx => $curr_split) {
					// escape PHP tags in template content
					$output .= preg_replace('/(<%|%>|<\?php|<\?|\?>)/', '<?php echo \'$1\'; ?>', $curr_split);
					if (isset($cache_parts[0][$curr_idx])) {
						$_template->properties['has_nocache_code'] = true;
						// remove nocache tags from cache output
						$output .= preg_replace("!/\*/?%%SmartyNocache:{$_template->properties['nocache_hash']}%%\*/!", '', $cache_parts[0][$curr_idx]);
					}
				}
				if (isset($this->smarty->autoload_filters['output']) || isset($this->smarty->registered_filters['output'])) {
					$output = Smarty_Internal_Filter_Handler::runFilter('output', $output, $_template);
				}
				// rendering (must be done before writing cache file because of {function} nocache handling)
				$_smarty_tpl = $_template;
				ob_start();
				eval("?>" . $output);
				$_output = ob_get_clean();
				// write cache file content
				$_template->writeCachedContent($output);
				if ($this->smarty->debugging) {
					Smarty_Internal_Debug::end_cache($_template);
				}
			} else {
				// var_dump('renderTemplate', $_template->has_nocache_code, $_template->template_resource, $_template->properties['nocache_hash'], $_template->parent->properties['nocache_hash'], $_output);
				if ($_template->has_nocache_code && !empty($_template->properties['nocache_hash']) && !empty($_template->parent->properties['nocache_hash'])) {
					// replace nocache_hash
					$_output = preg_replace("/{$_template->properties['nocache_hash']}/", $_template->parent->properties['nocache_hash'], $_output);
					$_template->parent->has_nocache_code = $_template->has_nocache_code;
				}
			}
		} else {
			if ($this->smarty->debugging) {
				Smarty_Internal_Debug::start_cache($_template);
			}
			ob_start();
			$_template->properties['unifunc']($_template);
			$_output = ob_get_clean();
			if ($this->smarty->debugging) {
				Smarty_Internal_Debug::end_cache($_template);
			}
		}
		$_template->updateParentVariables();
		if ((!$this->caching || $_template->source->recompiled) && (isset($this->smarty->autoload_filters['output']) || isset($this->smarty->registered_filters['output']))) {
			$_output = Smarty_Internal_Filter_Handler::runFilter('output', $_output, $_template);
		}
		if (isset($this->error_reporting)) {
			error_reporting($_smarty_old_error_level);
		}
		// display or fetch
		if ($display) {
			if ($this->caching && $this->cache_modified_check) {
				$_isCached = $_template->isCached() && !$_template->has_nocache_code;
				$_last_modified_date = @substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], 'GMT') + 3);
				if ($_isCached && $_template->cached->timestamp <= strtotime($_last_modified_date)) {
					switch (PHP_SAPI) {
						case 'cgi':         // php-cgi < 5.3
						case 'cgi-fcgi':    // php-cgi >= 5.3
						case 'fpm-fcgi':    // php-fpm >= 5.3.3
						header('Status: 304 Not Modified');
						break;

						case 'cli':
						if (/* ^phpunit */!empty($_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS'])/* phpunit$ */) {
							$_SERVER['SMARTY_PHPUNIT_HEADERS'][] = '304 Not Modified';
						}
						break;

						default:
						header('HTTP/1.1 304 Not Modified');
						break;
					}
				} else {
					switch (PHP_SAPI) {
						case 'cli':
						if (/* ^phpunit */!empty($_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS'])/* phpunit$ */) {
							$_SERVER['SMARTY_PHPUNIT_HEADERS'][] = 'Last-Modified: ' . gmdate('D, d M Y H:i:s', $_template->cached->timestamp) . ' GMT';
						}
						break;

						default:
						header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_template->cached->timestamp) . ' GMT');
						break;
					}
					echo $_output;
				}
			} else {
				echo $_output;
			}
			// debug output
			if ($this->debugging) {
				Smarty_Internal_Debug::display_debug($this);
			}
			return;
		} else {
			// return fetched content
			return $_output;
		}
	}

	/**
	* displays a Smarty template
	*
	* @param string $ |object $template the resource handle of the template file  or template object
	* @param mixed $cache_id cache id to be used with this template
	* @param mixed $compile_id compile id to be used with this template
	* @param object $parent next higher level of Smarty variables
	*/
	public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
	{
		// display template
		$this->fetch ($template, $cache_id, $compile_id, $parent, true);
	}

	/**
	* test if cache i valid
	*
	* @param string $ |object $template the resource handle of the template file or template object
	* @param mixed $cache_id cache id to be used with this template
	* @param mixed $compile_id compile id to be used with this template
	* @param object $parent next higher level of Smarty variables
	* @return boolean cache status
	*/
	public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
	{
		if ($template === null && $this instanceof $this->template_class) {
			$template = $this;
		}
		if ($parent === null) {
			$parent = $this;
		}
		if (!($template instanceof $this->template_class)) {
			$template = $this->createTemplate ($template, $cache_id, $compile_id, $parent, false);
		}
		// return cache status of template
		return $template->cached->valid;
	}

	/**
	* creates a data object
	*
	* @param object $parent next higher level of Smarty variables
	* @returns object data object
	*/
	public function createData($parent = null)
	{
		return new Smarty_Data($parent, $this);
	}

	/**
	* creates a template object
	*
	* @param string $template the resource handle of the template file
	* @param mixed $cache_id cache id to be used with this template
	* @param mixed $compile_id compile id to be used with this template
	* @param object $parent next higher level of Smarty variables
	* @param boolean $do_clone flag is Smarty object shall be cloned
	* @returns object template object
	*/
	public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
	{
		if (!empty($cache_id) && (is_object($cache_id) || is_array($cache_id))) {
			$parent = $cache_id;
			$cache_id = null;
		}
		if (!empty($parent) && is_array($parent)) {
			$data = $parent;
			$parent = null;
		} else {
			$data = null;
		}
		if (!is_object($template)) {
			// we got a template resource
			// already in template cache?
			$_templateId =  sha1($template . $cache_id . $compile_id);
			if ($do_clone) {
				if (isset($this->template_objects[$_templateId])) {
					// return cached template object
					$tpl = clone $this->template_objects[$_templateId];
					$tpl->parent = $parent;
				} else {
					$tpl = new $this->template_class($template, $this, $parent, $cache_id, $compile_id);
				}
			} else {
				if (isset($this->template_objects[$_templateId])) {
					// return cached template object
					$tpl = $this->template_objects[$_templateId];
				} else {
					$tpl = new $this->template_class($template, $this, $parent, $cache_id, $compile_id);
				}
			}
		} else {
			// just return a copy of template class
			$tpl = $template;
		}
		// fill data if present
		if (!empty($data) && is_array($data)) {
			// set up variable values
			foreach ($data as $_key => $_val) {
				$tpl->tpl_vars[$_key] = new Smarty_variable($_val);
			}
		}
		return $tpl;
	}

	/**
	* Registers plugin to be used in templates
	*
	* @param string $type plugin type
	* @param string $tag name of template tag
	* @param callback $callback PHP callback to register
	* @param boolean $cacheable if true (default) this fuction is cachable
	* @param array $cache_attr caching attributes if any
	*/

	public function registerPlugin($type, $tag, $callback, $cacheable = true, $cache_attr = null)
	{
		if (isset($this->smarty->registered_plugins[$type][$tag])) {
			throw new Exception("Plugin tag \"{$tag}\" already registered");
		} elseif (!is_callable($callback)) {
			throw new Exception("Plugin \"{$tag}\" not callable");
		} else {
			$this->smarty->registered_plugins[$type][$tag] = array($callback, (bool) $cacheable, (array) $cache_attr);
		}
	}

	/**
	* Unregister Plugin
	*
	* @param string $type of plugin
	* @param string $tag name of plugin
	*/
	function unregisterPlugin($type, $tag)
	{
		if (isset($this->smarty->registered_plugins[$type][$tag])) {
			unset($this->smarty->registered_plugins[$type][$tag]);
		}
	}

	/**
	* Registers a resource to fetch a template
	*
	* @param string $type name of resource type
	* @param Smarty_Resource|array $callback or instance of Smarty_Resource, or array of callbacks to handle resource (deprecated)
	*/
	public function registerResource($type, $callback)
	{
		$this->smarty->registered_resources[$type] = $callback instanceof Smarty_Resource ? $callback : array($callback, false);
	}

	/**
	* Unregisters a resource
	*
	* @param string $type name of resource type
	*/
	function unregisterResource($type)
	{
		if (isset($this->smarty->registered_resources[$type])) {
			unset($this->smarty->registered_resources[$type]);
		}
	}

	/**
	* Registers a cache resource to cache a template's output
	*
	* @param string $type name of cache resource type
	* @param Smarty_CacheResource $callback instance of Smarty_CacheResource to handle output caching
	*/
	public function registerCacheResource($type, $callback)
	{
		if (!($callback instanceof Smarty_CacheResource)) {
			throw new SmartyException("CacheResource handlers must implement Smarty_CacheResource");
		}
		$this->smarty->registered_cache_resources[$type] = $callback;
	}

	/**
	* Unregisters a cache resource
	*
	* @param string $type name of cache resource type
	*/
	function unregisterCacheResource($type)
	{
		if (isset($this->smarty->registered_cache_resources[$type])) {
			unset($this->smarty->registered_cache_resources[$type]);
		}
	}

	/**
	* Registers object to be used in templates
	*
	* @param string $object name of template object
	* @param object $ &$object_impl the referenced PHP object to register
	* @param mixed $ null | array $allowed list of allowed methods (empty = all)
	* @param boolean $smarty_args smarty argument format, else traditional
	* @param mixed $ null | array $block_functs list of methods that are block format
	*/
	function registerObject($object_name, $object_impl, $allowed = array(), $smarty_args = true, $block_methods = array())
	{
		// test if allowed methodes callable
		if (!empty($allowed)) {
			foreach ((array)$allowed as $method) {
				if (!is_callable(array($object_impl, $method))) {
					throw new SmartyException("Undefined method '$method' in registered object");
				}
			}
		}
		// test if block methodes callable
		if (!empty($block_methods)) {
			foreach ((array)$block_methods as $method) {
				if (!is_callable(array($object_impl, $method))) {
					throw new SmartyException("Undefined method '$method' in registered object");
				}
			}
		}
		// register the object
		$this->smarty->registered_objects[$object_name] =
		array($object_impl, (array)$allowed, (boolean)$smarty_args, (array)$block_methods);
	}

	/**
	* Registers static classes to be used in templates
	*
	* @param string $class name of template class
	* @param string $class_impl the referenced PHP class to register
	*/
	function registerClass($class_name, $class_impl)
	{
		// test if exists
		if (!class_exists($class_impl)) {
			throw new SmartyException("Undefined class '$class_impl' in register template class");
		}
		// register the class
		$this->smarty->registered_classes[$class_name] = $class_impl;
	}

	/**
	* Registers a default plugin handler
	*
	* @param  $callback mixed string | array $plugin class/methode name
	*/
	function registerDefaultPluginHandler($callback)
	{
		if (is_callable($callback)) {
			$this->smarty->default_plugin_handler_func = $callback;
		} else {
			throw new SmartyException("Default plugin handler '$callback' not callable");
		}
	}

	/**
	* Registers a default template handler
	*
	* @param  $callback mixed string | array class/method name
	*/
	function registerDefaultTemplateHandler($callback)
	{
		if (is_callable($callback)) {
			$this->smarty->default_template_handler_func = $callback;
		} else {
			throw new SmartyException("Default template handler '$callback' not callable");
		}
	}

	/**
	* Registers a default template handler
	*
	* @param  $callback mixed string | array class/method name
	*/
	function registerDefaultConfigHandler($callback)
	{
		if (is_callable($callback)) {
			$this->smarty->default_config_handler_func = $callback;
		} else {
			throw new SmartyException("Default config handler '$callback' not callable");
		}
	}

	/**
	* Registers a filter function
	*
	* @param string $type filter type
	* @param callback $callback
	*/
	public function registerFilter($type, $callback)
	{
		$this->smarty->registered_filters[$type][$this->_get_filter_name($callback)] = $callback;
	}

	/**
	* Unregisters a filter function
	*
	* @param string $type filter type
	* @param callback $callback
	*/
	public function unregisterFilter($type, $callback)
	{
		$name = $this->_get_filter_name($callback);
		if(isset($this->smarty->registered_filters[$type][$name])) {
			unset($this->smarty->registered_filters[$type][$name]);
		}
	}


	/**
	* Return internal filter name
	*
	* @param callback $function_name
	*/
	public function _get_filter_name($function_name)
	{
		if (is_array($function_name)) {
			$_class_name = (is_object($function_name[0]) ?
			get_class($function_name[0]) : $function_name[0]);
			return $_class_name . '_' . $function_name[1];
		} else {
			return $function_name;
		}
	}


	/**
	* load a filter of specified type and name
	*
	* @param string $type filter type
	* @param string $name filter name
	* @return bool
	*/
	function loadFilter($type, $name)
	{
		$_plugin = "smarty_{$type}filter_{$name}";
		$_filter_name = $_plugin;
		if ($this->smarty->loadPlugin($_plugin)) {
			if (class_exists($_plugin, false)) {
				$_plugin = array($_plugin, 'execute');
			}
			if (is_callable($_plugin)) {
				return $this->smarty->registered_filters[$type][$_filter_name] = $_plugin;
			}
		}
		throw new SmartyException("{$type}filter \"{$name}\" not callable");
		return false;
	}

	/**
	* Handle unknown class methods
	*
	* @param string $name unknown methode name
	* @param array $args aurgument array
	*/
	public function __call($name, $args)
	{
		static $camel_func;
		if (!isset($camel_func))
		$camel_func = create_function('$c', 'return "_" . strtolower($c[1]);');
		// see if this is a set/get for a property
		$first3 = strtolower(substr($name, 0, 3));
		if (in_array($first3, array('set', 'get')) && substr($name, 3, 1) !== '_') {
			// try to keep case correct for future PHP 6.0 case-sensitive class methods
			// lcfirst() not available < PHP 5.3.0, so improvise
			$property_name = strtolower(substr($name, 3, 1)) . substr($name, 4);
			// convert camel case to underscored name
			$property_name = preg_replace_callback('/([A-Z])/', $camel_func, $property_name);
			if (!property_exists($this, $property_name)) {
				throw new SmartyException("property '$property_name' does not exist.");
				return false;
			}
			if ($first3 == 'get')
			return $this->$property_name;
			else
			return $this->$property_name = $args[0];
		}
		// Smarty Backward Compatible wrapper
		if (strpos($name,'_') !== false) {
			if (!isset($this->wrapper)) {
				$this->wrapper = new Smarty_Internal_Wrapper($this);
			}
			return $this->wrapper->convert($name, $args);
		}
		if (in_array($name,array('clearCompiledTemplate','compileAllTemplates','compileAllConfig','testInstall','getTags'))) {
			if (!isset($this->utility)) {
				$this->utility = new Smarty_Internal_Utility($this);
			}
			return call_user_func_array(array($this->utility,$name), $args);
		}
		// PHP4 call to constructor?
		if (strtolower($name) == 'smarty') {
			throw new SmartyException('Please use parent::__construct() to call parent constuctor');
			return false;
		}
        // pass call to Smarty object
        if (is_callable(array($this->smarty,$name))) {
        	return call_user_func_array(array($this->smarty,$name),$args);
        } else {
			throw new SmartyException("Call of unknown function '$name'.");
		}
	}
}
?>