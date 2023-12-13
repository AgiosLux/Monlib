<?php

namespace Monlib\Controllers\User;

use Monlib\Models\ORM;
use Monlib\Http\Response;

class Profile extends Response {
    
    protected ORM $orm;

	public function __construct(string $table = 'users') {
		$this->orm		=	new ORM($table);
	}

}
