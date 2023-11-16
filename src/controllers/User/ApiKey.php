<?php

namespace Monlib\Controllers\User;

use Monlib\Models\ORM;
use Monlib\Http\Response;
use Monlib\Utils\Generate;
use Monlib\Utils\Validate;
use Monlib\Services\Crypto;
use Monlib\Controllers\Account\Login;

class ApiKey extends Response {
    
    protected ORM $orm;
	protected Login $login;
	protected Crypto $crypto;

	private function createUniqueTitle(string $title): string {
		$i      =   1;
	
		while (true) {
			$candidateTitle  =    $i > 1 ? "$title ($i)" : $title;

			$query			=	$this->orm->select([
				'title'		=>	$candidateTitle,
				'user_id'	=>	$this->login->getUserID(),
			]);
	
			if ($query != null) {
				$i++;
			} else {
				return $candidateTitle;
			}
		}
	}

	private function checkHasUniqueKey(): string {
		$key			=	$this->crypto->encrypt(
			Generate::apiKey(32)
		);

		$query			=	$this->orm->select([
			'api_key'	=>	$key,
		]);

		if ($query != null) {
			return $this->crypto->encrypt(
				Generate::apiKey(32)
			);
		} else {
			return $key;
		}
	}
	
	public function __construct(string $table = 'api_keys') {
		$this->login	=	new Login;
		$this->crypto	=	new Crypto;
		$this->orm		=	new ORM($table);
	}

