<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Http\{Response, Callback};
use Monlib\Controllers\Account\Login;
use Monlib\Controllers\Lists\ListsMeta;
use Monlib\Controllers\User\{User, ApiKey};

class ListsList extends Response {

    protected ORM $orm;
    protected User $user;
    protected Login $login;
    protected ApiKey $apiKey;
    protected string $username;
    protected Callback $callback;
	protected ListsMeta $listsMeta;

    public function __construct(string $username, string $table = 'lists') {
        $this->user			=	new User;
        $this->login		=	new Login;
        $this->apiKey		=	new ApiKey;
        $this->callback		=	new Callback;

        $this->orm			=	new ORM($table);

        $this->username		=	$this->user->getUserIdByUsername($username);
		$this->listsMeta	=	new ListsMeta($username, null);
    }

    public function listAllLists(?string $privacy, ?int $offset = 0, ?int $limit = 50) {
        $data			=	[];
        $limit			=	$limit ?? 50;
        $offset			=	$offset ?? 0;
        $privacy		=	$privacy ?? 'public';

        $apiKey			=	$this->callback->getApiKey();
        $userID			=	$this->apiKey->getUserID($apiKey) ?? $this->login->getUserID();

        $conditions		=	[
            'privacy'	=>	$privacy,
            'user_id'	=>	$this->username,
        ];

        $query			=	$this->orm->select($conditions, [
            'slug', 'title', 'total_access', 'total_downloads', 'added_in', 'updated_in'
        ], $offset, $limit);

        foreach ($query as $key => $value) {
            $data[]					=	[
                'slug'				=>	$value['slug'],
                'title'				=>	$value['title'],
                'added_in'			=>	$value['added_in'],
				'last_updated'		=>	$value['updated_in'],
                'total_access'		=>	$value['total_access'],
                'total_downloads'	=>	$value['total_downloads'],
                'url'				=>	[
					'api'			=>	$this->listsMeta->apiUrl($value['slug']),
					'page'			=>	$this->listsMeta->pageUrl($value['slug']),
				],
            ];
        }

        if (!empty($query)) {
            if ($privacy == 'public') {
                $this->setHttpCode(200);
                echo json_encode([
                    'success' 	=>	true,
                    'data' => [
                        'lists'	=>	$data,
                        'total'	=>	$this->orm->count($conditions)
                    ]
                ]);
            } elseif ($privacy == 'private') {
                if ($this->username == $userID) {
                    $this->setHttpCode(200);
                    echo json_encode([
                        'success'	=>	true,
                        'data' => [
                            'lists'	=>	$data,
                            'total'	=>	$this->orm->count($conditions)
                        ]
                    ]);
                } else {
                    $this->setHttpCode(403);
                    echo json_encode([
                        'success'	=>	false,
                        'message'	=>	'Error: access denied'
                    ]);
                }
            }
        } else {
            $this->setHttpCode(404);
            echo json_encode([
                'success'	=>	false,
                'message'	=>	'Error: User not found'
            ]);
        }
    }

}
