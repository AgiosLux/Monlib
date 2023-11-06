<?php

namespace Monlib\Controllers\User;

use Monlib\Models\ORM;

class User {
    
    protected ORM $orm;

	public function __construct(string $table = 'user') {
		$this->orm		=	new ORM($table);
	}

}
