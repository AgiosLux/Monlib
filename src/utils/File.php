<?php

namespace Monlib\Utils;

class File {

	public static function read(string $file): string {
		return file_get_contents($file);
	}

	public static function create(string $file, string $content): bool {
		if (file_put_contents($file, $content)) { return true; }
		return false;
	}

	public static function delete(string $file): bool {
		if (unlink($file)) { return true; }
		return false;
	}

	public static function exists(string $file): bool {
		if (is_file($file)) { return true; }
		return false;
	}

	public static function size(string $file): int {
		return filesize($file);
	}

	public static function move(string $from, string $to): bool {
		if (move_uploaded_file($from, $to)) { return true; }
		return false;
	}

	public static function ext(string $file): string {
		return pathinfo($file, PATHINFO_EXTENSION);
	}

}
