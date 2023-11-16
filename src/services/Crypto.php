<?php

namespace Monlib\Services;

use Dotenv\Dotenv;

class Crypto {

	private $cipher, $key, $iv;
	private $payload	= 	[
		'threads'		=>	3,
		'time_cost'		=>	4, 
		'memory_cost'	=>	2048, 
	];

	public function __construct() {
		Dotenv::createImmutable('./')->load();

		$this->iv		=	$_ENV['OPENSSL_IV'];
		$this->key		=	$_ENV['OPENSSL_KEY'];
		$this->cipher	=	$_ENV['OPENSSL_ALGO'];
	}

	public function encrypt(string $data): string {
		$encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $this->iv);
		return base64_encode($encrypted);
	}

	public function decrypt(string $data): string {
		$decoded = base64_decode($data);
		return openssl_decrypt($decoded, $this->cipher, $this->key, 0, $this->iv);
	}

	public function password(string $password, array $payload = []): string {
		if ($payload == []) { $payload = $this->payload; }
		return password_hash($password, PASSWORD_ARGON2I, $payload);
	}

	public function passwordVerify(string $plain, string $encrypted): bool {
		return password_verify($plain, $encrypted);
	}

}
