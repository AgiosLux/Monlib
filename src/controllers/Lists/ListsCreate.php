<?php

namespace Monlib\Controllers\Lists;

use Monlib\Http\Response;

use Monlib\Models\ORM;

use Monlib\Utils\File;
use Monlib\Utils\Generate;

use Dotenv\Dotenv;

class ListsCreate extends Response {

	protected ORM $orm;
	protected string $url;
	protected string $path;
	protected Dotenv $dotenv;

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

		$this->orm		=	new ORM($table);
		$this->url 		=	$_ENV['URL_ROOT'];
		$this->path		=	$_ENV['STORAGE_PATH'];
	}

	public function uploadAndCreate() {
		if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
			$fileData   =   $_FILES['file'];
			$extFile    =   File::ext($fileData['name']);
			$itemID		=	Generate::generateRandomString(36);
			$fileName   =   Generate::generateRandomString(36) . '.' . $extFile;
			$slug       =   $this->createUniqueSlug($_POST['title'], $_POST['user_id']);

			if (in_array($extFile, ['txt', 'mon'])) {
				$dest	=	$this->path . $fileName;
		
				if (File::move($fileData['tmp_name'], $dest)) {
					$inserData    	=	$this->orm->create([
						'slug'		=>	$slug,
						'item_id'   =>	$itemID,
						'list_file'	=>	$fileName,
						'title'     =>	$_POST['title'],
						'user_id'   =>	$_POST['user_id'],
						'added_in'  =>	date('Y-m-d H:i:s'),
						'privacy'   =>	$_POST['privacy'] ? $_POST['privacy'] : 'public',
					]);
		
					if ($inserData !== false) {
						$this->setHttpCode(200);
						$response 		=	[
							'success'	=>	true, 
							'message'	=>	'Created successfully.',
							'paimon'	=>	'paimon -r @' . $_POST['user_id'] . '/' . $slug,
							'url'		=>	$this->url . '/' . $_POST['user_id'] . '/' . $slug,
						];
					} else {
						$this->setHttpCode(500);
						$response = ['success' => false, 'message' => 'Error: saving to the database.'];
					}
				} else {
					$this->setHttpCode(500);
					$response = ['success' => false, 'message' => 'Error: moving the file to the destination.'];
				}
			} else {
				$this->setHttpCode(500);
				$response = ['success' => false, 'message' => 'Error: Invalid format error.'];
			}
		} else {
			$this->setHttpCode(500);
			$response = ['success' => false, 'message' => 'Error: File not found in the request data.'];
		}
	
		echo json_encode($response);
	}

}
