<?php

namespace Monlib\Http;

class Response {

	private int $httpCode = 200;

	private array $headers = [];

	private string $contentType = 'text/html';

	private $content;

	public function __construct(int $httpCode, $content, string $contentType = 'text/html') {
		$this->content		=	$content;
		$this->httpCode		=	$httpCode;

		$this->setContentType($contentType);
	}

	public function setContentType(string $contentType) {
		$this->contentType  =   $contentType;
		$this->setHeader('Content-Type', $contentType);
	}

	public function getHttpCode(): int { return $this->httpCode; }

	public function getContent(): string { return $this->content; }

	public function getContentType(): string { return $this->contentType; }

	public function getHeaders(): array { return $this->headers; }

	public function setContent(string $content) { $this->content = $content; }

	public function setHeader(string $key, string $value) { $this->headers[$key] = $value; }

	public function setHttpCode(int $httpCode) {
		$this->httpCode	=	$httpCode;
		http_response_code($this->httpCode);
	}

	public function sendHeaders() {
		http_response_code($this->httpCode);
		foreach ($this->headers as $key => $value) { header($key . ':' . $value); }
	}

	public function sendResponse() {
		$this->sendHeaders();

		switch ($this->contentType) {
			case 'text/html':
				echo $this->content;
				break;
		}
	}

}
