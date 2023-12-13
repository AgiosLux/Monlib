<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Utils\{File, Generate};
use Monlib\Controllers\Account\Login;
use Monlib\Http\{Response, Callback};
use Monlib\Controllers\User\{User, ApiKey};

use Dotenv\Dotenv;

class ListsCreate extends Response {

	protected ORM $orm;
	protected User $user;
	protected string $url;
	protected Login $login;
	protected string $path;
	protected Dotenv $dotenv;
	protected ApiKey $apiKey;
	protected Callback $callback;

	private function createUniqueSlug(string $slug, string $username): string {
		$i      =   1;
		$slug   =   Generate::slugify($slug);
	
		while (true) {
			$candidateSlug  =    $i > 1 ? "$slug-$i" : $slug;

			$query			=	$this->orm->select([
				'user_id'	=>	$username,
				'slug'		=>	$candidateSlug,
			]);
	
			if ($query != null) {
				$i++;
			} else {
				return $candidateSlug;
			}
		}
	}

	public function __construct(string $table = 'lists') {
		$this->dotenv	=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->user		=	new User;
		$this->login	=	new Login;
		$this->apiKey	=	new ApiKey;
		$this->callback	=	new Callback;
		$this->orm		=	new ORM($table);

		$this->url 		=	$_ENV['URL_ROOT'];
		$this->path		=	$_ENV['STORAGE_LISTS_PATH'];
	}

	public function uploadAndCreate() {
		if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
			$apiKey		=	$this->callback->getApiKey();

			if ($this->apiKey->isValid($apiKey) || $this->login->hasLogged()) {
				$fileData   =   $_FILES['file'];
				$extFile    =   File::ext($fileData['name']);
				$apiKey		=	$this->callback->getApiKey();
				$itemID		=	Generate::generateRandomString(36);
				$fileName   =   Generate::generateRandomString(36) . '.' . $extFile;
				$userMode	=	$this->apiKey->isValid($apiKey) ? 'apiKey' : 'userID';
				$userID		=	$this->apiKey->isValid($apiKey) ? $this->apiKey->getUserID($apiKey) : $this->login->getUserID();

				if (in_array($extFile, ['txt', 'mon'])) {
					$dest	=	$this->path . $fileName;
			
					if (File::move($fileData['tmp_name'], $dest)) {
						$slug       	=   $this->createUniqueSlug($_POST['title'], $userID);
						$insertData    	=	$this->orm->create([
							'slug'		=>	$slug,
							'item_id'   =>	$itemID,
							'user_id'   =>	$userID,
							'list_file'	=>	$fileName,
							'mode'		=>	$userMode,
							'title'     =>	$_POST['title'],
							'added_in'  =>	date('Y-m-d H:i:s'),
							'privacy'   =>	$_POST['privacy'] ? $_POST['privacy'] : 'public',
						]);
			
						if ($insertData !== false) {
							$username			=	$this->user->getUsernameByUserId($userID);

							$this->setHttpCode(200);

							$httpCodeError		=	200;
							$response 			=	[
								'success'		=>	true,
								'data'			=>	[
									'message'	=>	'Created successfully.',
									'paimon'	=>	"paimon -r @$username/$slug",
									'url'		=>	$this->url . '/' . $username . '/' . $slug,
								]
							];
						} else {
							$httpCodeError		=	500;
							$response			=	[
								'success'		=>	false, 
								'message'		=>	'Error: saving to the database.'
							];
						}
					} else {
						$httpCodeError	=	500;
						$response		=	[
							'success'	=>	false, 
							'message'	=>	'Error: moving the file to the destination.'
						];
					}
				} else {
					$httpCodeError	=	500;
					$response		=	[
						'success'	=>	false,
						'message'	=>	'Error: Invalid format error.'
					];
				}
			} else {
				$httpCodeError	=	401;
				$response		=	[
					'success'	=>	false, 
					'message'	=>	'Error: api key is invalid or user not logged.'
				];
			}
		} else {
			$httpCodeError	=	500;
			$response		=	[
				'success'	=>	false, 
				'message'	=>	'Error: File not found in the request data.'
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

}
