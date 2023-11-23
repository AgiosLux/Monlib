<?php

namespace Monlib\Controllers\Lists;

use Monlib\Utils\Pdf;
use Monlib\Utils\File;
use Monlib\Utils\Misc;
use Monlib\Models\ORM;
use Monlib\Http\Response;
use Monlib\Http\Callback;
use Monlib\Controllers\User\User;
use Monlib\Controllers\User\ApiKey;
use Monlib\Controllers\Account\Login;

use Dotenv\Dotenv;

class ListsRead extends Response {

	protected ORM $orm;
	protected User $user;
	protected Login $login;
	protected string $path;
	protected array $fields;
	protected string $listID;
	protected ApiKey $apiKey;
	protected Dotenv $dotenv;
	protected string $username;
	protected Callback $callback;

	private function rawUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/raw";
	}

	private function getCli(): string {
		return "paimon -r @" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID;
	}

	private function pageUrl(): string {
		return $_ENV['URL_ROOT'] . "/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID;
	}

	private function inspectUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/inspect";
	}

	private function lineContainsIgnore($line): bool {
		$position = strpos($line, '!ignore');
		
		if ($position !== false) {
			return true;
		} else {
			return false;
		}
	}

	public function __construct(string $username, string $listID, string $table = 'lists') {
		$this->dotenv	=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->user		=	new User;
		$this->login	=	new Login;
		$this->apiKey	=	new ApiKey;
		$this->callback	=	new Callback;
		$this->orm		=	new ORM($table);
		$this->path		=	$_ENV['STORAGE_PATH'];

		$this->listID	=	$listID;
		$this->username	=	$this->user->getUserIdByUsername($username);

		$this->fields	=	[
			'slug', 'title', 'item_id', 'user_id', 'mode', 'privacy', 'added_in', 'updated_in', 'user_id', 'total_access', 'total_downloads'
		];
	}

	public function get() {
		if (!in_array($this->username, ['', null, false])) {
			$apiKey			=	$this->callback->getApiKey();
			$query			=	$this->orm->select([
				'slug'		=>	$this->listID,
				'user_id'	=>	$this->username,
			], $this->fields);
	
			if ($query != null) {
				foreach ($query[0] as $key => $value) { $data[$key]	= $value; }
				
				$listUserID	=	$data['user_id'];

				if (isset($apiKey)) {
					$userID		=	$this->apiKey->getUserID($apiKey);
				} else {
					$userID		=	$this->login->getUserID();
				}

				unset($data['user_id']);

				$data['cmd']	=	$this->getCli();
				$data['url']	=	[
					'raw'		=>	$this->rawUrl(),
					'page'		=>	$this->pageUrl(),
					'inspect'	=>	$this->inspectUrl(),
				];
	
				if ($data["privacy"] == "private") {
					if ($listUserID == $userID) {
						$this->setHttpCode(200);
						echo json_encode([
							"success" 	=>	true,
							"data"		=>	$data
						]);
					} else {
						$this->setHttpCode(403);
						echo json_encode([
							'success'	=>	false,
							'message'	=>	'Error: List is private'
						]);
					}
				} else if ($data["privacy"] == "public") {
					$this->setHttpCode(200);
					echo json_encode([
						"success" 	=>	true,
						"data"		=>	$data
					]);
				}
			} else {
				$this->setHttpCode(404);
				echo json_encode([
					'success'	=>	false,
					'message'	=>	'Error: List not found'
				]);
			}
		} else {
			$this->setHttpCode(404);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'Error: List not found'
			]);
		}
	}

	public function raw() {
		if (!in_array($this->username, ['', null, false])) {
			$apiKey			=	$this->callback->getApiKey();
			$query			=	$this->orm->select([
				'slug'		=>	$this->listID,
				'user_id'	=>	$this->username,
			], [ 'list_file', 'privacy', 'user_id' ]);

			if ($query != null) {
				$listUserID	=	$query[0]['user_id'];

				if (isset($apiKey)) {
					$userID		=	$this->apiKey->getUserID($apiKey);
				} else {
					$userID		=	$this->login->getUserID();
				}

				if ($query[0]["privacy"] == "private") {
					if ($listUserID == $userID) {
						$this->setHttpCode(200);
						echo File::read($this->path . $query[0]['list_file']);
					} else {
						$this->setHttpCode(403);
						echo json_encode([
							'success'	=>	false,
							'message'	=>	'Error: List is private'
						]);
					}
				} else if ($query[0]["privacy"] == "public") {
					$this->setHttpCode(200);
					echo File::read($this->path . $query[0]['list_file']);
				}
			} else {
				$this->setHttpCode(404);
				echo json_encode([
					'success'	=>	false,
					'message'	=>	'Error: List not found'
				]);
			}
		} else {
			$this->setHttpCode(404);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'Error: List not found'
			]);
		}
	}

	public function inspect() {
		if (!in_array($this->username, ['', null, false])) {
			$apiKey			=	$this->callback->getApiKey();
			$query			=	$this->orm->select([
				'slug'		=>	$this->listID,
				'user_id'	=>	$this->username,
			], [ 'list_file', 'user_id', 'title', 'privacy' ]);

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
									'thumbnail'	=>	Pdf::thumbnail($pdfFile),
									'name'		=> 	Pdf::urlFileName($pdfFile),
									'size'		=>	Pdf::urlFileSize($pdfFile),
									'ignore'	=>	$this->lineContainsIgnore($line) ? true : false,
								];

								$total++;
							}
						}

						fclose($file);
						$listUserID	=	$query[0]['user_id'];
		
						if (isset($apiKey)) {
							$userID		=	$this->apiKey->getUserID($apiKey);
						} else {
							$userID		=	$this->login->getUserID();
						}

						if ($query[0]['privacy'] == "private") {
							if ($listUserID == $userID) {
								$this->setHttpCode(200);
								echo json_encode([
									'success'		=>	true,
									'total_links'	=>	$total,
									'pdf_files'		=>	$pdf_files,
									'title'			=>	$query[0]['title'],
									'dataset_size'	=>	Misc::formatBytes(File::size($file_path)),
									'author'		=>	$this->user->getUsernameByUserId($query[0]['user_id']),
								]);
							} else {
								$this->setHttpCode(403);
								echo json_encode([
									'success'	=>	false,
									'message'	=>	'Error: List is private'
								]);
							}
						} else if ($query[0]['privacy'] == 'public') {
							$this->setHttpCode(200);
							echo json_encode([
								'success'		=>	true,
								'total_links'	=>	$total,
								'pdf_files'		=>	$pdf_files,
								'title'			=>	$query[0]['title'],
								'dataset_size'	=>	Misc::formatBytes(File::size($file_path)),
								'author'		=>	$this->user->getUsernameByUserId($query[0]['user_id']),
							]);
						}
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
			} else {
				$this->setHttpCode(404);
				echo json_encode([
					'success'	=>	false,
					'message'	=>	'List not found'
				]);
			}
		} else {
			$this->setHttpCode(404);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'Error: List not found'
			]);
		}
	}

}
