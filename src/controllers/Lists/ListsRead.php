<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Utils\{Pdf, File, Misc};
use Monlib\Http\{Response, Callback};
use Monlib\Controllers\Account\Login;
use Monlib\Controllers\Lists\ListsStats;
use Monlib\Controllers\User\{User, ApiKey};

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\{QRCode, QROptions};

use Dotenv\Dotenv;
use ZipStream\ZipStream;

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
	protected ListsStats $listsStats;

	private function rawUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/raw";
	}

	private function getCli(): string {
		return "paimon -r @" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID;
	}

	private function pageUrl(): string {
		return $_ENV['URL_ROOT'] . "/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID;
	}

	private function statsUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/stats";
	}

	private function qrCodeUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/qrcode";
	}

	private function inspectUrl(): string {
		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/inspect";
	}

	private function lineContainsIgnore(string $line): bool {
		$position = strpos($line, '!ignore');
		
		if ($position !== false) {
			return true;
		} else {
			return false;
		}
	}

	private function downloadUrl(bool $no_ignore = false): string {
		if ($no_ignore) {
			return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/download?no-ignore=true";
		}

		return $_ENV['URL_ROOT'] . "/api/lists/" . $this->user->getUsernameByUserId($this->username) . "/" . $this->listID . "/download";
	}

	private function runDownloadPdfFiles(string $path, string $title, string $no_ignore = null) {
		if (file_exists($path)) {
			$file	=	fopen($path, 'r');

			$zip	=	new ZipStream(
				sendHttpHeaders: true,
				outputName: "$title.zip",
			);

			if ($file) {
				while (($line = fgets($file)) !== false) {
					if (Misc::hasUrl($line)) {
						$pdfFile		=	Misc::getUrl($line);

						if (!isset($no_ignore) || $no_ignore == null) {
							if ($this->lineContainsIgnore($line)) {
								$zip->addFile(
									data: Pdf::urlFileContent($pdfFile),
									fileName: Pdf::urlFileName($pdfFile),
								);
							}
						} else if ($no_ignore == 'true') {
							$zip->addFile(
								data: Pdf::urlFileContent($pdfFile),
								fileName: Pdf::urlFileName($pdfFile),
							);
						}
					}
				}
			}

			fclose($file);
			$zip->finish();
		}
	}

	public function __construct(string $username, string $listID, string $table = 'lists') {
		$this->dotenv		=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->user			=	new User;
		$this->login		=	new Login;
		$this->apiKey		=	new ApiKey;
		$this->callback		=	new Callback;
		$this->orm			=	new ORM($table);
		$this->path			=	$_ENV['STORAGE_PATH'];

		$this->listID		=	$listID;
		$this->username		=	$this->user->getUserIdByUsername($username);
		$this->listsStats	=	new ListsStats($this->username, $this->listID);

		$this->fields		=	[
			'slug', 'title', 'item_id', 'user_id', 'mode', 'privacy', 'added_in', 'updated_in', 'user_id'
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

				$data['url']		=	[
					'raw'			=>	$this->rawUrl(),
					'page'			=>	$this->pageUrl(),
					'stats'			=>	$this->statsUrl(),
					'qrcode'		=>	$this->qrCodeUrl(),
					'inspect'		=>	$this->inspectUrl(),
				];

				$data['download']	=	[
					'cli'			=>	$this->getCli(),
					'main'			=>	$this->downloadUrl(),
					'no_ignore'		=>	$this->downloadUrl(true),
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
			$apiKey			=	$this->callback->getApiKey() ? $this->callback->getApiKey() : null;
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
				} else {
					$this->setHttpCode(200);

					if ($apiKey != null) {
						if ($listUserID != $userID) {
							$this->listsStats->addRawDownloadCount();
						}
					}

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

	public function qrCode() {
		$options 				=	new QROptions([
			'version'			=>	7,
			'circleRadius'		=>	0.4,
			'outputBase64'		=>	false,
			'eccLevel'			=>	EccLevel::H,
			'outputInterface'	=>	\QRGdRounded::class,
		]);
		
		header("Content-Type: image/svg+xml");
		echo (new QRCode($options))->render(
			$this->pageUrl()
		);
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
							if (Misc::hasUrl($line)) {
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
						} else {
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
							'message'	=>	'Error: Could not open the file'
						]);
					}
				} else {
					$this->setHttpCode(404);
					echo json_encode([
						'success'	=>	false,
						'message'	=>	'Error: File list not found'
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

	public function download(string|null $no_ignore) {
		if (!in_array($this->username, ['', null, false])) {
			$apiKey			=	$this->callback->getApiKey();
			$query			=	$this->orm->select([
				'slug'		=>	$this->listID,
				'user_id'	=>	$this->username,
			], [ 'list_file', 'user_id', 'title', 'privacy' ]);

			if ($query != null) {
				$listUserID	=	$query[0]['user_id'];
				$path		=	$this->path . $query[0]['list_file'];

				if (isset($apiKey)) {
					$userID		=	$this->apiKey->getUserID($apiKey);
				} else {
					$userID		=	$this->login->getUserID();
				}

				if ($query[0]['privacy'] == "private") {
					if ($listUserID == $userID) {
						$this->setHttpCode(200);
						header("Content-type: application/x-zip");
						$this->runDownloadPdfFiles($path, $query[0]['title'], $no_ignore);
					} else {
						$this->setHttpCode(403);
						echo json_encode([
							'success'	=>	false,
							'message'	=>	'Error: List is private'
						]);
					}
				} else {
					$this->setHttpCode(200);
					header("Content-type: application/x-zip");
					$this->runDownloadPdfFiles($path, $query[0]['title'], $no_ignore);
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

}
