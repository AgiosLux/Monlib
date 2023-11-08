<?php

namespace Monlib\Utils;

class Validate {

	public static function ip($ip): bool {
		return filter_var($ip, FILTER_VALIDATE_IP) !== false;
	}

	public static function email($email): bool {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	public static function url($url): bool {
		return filter_var($url, FILTER_VALIDATE_URL) !== false;
	}

	public static function int($integer): bool {
		return filter_var($integer, FILTER_VALIDATE_INT) !== false;
	}

	public static function boolean($integer): bool {
		return filter_var($integer, FILTER_VALIDATE_BOOLEAN) !== false;
	}

	public static function float($float): bool {
		return filter_var($float, FILTER_VALIDATE_FLOAT) !== false;
	}

	public static function mac($mac): bool {
		return filter_var($mac, FILTER_VALIDATE_MAC) !== false;
	}

	public static function domain($domain): bool {
		return filter_var($domain, FILTER_VALIDATE_DOMAIN) !== false;
	}

	public static function regex($regex): bool {
		return filter_var($regex, FILTER_VALIDATE_REGEXP) !== false;
	}

	public static function apiKey($key): bool {
		if (strlen($key) >= 32 && strlen($key) <= 64) {
			return preg_match('/^[a-zA-Z0-9]+$/', $key);
		} else {
			return false;
		}
	}

	public static function serialKey($serial): bool {
		return preg_match('/^[A-Z0-9]{6}-[A-Z0-9]{6}-[A-Z0-9]{6}$/', $serial);
	}

}
