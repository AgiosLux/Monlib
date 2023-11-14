<?php

namespace Monlib\Utils;
use Smalot\PdfParser\Parser;

use Dotenv\Dotenv;

class Pdf extends Misc {

	public static function urlFileName(string $url): string|bool {
		$url_info	=	parse_url($url);

		if (isset($url_info['path'])) {
			$path_parts		=	pathinfo($url_info['path']);
			return $path_parts['basename'];
		} else {
			return false;
		}
	}

	public static function urlFileSize(string $url): string|bool {
		$file_contents = file_get_contents($url);

		if ($file_contents !== false) {
			$size	=	strlen($file_contents);
			return parent::formatBytes($size);
		} else {
			return false;
		}
	}

    private static function date(string $value, string $key): string {
        if (in_array($key, ['CreationDate', 'ModDate'])) {
            return parent::formatDate($value, 'Y-m-d H:i:s');
        } else {
            return $value;
        }
    }

    public static function details(string $pdfFile): array {
        $data       =   [];
        $parser     =   new Parser;
        $pdf        =   $parser->parseFile($pdfFile);

        foreach ($pdf->getDetails() as $key => $value) {
            $data[$key] = self::date($value, $key);
        }

        return $data;
    }

	public static function checksum(string $pdfFile): null|array {
		$content			=	file_get_contents($pdfFile);
	
		if ($content !== false) {
			return [
				'md5'		=>	hash('md5', $content),
				'sha1'		=>	hash('sha1', $content),
				'sha256'	=>	hash('sha256', $content),
			];
		} else {
			return null;
		}
	}

	public static function thumbnail(string $url): string {
		Dotenv::createImmutable('./')->load();
		return $_ENV['URL_PDF_THUMB'] . $url;
	}

}
