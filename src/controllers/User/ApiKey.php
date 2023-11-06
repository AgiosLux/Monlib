<?php

namespace Monlib\Controllers\User;

use Monlib\Models\ORM;

class Apikey extends User {
    
    protected ORM $orm;

	public function __construct(string $table = 'apis_key') {
		$this->orm		=	new ORM($table);
	}

}
