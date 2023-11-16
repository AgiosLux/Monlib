<?php

namespace Monlib\Controllers\User;

use Monlib\Models\ORM;
use Monlib\Http\Response;
use Monlib\Services\Crypto;

class User extends Response {
    
    protected ORM $orm;
	protected Crypto $crypto;

	public function __construct(string $table = 'users') {
		$this->crypto	=	new Crypto;
		$this->orm		=	new ORM($table);
	}

	public function checkUniqueEmail(string $email): bool {
		$query		=	$this->orm->select([
			'email'	=>	$email,
		]);

		if ($query == null) { return true; }
		return false;
	}

	public function checkUniqueUsername(string $username): bool {
		$query			=	$this->orm->select([
			'username'	=>	$username,
		]);

		if ($query == null) { return true; }
		return false;
	}

	public function verifyAvaliableUsername(): void { 
		$this->setHttpCode(200);

		if ($this->checkUniqueUsername($_POST['username'])) {
			echo json_encode([
				"avaliable"	=>	true
			]);
		} else {
			echo json_encode([
				"avaliable"	=>	false
			]);
		}
	}

	public function verifyAvaliableEmail(): void { 
		$this->setHttpCode(200);

		if ($this->checkUniqueEmail($_POST['email'])) {
			echo json_encode([
				"avaliable"	=>	true
			]);
		} else {
			echo json_encode([
				"avaliable"	=>	false
			]);
		}
	}

}
