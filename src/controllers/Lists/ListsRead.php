<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Utils\{Pdf, File, Misc};
use Monlib\Http\{Response, Callback};
use Monlib\Controllers\Account\Login;
use Monlib\Controllers\User\{User, ApiKey};
use Monlib\Controllers\Lists\{ListsStats, ListsMeta};

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\{QRCode, QROptions};

use Dotenv\Dotenv;

class ListsRead extends Response {

	protected ORM $orm;
	protected User $user;
	protected Login $login;
	protected string $path;
	protected array $fields;
	protected string $listID;
	protected ApiKey $apiKey;
	protected string $username;
	protected Callback $callback;
	protected ListsMeta $listsMeta;
	protected ListsStats $listsStats;

	public function __construct(string $username, string $listID, string $table = 'lists') {
		Dotenv::createImmutable('./')->load();

		$this->user			=	new User;
		$this->login		=	new Login;
		$this->apiKey		=	new ApiKey;
		$this->callback		=	new Callback;
		$this->orm			=	new ORM($table);
		$this->path			=	$_ENV['STORAGE_PATH'];

		$this->listID		=	$listID;
		$this->username		=	$this->user->getUserIdByUsername($username);
		$this->listsMeta	=	new ListsMeta($this->username, $this->listID);
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
					'raw'			=>	$this->listsMeta->rawUrl(),
					'page'			=>	$this->listsMeta->pageUrl(),
					'stats'			=>	$this->listsMeta->statsUrl(),
					'qrcode'		=>	$this->listsMeta->qrCodeUrl(),
					'inspect'		=>	$this->listsMeta->inspectUrl(),
				];

				$data['download']	=	[
					'cli'			=>	$this->listsMeta->getCli(),
					'main'			=>	$this->listsMeta->downloadUrl(),
					'no_ignore'		=>	$this->listsMeta->downloadUrl(true),
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
			$this->listsMeta->pageUrl()
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
									'ignore'	=>	Misc::lineContainsIgnore($line) ? true : false,
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

}
