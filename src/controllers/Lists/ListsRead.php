<?php

namespace Monlib\Controllers\Lists;

use Monlib\Utils\Pdf;
use Monlib\Utils\File;
use Monlib\Utils\Misc;

use Monlib\Models\ORM;
use Monlib\Http\Response;

use Dotenv\Dotenv;

class ListsRead extends Response {

	protected ORM $orm;
	private array $fields;
	protected string $path;
	private string $list_id;
	private string $username;
	protected Dotenv $dotenv;

	private function rawUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->username . "/" . $this->list_id . "/raw";
	}

	private function getCli(): string {
		return "paimon -r @" . $this->username . "/" . $this->list_id;
	} 

	private function pageUrl(): string {
		return $_ENV['URL_ROOT'] . "/" . $this->username . "/" . $this->list_id;
	}

	private function inspectUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->username . "/" . $this->list_id . "/inspect";
	}

	private function lineContainsIgnore($line): bool {
		$position = strpos($line, '!ignore');
		
		if ($position !== false) {
			return true;
		} else {
			return false;
		}
	}

	public function __construct(string $username, string $list_id, string $table = 'lists') {
		$this->dotenv	=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->list_id	=	$list_id;
		$this->username	=	$username;

		$this->orm		=	new ORM($table);
		$this->path		=	$_ENV['STORAGE_PATH'];

		$this->fields	=	[
			'slug', 'title', 'item_id', 'user_id', 'privacy', 'added_in', 'updated_in', 'total_access', 'total_downloads'
		];
	}

	public function get() {
		$query			=	$this->orm->select([
			'user_id'	=>	$this->username,
			'slug'		=>	$this->list_id,
		], $this->fields);

		if ($query != null) {
			foreach ($query[0] as $key => $value) { $data[$key]	= $value; }

			$data['cmd']	=	$this->getCli();

			$data['url']	=	[
				'raw'		=>	$this->rawUrl(),
				'page'		=>	$this->pageUrl(),
				'inspect'	=>	$this->inspectUrl(),
			];
			
			$this->setHttpCode(200);
			echo json_encode([
				"success" 	=>	true,
				"data"		=>	$data
			]);
		} else {
			$this->setHttpCode(404);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'List not found'
			]);
		}
	}

	public function raw() {
		$query			=	$this->orm->select([
			'user_id'	=>	$this->username,
			'slug'		=>	$this->list_id,
		], [ 'list_file' ]);

		if ($query != null) {
			$this->setHttpCode(200);

			echo nl2br(
				File::get($this->path . $query[0]['list_file'])
			);
		} else {
			$this->setHttpCode(404);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'List not found'
			]);
		}
	}

	public function inspect() {
		$query			=	$this->orm->select([
			'slug'		=>	$this->list_id,
			'user_id'	=>	$this->username,
		], [ 'list_file', 'user_id', 'title' ]);

		if ($query != null) {
			$file_path	=	$this->path . $query[0]['list_file'];

			if (file_exists($file_path)) {
				$total	=	0;
				$file	=	fopen($file_path, 'r');

				if ($file) {
					while (($line = fgets($file)) !== false) {
						if (preg_match('/\bhttps?:\/\/\S+\b/', $line)) {
							$pdfFile		=	Misc::getUrl($line);

							$pdf_files[]	=	[
								'url'		=>	$pdfFile,
								'metadata'	=>	Pdf::details($pdfFile),
								'name'		=> 	Pdf::urlFileName($pdfFile),
								'size'		=>	Pdf::urlFileSize($pdfFile),
								'ignore'	=>	$this->lineContainsIgnore($line) ? true : false,
							];

							$total++;
						}
					}

					fclose($file);
					
					$this->setHttpCode(200);
					echo json_encode([
						'success'		=>	true,
						'total_links'	=>	$total,
						'pdf_files'		=>	$pdf_files,
						'title'			=>	$query[0]['title'],
						'author'		=>	$query[0]['user_id'],
						'dataset_size'	=>	Misc::formatBytes(File::size($file_path))
					]);
				} else {
					$this->setHttpCode(500);
					echo json_encode([
						'success'	=>	false,
						'message'	=>	'Could not open the file'
					]);
				}
			} else {
				$this->setHttpCode(404);
				echo json_encode([
					'success'	=>	false,
					'message'	=>	'File list not found'
				]);
			}

			$this->setHttpCode(200);
		} else {
			$this->setHttpCode(404);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'List not found'
			]);
		}
	}

}
