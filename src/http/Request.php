<?php

namespace Monlib\Http;

class Request {

	private string $uri;
	private array $queryParams = [];
	private array $posttVars = [];
	private array $headers = [];
	private string $httpMethod;

	public function __construct() {
		$this->queryParams	=	$_GET ?? [];
		$this->posttVars	=	$_POST ?? [];
		$this->headers		=	getallheaders();
		$this->uri			=	$_SERVER['REQUEST_URI'] ?? '';
		$this->httpMethod	=	$_SERVER["REQUEST_METHOD"] ?? '';
	}

	public function getHttpMethod() { return $this->httpMethod; }

	public function getUri() { return $this->uri; }

	public function getQueryParams() { return $this->queryParams; }

	public function getPostParams() { return $this->posttVars; }

	public function getHeaders() { return $this->headers; }

}
