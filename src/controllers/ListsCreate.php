<?php

namespace Monlib\Controllers;

use Monlib\Utils\Generate;

use Monlib\Models\ORM;
use Monlib\Models\Database;

use PDO;
use Dotenv\Dotenv;

class ListsCreate {

	protected string $path;
	protected string $table;
	protected PDO $pdo;
	protected Database $database;
	protected Dotenv $dotenv;
	protected ORM $orm;

	private function createUniqueSlug(string $get_slug, string $user_id): string {
		$i      =   1;
		$slug   =   Generate::slugify($get_slug);
	
		while (true) {
			$candidateSlug  =    $i > 1 ? "$slug-$i" : $slug;

			$query			=	$this->orm->select([
				'user_id'	=>	$user_id,
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
		$this->database =   new Database;
		$this->dotenv	=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->table    =   $table;
		$this->path		=	$_ENV['STORAGE_PATH'];
		$this->orm		=	new ORM($this->table);
	}

	public function uploadAndCreate(): void {
		if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
			$fileData   =   $_FILES['file'];
			$extFile    =   pathinfo($fileData['name'], PATHINFO_EXTENSION);
			$fileName   =   Generate::generateRandomString(36) . '.' . $extFile;
			$slug       =   $this->createUniqueSlug($_POST['title'], $_POST['user_id']);

			if (in_array($extFile, ['txt', 'mon'])) {
				$dest	=	$this->path . '/' . $fileName;
		
				if (move_uploaded_file($fileData['tmp_name'], $dest)) {
					$inserData    	=   $this->orm->create([
						'slug'      =>  $slug,
						'list_file' =>  $fileName,
						'title'     =>  $_POST['title'],
						'user_id'   =>  $_POST['user_id'],
						'added_in'  =>  date('Y-m-d H:i:s'),
						'item_id'   =>  Generate::generateRandomString(36),
						'privacy'   =>  $_POST['privacy'] ? $_POST['privacy'] : 'public',
					]);
		
					if ($inserData !== false) {
						$response = ['success' => true, 'message' => 'Created successfully.'];
					} else {
						$response = ['success' => false, 'message' => 'Error saving to the database.'];
					}
				} else {
					$response = ['success' => false, 'message' => 'Error moving the file to the destination.'];
				}
			} else {
				$response = ['success' => false, 'message' => 'Invalid format error.'];
			}
		} else {
			$response = ['success' => false, 'message' => 'File not found in the request data.'];
		}
	
		echo json_encode($response);
	}

}
