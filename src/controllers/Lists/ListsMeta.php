<?php

namespace Monlib\Controllers\Lists;

use Monlib\Http\Response;
use Monlib\Controllers\User\User;

use Dotenv\Dotenv;

class ListsMeta extends Response {

	protected User $user;
	protected string $listID;
	protected string $username;

	private function convertUserIDToUsername(string|int $username): string {
		return ctype_digit($username) ? $this->user->getUsernameByUserId($username) : $username;
	}

	public function getCli(string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		return "paimon -r @$username/$listID";
	}

	public function apiUrl(string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		return $_ENV['URL_ROOT'] . "/api/lists/" . $username . "/" . $listID . "/details";
	}

	public function rawUrl(string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		return $_ENV['URL_ROOT'] . "/api/lists/" . $username . "/" . $listID . "/raw";
	}

	public function pageUrl(string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		return $_ENV['URL_ROOT'] . "/" . $username . "/" . $listID;
	}

	public function statsUrl(string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		return $_ENV['URL_ROOT'] . "/api/lists/" . $username . "/" . $listID . "/stats";
	}

	public function qrCodeUrl(string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		return $_ENV['URL_ROOT'] . "/api/lists/" . $username . "/" . $listID . "/qrcode";
	}

	public function inspectUrl(string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		return $_ENV['URL_ROOT'] . "/api/lists/" . $username . "/" . $listID . "/inspect";
	}

	public function __construct(string $username, string|null $listID = null) {
		Dotenv::createImmutable('./')->load();

		$this->user			=	new User;

		if ($listID != null) {
			$this->listID	=	$listID;
		}

		$this->username		=	$username;
	}

	public function downloadUrl(bool $noIgnore = false, string|null $listID = null): string {
		$listID		=	$listID ?? $this->listID;
		$username	=	$this->convertUserIDToUsername($this->username);

		if ($noIgnore) {
			return $_ENV['URL_ROOT'] . "/api/lists/" . $username . "/" . $listID . "/download?no-ignore=true";
		}

		return $_ENV['URL_ROOT'] . "/api/lists/" . $username . "/" . $listID . "/download";
	}

}
