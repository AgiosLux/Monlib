<?php

namespace Monlib\Utils;

class File {

    public static function get(string $file) {
        if (is_file($file)) {
            return file_get_contents($file);
        } else {
            return 'file not exists';
        }
    }

    public static function put(string $file, string $content) {
        if (is_file($file)) {
            return file_put_contents($file, $content);
        }
    }

    public static function delete(string $file) {
        if (is_file($file)) {
            return unlink($file);
        }
    }

    public static function exists(string $file): bool {
        if (is_file($file)) {
            return true;
        }

        return false;
    }

    public static function size(string $file) {
        if (is_file($file)) {
            return filesize($file);
        }
    }

    public static function move(string $from, string $to): bool {
        if (move_uploaded_file($from, $to)) {
            return true;
        }

        return false;
    }

}
