<?php

namespace Monlib\Controllers\Tools;

use Monlib\Http\Response;

use Monlib\Utils\Pdf;
use Monlib\Utils\Misc;
use Monlib\Utils\Validate;

class Inspect extends Response {

	protected string $url;

	public function __construct(string $url) {
		$this->url		=	$url;
	}

	public function getData(): void {
		if (Validate::url($this->url) && Validate::pdfFile($this->url)) {
			$pdfFile			=	Misc::getUrl($this->url);

			$this->setHttpCode(200);
			echo json_encode([
				'success'		=>	true,
				'data'			=>	[
					'url'		=>	$pdfFile,
					'metadata'	=>	Pdf::details($pdfFile),
					'checksum'	=>	Pdf::checksum($pdfFile),
					'thumbnail'	=>	Pdf::thumbnail($pdfFile),
					'name'		=> 	Pdf::urlFileName($pdfFile),
					'size'		=>	Pdf::urlFileSize($pdfFile),
				],
			]);
		} else {
			$this->setHttpCode(500);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'Error: PDF file could not be read',
			]);
		}
	}

}
