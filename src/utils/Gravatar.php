<?php

namespace Monlib\Utils;

class Gravatar {

	private static string $URL	=	'https://www.gravatar.com/avatar/%s?s=%s';

	private static function avatar(string $email): string { return md5($email); }

	public static function get(string $email, int $size = null): string {
		return sprintf(
			self::$URL, self::avatar($email), $size ?? 300
		);
	}

}