	public function isValid(string $key): bool {
		if (Validate::apiKey($key)) {
			$query			=	$this->orm->count([
				'status'	=>	'actived',
				'api_key'	=>	$this->crypto->encrypt($key),
			]);
	
			if ($query > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getUserID(string $key): int|bool {
		if ($this->isValid($key)) {
			$query			=	$this->orm->select([
				'api_key'	=>	$this->crypto->encrypt($key),
			], [ 'user_id' ])[0];

			return $query['user_id'];
		} else {
			return false;
		}
	}

	public function getKey(string $key) {
		if ($this->login->hasLogged()) {
			$query			=	$this->orm->select([
				'slug'		=>	$key,
				"user_id"	=>	$this->login->getUserID()
			], [ 'api_key', 'title', 'status', 'added_in', 'updated_in' ])[0];
	
			if ($query !== null) {
				$httpCodeError		=	200;
				$query['api_key']	=	$this->crypto->decrypt($query['api_key']);
				$response			=	[
					"success"		=>	true,
					"data"			=>	$query,
				];
			} else {
				$httpCodeError	=	404;
				$response		=	[
					'success'	=>	false, 
					'message'	=>	'Error: api key not found.'
				];
			}
		} else {
			$httpCodeError	=	401;
			$response		=	[
				"success"	=>	false,
				"message"	=>	"Error: User not logged in account",	
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

	public function listAllKeys() {
		if ($this->login->hasLogged()) {
			$data			=	[];
			$conditions		=	[
				"user_id"	=>	$this->login->getUserID()
			];

			$query			=	$this->orm->select($conditions, [
				'slug', 'title', 'status', 'added_in', 'updated_in'
			]);
	
			if ($query !== null) {
				foreach ($query as $key => $value) {
					$data[$key]		=	$value;
				}

				$httpCodeError		=	200;
				$response			=	[
					"success"		=>	true,
					"data"			=>	$data,
					"total"			=>	$this->orm->count($conditions)
				];
			} else {
				$httpCodeError	=	404;
				$response		=	[
					'success'	=>	false, 
					'message'	=>	'Error: api keys not found.'
				];
			}
		} else {
			$httpCodeError	=	401;
			$response		=	[
				"success"	=>	false,
				"message"	=>	"Error: User not logged in account",	
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

	public function generateNewKey() {
		if ($this->login->hasLogged()) {
			$key			=	$this->checkHasUniqueKey();
			$slug			=	Generate::generateRandomString(32);
			$title			=	$_POST["title"] ? $this->createUniqueTitle($_POST["title"]) : "API Key: " . Generate::generateRandomString(8);

			$insertData    	=	$this->orm->create([
				'api_key'	=>	$key,
				'slug'		=>	$slug,
				'title'		=>	$title,
				'user_id'	=>	$this->login->getUserID(),
			]);

			if ($insertData !== false) {
				$httpCodeError		=	201;
				$response			=	[
					"success"		=>	true,
					"data"			=>	[
						"slug"		=>	$slug,
						"title"		=>	$title,
						"apiKey"	=>	$this->crypto->decrypt($key),
					],
				];
			} else {
				$httpCodeError	=	500;
				$response		=	[
					'success'	=>	false, 
					'message'	=>	'Error: saving to the database.'
				];
			}
		} else {
			$httpCodeError	=	401;
			$response		=	[
				"success"	=>	false,
				"message"	=>	"Error: User not logged in account",	
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

	public function editKey(string $key) {
		if ($this->login->hasLogged()) {
			if ($_POST['title']) {
				$title				=	$this->createUniqueTitle($_POST["title"]);
				$conditions			=	[
					"slug"			=>	$key,
					"user_id"		=>	$this->login->getUserID()
				];
	
				$editData			=	$this->orm->update([
					"title"			=>	$title,
				], $conditions);
	
				if ($editData !== false) {
					$newQuery		=	$this->orm->select($conditions, [ 'status', 'title', 'updated_in' ])[0];
					$httpCodeError	=	200;
					$response		=	[
						"success"	=>	true,
						"data"		=>	$newQuery,
					];
				} else {
					$httpCodeError	=	500;
					$response		=	[
						'success'	=>	false, 
						'message'	=>	'Error: editing to the database.'
					];
				}
			} else {
				$httpCodeError	=	500;
				$response		=	[
					'success'	=>	false, 
					'message'	=>	'Error: fields is missing.'
				];
			}
		} else {
			$httpCodeError	=	401;
			$response		=	[
				"success"	=>	false,
				"message"	=>	"Error: User not logged in account",	
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

	public function changeStatusKey(string $key) {
		if ($this->login->hasLogged()) {
			$conditions		=	[
				"slug"		=>	$key,
				"user_id"	=>	$this->login->getUserID()
			];

			$query			=	$this->orm->select($conditions, [ 'status' ])[0];

			if ($query['status'] == 'actived') {
				$newStatus	=	'desactived';
			} else {
				$newStatus	=	'actived';
			}

			$editData		=	$this->orm->update([
				"status"	=>	$newStatus
			], $conditions);

			if ($editData !== false) {
				$newQuery		=	$this->orm->select($conditions, [ 'status', 'title', 'updated_in' ])[0];
				$httpCodeError	=	200;
				$response		=	[
					"success"	=>	true,
					"data"		=>	$newQuery,
				];
			} else {
				$httpCodeError	=	500;
				$response		=	[
					'success'	=>	false, 
					'message'	=>	'Error: updating status to the database.'
				];
			}
		} else {
			$httpCodeError	=	401;
			$response		=	[
				"success"	=>	false,
				"message"	=>	"Error: User not logged in account",	
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

	public function deleteKey(string $key) {
		if ($this->login->hasLogged()) {
			$deleteData		=	$this->orm->delete([
				"slug"		=>	$key,
				"user_id"	=>	$this->login->getUserID()
			]);

			if ($deleteData !== false) {
				$httpCodeError	=	200;
				$response		=	[
					"success"	=>	true,
					"message"	=>	"Api key has been deleted successfully"
				];
			} else {
				$httpCodeError	=	500;
				$response		=	[
					'success'	=>	false, 
					'message'	=>	'Error: deleting to the database.'
				];
			}
		} else {
			$httpCodeError	=	401;
			$response		=	[
				"success"	=>	false,
				"message"	=>	"Error: User not logged in account",	
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

}
