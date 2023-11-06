<?php

namespace Monlib\Utils;

class Generate {

	public static function uuid4(): string {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	public static function slugify(string $text): string {
		$text	=	iconv('UTF-8', 'ASCII//TRANSLIT', $text);
		$text	=	preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
		$text	=	preg_replace('/\s+/', '-', $text);
		$text	=	strtolower($text);
		$text	=	trim($text, '-');
		$text	=	preg_replace('/-+/', '-', $text);
		
		return $text;
	}

	public static function generateRandomString(int $size): string {
		$characters			=	'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$password			=	'';
	
		for ($i = 0; $i < $size; $i++) {
			$randomIndex	=	mt_rand(0, strlen($characters) - 1);
			$password		.=	$characters[$randomIndex];
		}
	
		return $password;
	}
	
}
