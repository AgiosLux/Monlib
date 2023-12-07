<?php

namespace Monlib\Utils;
use DateTime;

class Misc {

	public static function getUrl(string $inputString): string {
		preg_match_all('/(https?:\/\/[^\s]+)/', $inputString, $matches);

		$links		=	$matches[0];
		return $links[0];
	}

	public static function gravatar(string $email, int $size = 300): string {
		return sprintf(
			'https://www.gravatar.com/avatar/%s?s=%s',
			md5($email),
			$size ?? 300
		);
	}

	public static function formatDate(string $date, string $format): string {
		$date	=	new DateTime($date);
		return $date->format($format);
	}

	public static function hasUrl(string $string): bool {
		return preg_match('/\bhttps?:\/\/\S+\b/', $string);
	}

	public static function formatBytes(int $bytes, int $precision = 2): string {
		$units	=	['B', 'KB', 'MB', 'GB', 'TB', 'EB'];
		$bytes  = 	max($bytes, 0);
		$pow    =	floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow	=	min($pow, count($units) - 1);
		$bytes	/=	(1 << (10 * $pow));

		return round($bytes, $precision) . ' ' . $units[$pow];
	}

}
