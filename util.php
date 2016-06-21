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

require_once('../includes/db.php');

function get($name, $default = null) { return isset($_GET[$name]) ? $_GET[$name] : $default; }
function post($name, $default = null) { return isset($_POST[$name]) ? $_POST[$name] : $default; }
function is_postback() { return (strtolower($_SERVER['REQUEST_METHOD']) == 'post'); }
function error_message($msg) { return array('message' => $msg); }
function invalid_ip($value) { return (empty($value) || (@inet_pton($value) === false)); }
function invalid_port($value) { return (empty($value) || ($value <= 0) || ($value > 65535)); }

function not_found() {
	header('HTTP/1.0 404 Not Found');
	die("<h1>404 Not Found</h1>\nThe page that you have requested could not be found.");
}

if (!function_exists("http_redirect")) {
   function http_redirect($url, $params=array(), $session=false, $status=0) {
       if ($session) {
           $params[session_name()] = session_id();
       }
       if ($params) {
           $url .= strstr($url, "?") ? "&" : "?";
           $url .= http_build_query($params);
       }
       header("Location: $url"); #, $status ? $status : 301);
       $url = htmlspecialchars($url, ENT_QUOTES, "UTF-8");
       print "Redirecting to <a href=\"$url\">$url</a>\n";
       print "<meta http-equiv=\"Location\" content=\"$url\" />\n";
       exit; // built-in exit
   }
}

class util {
	// Redirect the current page elsewhere, FreePBX' version of this is broken
	// Note: http_redirect does not work properly when no parameters
	//       are given and will cause Chrome to misbehave
	public static function redirect($url = null, array $params = null) {
		if ($url === null || strlen($url) <= 0) {
			$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

			if (!is_array($params) || count($params) <= 0) {
				parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $params);
			}
		}
		while (@ob_end_clean());
		http_redirect($url, $params);
		exit;
	}

	public static function valid_mac_address($mac_address) {
		return preg_match('/^[0-9a-f]{12}$/', $mac_address);
	}

	// Validate a string as a port number, port 0 is allowed
	public static function valid_port($port) {
		if (preg_match('/^\d{1,5}$/', $port)) {
			$value = intval($value);
			return ($value >= 0 && $value < 65536);
		}
		return false;
	}

	// Trims and strips colons and minus from input string
	public static function sanitize_mac_address($mac_address) {
		return str_replace(array(':', '-'), null, strtolower(trim($mac_address)));
	}

	// Returns null when string has zero length
	public static function nullable($value) {
		return (strlen($value) > 0) ? $value : null;
	}

	// Returns null when int is zero or string has zero length
	public static function nullable_int($value) {
		if (strlen($value) > 0) {
			$value = intval($value);
			return ($value > 0) ? $value : null;
		}
		return null;
	}

	public static function array_get($name, &$array, $default = null) {
		return (array_key_exists($name, $array)) ? $array[$name] : $default;
	}

	public static function ampconf() {
		if (($amportal_conf = @file_get_contents('/etc/amportal.conf')) !== false) {
			if (preg_match_all('/(AMPDB.*?)\=(.*)/', $amportal_conf, $m) !== false) {
				$ampconf = (object) array();
				foreach ($m[1] as $i => $name) { $ampconf->$name = $m[2][$i]; }
				return $ampconf;
			}
		}
		return null;
	}
}

?>
