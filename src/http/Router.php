<?php

namespace Monlib\Http;

use Dotenv\Dotenv;

use Closure;
use Exception;
use ReflectionFunction;

class Router {

	protected string $url;
	protected array $routes;
	protected string $prefix;
	protected Dotenv $dotenv;
	protected Request $request;
	protected Response $response;

	private function getRoute() {
		$uri = $this->getUri();
		$httpMethod = $this->request->getHttpMethod();

		foreach ($this->routes as $patternRoute => $methods) {
			if (preg_match($patternRoute, $uri, $matches)) {
				if ($methods[$httpMethod]) {
					unset($matches[0]);

					$keys                                           =   $methods[$httpMethod]['variables'];
					$methods[$httpMethod]['variables']              =   array_combine($keys, $matches);
					$methods[$httpMethod]['variables']['request']   =   $this->request;

					return $methods[$httpMethod];
				}

				throw new Exception("Method not allowed", 405);
			}
		}

		throw new Exception("URL not found", 404);
	}

	private function setPrefix() {
		$parserUrl		=	parse_url($this->url);
		$this->prefix	=	$parserUrl['path'] ?? '';
	}

	private function addRoute(string $method, string $route, $params = []) {
		foreach ($params as $key => $value) {
			if ($value instanceof Closure) {
				$params['controller']			=	$value;
				unset($params[$key]);
				continue;
			}
		}

		$params['variables']					=	[];
		$patternVariable						=	'/{(.*?)}/';

		if (preg_match_all($patternVariable, $route, $matches)) {
			$route								=	preg_replace($patternVariable, '(.*?)', $route);
			$params['variables']				=	$matches[1];
		}

		$patternRoute							=	'#^' . preg_replace('#^/#', '\/', $route) . '$#';
		$this->routes[$patternRoute][$method]	=	$params;
	}

	private function getUri(): string {
		$uri			=	$this->request->getUri();
		$rem_queries	=	explode("?", $uri)[0];
		$xUri			=	strlen($this->prefix) ? explode($this->prefix, $rem_queries) : [$rem_queries];
		return end($xUri);
	}

	public function __construct(string $url) {
		$this->dotenv       =   Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->url          =   $url;
		$this->request      =   new Request;
		$this->response     =   new Response(200, '');

		$this->setPrefix();
	}

	public function get(string $route, $params = []) { $this->addRoute('GET', $route, $params); }

	public function post(string $route, $params = []) { $this->addRoute('POST', $route, $params); }

	public function put(string $route, $params = []) { $this->addRoute('PUT', $route, $params); }

	public function delete(string $route, $params = []) { $this->addRoute('DELETE', $route, $params); }

	public function run() {
		try {
			$route				=	$this->getRoute();

			if (!isset($route['controller'])) { throw new Exception('A URL could not be processed.', 500); }

			$args               =   [];
			$reflection         =   new ReflectionFunction($route['controller']);

			foreach ($reflection->getParameters() as $parameter) {
				$name           =   $parameter->getName();
				$args[$name]    =   $route['variables'][$name] ?? '';
			}

			return call_user_func_array($route['controller'], $args);
		} catch (Exception $e) {
			$errorResponse		=	new Response($e->getCode(), $e->getMessage(), 'text/html');
			$errorResponse->sendResponse();
		}
	}
	
}
