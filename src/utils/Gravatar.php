<?php

namespace Monlib\Utils;

class Gravatar {

	private static string $URL	=	'https://www.gravatar.com/avatar/%s?s=%s';

	public static function get(string $email, int $size = null): string {
		return sprintf(
			self::$URL, md5($email), $size ?? 300
		);
	}

}
