<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Utils\{Pdf, Misc};
use Monlib\Http\{Response, Callback};
use Monlib\Controllers\Account\Login;
use Monlib\Controllers\User\{User, ApiKey};

use Dotenv\Dotenv;
use ZipStream\ZipStream;

class ListsDownload extends Response {

	protected ORM $orm;
	protected User $user;
	protected Login $login;
	protected string $path;
	protected array $fields;
	protected string $listID;
	protected ApiKey $apiKey;
	protected string $username;
	protected Callback $callback;

	private function makeZipAndDownload(string $path, string $title, string $noIgnore = null) {
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

						if (!isset($noIgnore) || $noIgnore == null) {
							if (Misc::lineContainsIgnore($line)) {
								$zip->addFile(
									data: Pdf::urlFileContent($pdfFile),
									fileName: Pdf::urlFileName($pdfFile),
								);
							}
						} else if ($noIgnore == 'true') {
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
		Dotenv::createImmutable('./')->load();

		$this->user			=	new User;
		$this->login		=	new Login;
		$this->apiKey		=	new ApiKey;
		$this->callback		=	new Callback;
		$this->orm			=	new ORM($table);

		$this->listID		=	$listID;
		$this->path			=	$_ENV['STORAGE_LISTS_PATH'];
		$this->username		=	$this->user->getUserIdByUsername($username);
	}

	public function makeDownload(string|null $noIgnore) {
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
						$this->makeZipAndDownload($path, $query[0]['title'], $noIgnore);
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
					$this->makeZipAndDownload($path, $query[0]['title'], $noIgnore);
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
