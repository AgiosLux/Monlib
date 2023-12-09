<?php

namespace Monlib\Utils;

class View {

	private static array $vars;

	private static function getContentView(string $view) {
		$file	=	"./resources/pages/$view.html";
		return file_exists($file) ? file_get_contents($file) : $file;
	}

	public static function init(array $vars) { self::$vars = $vars; }

	public static function render(string $view, array $vars = []) {
		$content	=	self::getContentView($view);
		$vars       =	array_merge(self::$vars, $vars);
		$keys       =	array_keys($vars);
		
		$keys		=	array_map(function ($var) {
			return '{{' . $var . '}}';
		}, $keys);

		return str_replace(
			$keys, array_values($vars), $content
		);
	}

}
