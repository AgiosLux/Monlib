<?php

namespace Monlib\Controllers\Login;

use Monlib\Models\ORM;
use Monlib\Http\Response;

class ResetPassword extends Response {
    
    protected ORM $orm;

	public function __construct(string $table = 'users') {
		$this->orm		=	new ORM($table);
	}

}
