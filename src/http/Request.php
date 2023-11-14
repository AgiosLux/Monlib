<?php

namespace Monlib\Http;

class Request {

	private string $uri;
	private string $httpMethod;
	private array $headers = [];
	private array $postVars = [];
	private array $queryParams = [];

	public function __construct() {
		$this->queryParams	=	$_GET ?? [];
		$this->postVars	=	$_POST ?? [];
		$this->headers		=	getallheaders();
		$this->uri			=	$_SERVER['REQUEST_URI'] ?? '';
		$this->httpMethod	=	$_SERVER["REQUEST_METHOD"] ?? '';
	}

	public function getUri() { return $this->uri; }

	public function getHeaders() { return $this->headers; }

	public function getPostParams() { return $this->postVars; }

	public function getHttpMethod() { return $this->httpMethod; }

	public function getQueryParams() { return $this->queryParams; }

}
