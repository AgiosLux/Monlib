<?php

namespace Monlib\Controllers\Login;

use Monlib\Models\ORM;

class ResetPassword {
    
    protected ORM $orm;

	public function __construct(string $table = 'users') {
		$this->orm		=	new ORM($table);
	}

}
