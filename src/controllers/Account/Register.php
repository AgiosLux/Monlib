<?php

namespace Monlib\Controllers\Account;

use Monlib\Models\ORM;
use Monlib\Http\Response;
use Monlib\Utils\Validate;
use Monlib\Services\Crypto;
use Monlib\Controllers\User\User;

class Register extends Response {
    
    protected ORM $orm;
	protected User $user;
	protected Crypto $crypto;

	public function __construct(string $table = 'users') {
		$this->user		=	new User;
		$this->crypto	=	new Crypto;
		$this->orm		=	new ORM($table);
	}

	public function createAccount(): void {
		if (Validate::email($_POST['email'])) {
			$username	=	$_POST['username'];
			$email		=	$this->crypto->encrypt($_POST['email']);
			
			if ($this->user->checkUniqueEmail($email)) {
				if ($this->user->checkUniqueUsername($username)) {
					$insertData		=	$this->orm->create([
						'email'		=>	$email,
						'username'	=>	$username,
						'name'		=>	$_POST['name'],
						'password'	=>	$this->crypto->password($_POST['password']),
					]);
					
					if ($insertData !== false) {
						$httpCodeError	=	201;
						$response		=	[
							"success"	=>	true,
							"message"	=>	"Created with successully"
						];
					} else {
						$httpCodeError	=	500;
						$response		=	[
							"success"	=>	false,
							"message"	=>	"Error: Internal Server Error",
						];
					}
				} else {
					$httpCodeError	=	409;
					$response		=	[
						'success'	=>	false,
						'message'	=>	"Error: This username is already in use.",
					];
				}
			} else {
				$httpCodeError	=	409;
				$response		=	[
					'success'	=>	false,
					'message'	=>	"Error: This email is already in use.",
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

}
