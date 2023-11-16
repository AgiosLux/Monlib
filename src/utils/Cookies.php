<?php

namespace Monlib\Utils;

class Cookies {

	public static function has(string $name): bool {
		return isset(
			$_COOKIE[$name]
		);
	}

	public static function delete(string $name): void {
		self::create($name, [
			'value'		=> 	null,
			'expire'	=>	time() - 3600,
		]);
	}

	public static function create(string $name, array $data): void {
		setcookie(
			$name,
			$data['value'],
			$data['expire'] ?? 0,
			$data['path'] ?? '/',
			$data['domain'] ?? null,
			$data['secure'] ?? false,
			$data['httpOnly'] ?? false
		);
	}

	public static function get(string $name) { return $_COOKIE[$name]; }

}
