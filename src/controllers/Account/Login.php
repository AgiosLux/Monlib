<?php

namespace Monlib\Controllers\Account;

use Monlib\Models\ORM;
use Monlib\Http\Response;
use Monlib\Utils\Cookies;
use Monlib\Utils\Validate;
use Monlib\Utils\Generate;
use Monlib\Services\Crypto;

class Login extends Response {
    
	protected ORM $orm;
    protected Crypto $crypto;
	protected string $cookie;

	private function generateCookieValue(int $userID): string {
		return base64_encode(
			Generate::generateRandomString(32) . ':' . $userID
		);
	}

	public function __construct(string $table = 'users', string $cookie = 'userLogged') {
		$this->cookie	=	$cookie;
		
		$this->crypto	=	new Crypto;
		$this->orm		=	new ORM($table);
	}

	public function getUserID() {
		$cookieValue	=	Cookies::get($this->cookie);
		$cookieValue	=	base64_decode($cookieValue);
		return explode(':', $cookieValue)[1];
	}

	public function hasLogged(): bool {
		return Cookies::has($this->cookie);
	}

	public function loginAccount(): void {
		if (Validate::email($_POST['email'])) {
			$query			=	$this->orm->select([
				'email'		=>	$this->crypto->encrypt($_POST['email']),
			], [ 'id', 'password' ]);

			if (count($query) > 0) {
				if ($this->crypto->passwordVerify($_POST['password'], $query[0]['password'])) {
					Cookies::create($this->cookie, [
						'expire'	=>	time() + (86400 * 7),
						'value'		=>	$this->generateCookieValue($query[0]['id']),
					]);

					$httpCodeError	=	200;
					$response		=	[
						"success"	=>	true,
						"message"	=>	"Logged with succesfully.",
					];
				} else {
					$httpCodeError	=	401;
					$response		=	[
						"success"	=>	false,
						"message"	=>	"Error: Email or password is incorrect.",
					];
				}
			} else {
				$httpCodeError	=	401;
				$response		=	[
					"success"	=>	false,
					"message"	=>	"Error: Email or password is incorrect.",
				];
			}
		} else {
			$httpCodeError	=	422;
			$response		=	[
				'success'	=>	false,
				'message'	=>	"Error: The format of the provided email is invalid.",
			];
		}

		$this->setHttpCode($httpCodeError);
		echo json_encode($response);
	}

	public function doLogoff(): void {
		Cookies::delete($this->cookie);

		if (Cookies::has($this->cookie) != true) {
			$this->setHttpCode(200);
	
			echo json_encode([
				"success"	=>	true,
				"message"	=>	"Logoff with successfully.",
			]);
		}
	}

	public function checkUserLogged(): void {
		$this->setHttpCode(200);

		echo json_encode([
			"logged"	=>	$this->hasLogged(),
		]);
	}

}
