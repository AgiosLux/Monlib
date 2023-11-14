<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;

class ListList {

	protected string $path;
	protected ORM $orm;

	public function __construct(string $table = 'lists') {
		$this->orm		=	new ORM($table);
	}

}
