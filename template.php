<?php

/*
 * Copyright 2014, Xorcom Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

class template_block {
	private $stack;

	private function __construct($stack) {
		$this->stack = $stack;
	}

	private function get_property($name, &$value, $parent_only = false) {
		$first_element = true;
		foreach ($this->stack as $property) {
			if ($first_element && $parent_only) {
				$first_element = false;
				continue;
			}
			if (is_array($property) && array_key_exists($name, $property)) {
				$value = $property[$name];
				return true;
			}
			if (is_object($property) && property_exists($property, $name)) {
				$value = $property->$name;
				return true;
			}
		}
		$value = null;
		return false;
	}

	// Does the same as get_property() but skips the first stack entry
	private function parent_property($name, &$value) {
		return $this->get_property($name, $value, true);
	}

	public static function render($stack, $body) {
		$block = new template_block($stack);
		$regex = '/\{([^\s}]+)\s+([^\s}]+)(?:\s+([^}]+))?\}[\t\r\n]*(.*?)\{\/\1\s+\2\}[\t\r\n]*/ms';
		$callback = array($block, 'expand');
		return $block->resolve($block->exec(preg_replace_callback($regex, $callback, $body)));
	}

	private function expand($match) {
		list(, $method, $name, $params, $body) = $match;
		$method = 'expand_' . $method;
		if ($method != __FUNCTION__ && method_exists($this, $method)) {
			return $this->$method($name, $params, $body);
		}
		return null;
	}

	private function expand_loop($name, $params, $body) {
		$result = null;
		if ($this->get_property($name, $collection)) {
			if (is_array($collection) || $collection instanceof Traversable) {
				foreach ($collection as $property) {
					$stack = array_merge(array($property), $this->stack);
					$result .= self::render($stack, $body);
				}
			}
		}
		return $result;
	}

	private function expand_if($left_name, $right_name, $body) {
		$parts = preg_split('/\{else\s+' . preg_quote($left_name) . '\}/', $body);
		if (strlen($right_name) > 0) {
			if ($this->get_property($left_name, $left_value) &&
				$this->get_property($right_name, $right_value) &&
				$left_value == $right_value) {
				return self::render($this->stack, $parts[0]);
			} else if (count($parts) > 1) {
				return self::render($this->stack, $parts[1]);
			}
		} else {
			if ($this->get_property($left_name, $left_value) && $left_value) {
				return self::render($this->stack, $parts[0]);
			} else if (count($parts) > 1) {
				return self::render($this->stack, $parts[1]);
			}
		}
		return null;
	}

	private function exec($body) {
		$regex = '/\{([^\s}]+)\s+([^}]+)\}/';
		$callback = array($this, 'exec_method');
		return preg_replace_callback($regex, $callback, $body);
	}

	private function exec_method($match) {
		list(,$method, $params) = $match;
		$method = 'exec_' . $method;
		if ($method != __FUNCTION__ && method_exists($this, $method)) {
			return $this->$method($params);
		}
		return null;
	}

	// For one parameter, we will compare between $left_name and "value" (previous stack)
	// For two parmeters, we will compare between $left_name (current stack) and $right_name (previous stack)
	private function exec_selectedif($params) {
		$params = explode(' ', $params);
		$left_name = $params[0];
		$right_name = 'value';
		if (count($params) > 1) {
			$right_name = $params[1];
		}
		if ($this->get_property($left_name, $left_value) &&
			$this->parent_property($right_name, $right_value) &&
			(string) $left_value == (string) $right_value) { // we want 0 == "0" but not 0 == null
			return ' selected';
		}
		return null;
	}

	private function exec_checkedif($params) {
		$params = explode(' ', $params);
		list($name) = $params;
		if ($this->get_property($name, $value) && $value) {
			return ' checked';
		}
		return null;
	}

	private function resolve($body) {
		$regex = '/\{([^\s}]+)\}/';
		$callback = array($this, 'resolve_symbol');
		return preg_replace_callback($regex, $callback, $body);
	}

	private function resolve_symbol($match) {
		list($original, $name) = $match;
		$length = strlen($name);
		if ($length > 0) {
			if (substr($name, -4, 4) == '.tpl') {
				$filename = __DIR__ . '/' . $name;
				if (file_exists($filename)) {
					return self::render($this->stack, @file_get_contents($filename));
				}
			} else {
				if ($name[0] == '_') {
					return _(substr($name, 1));
				}
				if ($this->get_property($name, $value)) {
					return $value;
				}
			}
		}
		return $original;
	}
}

class template {
	private $__filename;
	private $__properties = array();

	public function __construct($filename) {
		$this->__filename = $filename;
	}

	public function render() {
		$filename = __DIR__ . '/' . $this->__filename;
		if (file_exists($filename)) {
			echo template_block::render(array($this->__properties), @file_get_contents($filename));
		} else {
			throw new Exception($this->__filename . ' does not exist');
		}
	}

	public function __set($name, $value) {
		$this->__properties[$name] = $value;
	}

	public function &__get($name) {
		$null = null;
		if (array_key_exists($name, $this->__properties)) { return $this->__properties[$name]; }
		return $null;
	}

	public function __isset($name) {
		return isset($this->__properties[$name]);
	}

	public function __unset($name) {
		unset($this->__properties[$name]);
	}

	public function __tostring() {
		ob_start();
		$this->render();
		return ob_get_clean();
	}
}

?>
