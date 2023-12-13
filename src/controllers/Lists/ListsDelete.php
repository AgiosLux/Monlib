<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Utils\File;
use Monlib\Controllers\Account\Login;
use Monlib\Http\{Response, Callback};
use Monlib\Controllers\User\{User, ApiKey};

use Dotenv\Dotenv;

class ListsDelete extends Response {
	
	protected ORM $orm;
	protected User $user;
	protected Login $login;
	protected string $path;
	protected array $query;
	protected string $listID;
	protected ApiKey $apiKey;
	protected string $username;
	protected Callback $callback;

	public function __construct(string $username, string $listID, string $table = 'lists') {
		Dotenv::createImmutable('./')->load();

		$this->user		=	new User;
		$this->login	=	new Login;
		$this->apiKey	=	new ApiKey;
		$this->callback	=	new Callback;
		$this->orm		=	new ORM($table);

		$this->listID	=	$listID;
		$this->path		=	$_ENV['STORAGE_PATH'];
		$this->username	=	$this->user->getUserIdByUsername($username);

		$this->query	=	$this->orm->select([
			'slug'		=>	$this->listID,
			'user_id'	=>	$this->username,
		], [
			'list_file', 'user_id', 'item_id'
		]);
	}

	public function delete() {
		if ($this->query != null) {
			if ($this->query[0]['user_id'] == $this->login->getUserID()) {
				if (File::delete($this->path . $this->query[0]['list_file'])) {
					$delete			=	$this->orm->delete([
						'item_id'	=>	$this->query[0]['item_id'],
						'user_id'	=>	$this->query[0]['user_id'],
					]);

					if ($delete != null) {
						$this->setHttpCode(200);
						echo json_encode([
							'success'	=>	true,
							'message'	=>	'List deleted successfully'
						]);
					} else {
						$this->setHttpCode(500);
						echo json_encode([
							'success'	=>	false,
							'message'	=>	'Error: A server error occurred while deleting the list'
						]);
					}
				} else {
					$this->setHttpCode(404);
					echo json_encode([
						'success'	=>	false,
						'message'	=>	'Error: List file not found (xxx)'
					]);
				}
			} else {
				$this->setHttpCode(403);
				echo json_encode([
					'success'	=>	false,
					'message'	=>	'Error: You are not the owner of this list'
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

}
