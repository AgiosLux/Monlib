<?php

namespace Monlib\Controllers\User;

use Monlib\Models\ORM;

class Profile {
    
    protected ORM $orm;

	public function __construct(string $table = 'users') {
		$this->orm		=	new ORM($table);
	}

}
