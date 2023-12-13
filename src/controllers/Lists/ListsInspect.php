<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Utils\{Pdf, File, Misc};
use Monlib\Http\{Response, Callback};
use Monlib\Controllers\Account\Login;
use Monlib\Controllers\User\{User, ApiKey};

use Dotenv\Dotenv;

class ListsInspect extends Response {

	protected ORM $orm;
	protected User $user;
	protected Login $login;
	protected string $path;
	protected string $listID;
	protected ApiKey $apiKey;
	protected string $username;
	protected array $queryData;
	protected Callback $callback;

	public function __construct(string $username, string $listID, string $table = 'lists') {
		Dotenv::createImmutable('./')->load();

		$this->user			=	new User;
		$this->login		=	new Login;
		$this->apiKey		=	new ApiKey;
		$this->callback		=	new Callback;
		$this->orm			=	new ORM($table);
		$this->path			=	$_ENV['STORAGE_LISTS_PATH'];

		$this->listID		=	$listID;
		$this->username		=	$this->user->getUserIdByUsername($username);

		$this->queryData	=	$this->orm->select([
			'slug'			=>	$this->listID,
			'user_id'		=>	$this->username,
		], [ 'list_file', 'user_id', 'title', 'privacy' ]);
	}

	public function inspect() {
		if (!in_array($this->username, ['', null, false])) {
			$apiKey			=	$this->callback->getApiKey();

			if ($this->queryData != null) {
				$listFiles	=	$this->getListFileLinks();
				$total		=	$this->getListFileTotalLinks();
				$path		=	$this->path . $this->queryData[0]['list_file'];

				if (fopen($path, 'r')) {
					foreach ($listFiles as $key => $value) {
						$pdfFiles[]		=	[
							'url'		=>	$value['url'],
							'ignore'	=>	$value['ignore'],
							'metadata'	=>	Pdf::details($value['url']),
							'thumbnail'	=>	Pdf::thumbnail($value['url']),
							'name'		=> 	Pdf::urlFileName($value['url']),
							'size'		=>	Pdf::urlFileSize($value['url']),
						];
					}

					$listUserID	=	$this->queryData[0]['user_id'];
					$userID		=	isset($apiKey) ? $this->apiKey->getUserID($apiKey) : $this->login->getUserID();

					$listData	=	[
						'success'		=>	true,
						'total_links'	=>	$total,
						'pdf_files'		=>	$pdfFiles,
						'title'			=>	$this->queryData[0]['title'],
						'dataset_size'	=>	Misc::formatBytes(File::size($path)),
						'author'		=>	$this->user->getUsernameByUserId($this->queryData[0]['user_id']),
					];

					if ($this->queryData[0]['privacy'] == "private") {
						if ($listUserID == $userID) {
							$this->setHttpCode(200);
							echo json_encode($listData);
						} else {
							$this->setHttpCode(403);
							echo json_encode([
								'success'	=>	false,
								'message'	=>	'Error: List is private'
							]);
						}
					} else {
						$this->setHttpCode(200);
						echo json_encode($listData);
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

	public function getListFileLinks(): array|bool {
		$path	=	$this->path . $this->queryData[0]['list_file'];

		if (file_exists($path)) {
			$pdfFiles	=	[];
			$file		=	fopen($path, 'r');

			if ($file) {
				while (($line = fgets($file)) !== false) {
					if (Misc::hasUrl($line)) {
						$pdfFiles[]		=	[
							'url'		=>	Misc::getUrl($line),
							'ignore'	=>	Misc::lineContainsIgnore($line) ? true : false
						];
					}
				}

				fclose($file);
				return $pdfFiles;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getListFileTotalLinks(): int|bool {
		$path	=	$this->path . $this->queryData[0]['list_file'];

		if (file_exists($path)) {
			$total		=	0;
			$file		=	fopen($path, 'r');

			if ($file) {
				while (($line = fgets($file)) !== false) {
					if (Misc::hasUrl($line)) {
						$total++;
					}
				}

				fclose($file);
				return $total;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getListFileLinksProfile(string $file): array|bool {
		$path	=	$this->path . $file;

		if (file_exists($path)) {
			$pdfFiles	=	[];
			$file		=	fopen($path, 'r');

			if ($file) {
				while (($line = fgets($file)) !== false) {
					if (Misc::hasUrl($line)) {
						$pdfFiles[]		=	[
							'url'		=>	Misc::getUrl($line),
							'ignore'	=>	Misc::lineContainsIgnore($line) ? true : false
						];
					}
				}

				fclose($file);
				return $pdfFiles;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getListFileTotalLinksProfile(string $file): int|bool {
		$path	=	$this->path . $file;

		if (file_exists($path)) {
			$total		=	0;
			$file		=	fopen($path, 'r');

			if ($file) {
				while (($line = fgets($file)) !== false) {
					if (Misc::hasUrl($line)) {
						$total++;
					}
				}

				fclose($file);
				return $total;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

}
