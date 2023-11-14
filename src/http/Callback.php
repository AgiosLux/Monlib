<?php

namespace Monlib\Http;
use Monlib\Http\Request;

class Callback extends Request {

	public function getInputData(string $data = null) {
		$inputData	=	file_get_contents('php://input');
		$inputJson	=	json_decode($inputData, true);

		if ($data != null) {
			return $inputJson[$data];
		} else {
			return $inputJson;
		}
	}
	
	public function getApiKey(string $header = null): null|string {
		$headers		=	$this->getHeaders();

		if ($header) {
			return $headers[$header];
		} else {
			$apiKey		=	$headers['Authorization'] ? $headers['Authorization'] : $headers['authorization'];
			preg_match('/^Bearer\s+(.*?)$/', $apiKey, $matches);
			return $matches[1];
		}
	}

}
