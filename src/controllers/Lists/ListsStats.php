<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Http\Response;
use Monlib\Controllers\User\User;

class ListsStats extends Response {

	protected ORM $orm;
	protected User $user;
	protected int $username;
	protected string $listID;

	public function __construct(string $username, string $listID, string $table = 'lists') {
		$this->user		=	new User;
		$this->orm		=	new ORM($table);

		$this->listID	=	$listID;
		$this->username	=	$this->user->getUserIdByUsername($username);
	}

	public function addAccessCount(): bool {
		$conditions		=	[
			'slug'		=>	$this->listID,
			'user_id'	=>	$this->username,
		];

		$query			=	$this->orm->select($conditions, [
			'total_access'
		]);

		if ($query != null) {
			$editData				=	$this->orm->update([
				"total_access"	=>	$query[0]['total_access'] += 1
			], $conditions);

			if ($editData != null) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function addDownloadCount(): bool {
		$conditions		=	[
			'slug'		=>	$this->listID,
			'user_id'	=>	$this->username,
		];

		$query			=	$this->orm->select($conditions, [
			'total_downloads'
		]);

		if ($query != null) {
			$editData				=	$this->orm->update([
				"total_downloads"	=>	$query[0]['total_downloads'] += 1
			], $conditions);

			if ($editData != null) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getStats(string|null $action) {
		switch ($action) {
			case 'addDownload':
				$this->addDownloadCount();
				break;

			case 'addAccess':
				$this->addAccessCount();
				break;
			
			default:
				break;
		}

		$query			=	$this->orm->select([
			'slug'		=>	$this->listID,
			'user_id'	=>	$this->username,
		], [
			'total_access', 'total_downloads'
		]);

		if ($query != null) {
			$this->setHttpCode(200);
			echo json_encode([
				'success'				=>	true,
				'data'					=>	[
					'total_access'		=>	$query[0]['total_access'],
					'total_downloads'	=>	$query[0]['total_downloads'],
				]
			]);
		} else {
			$this->setHttpCode(404);
			echo json_encode([
				'success'	=>	false,
				'message'	=>	'Error: List not found'
			]);
		}
	}
	
}
