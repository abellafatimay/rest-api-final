<?php

namespace Requests\Request;
use Requests\RequestInterface\RequestInterface;

class Request implements RequestInterface {
    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getPath(): string {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Ensure that the root path is represented as '/' and not an empty string
        return $path === '' ? '/' : rtrim($path, '/');
    }

    public function getBody(): array {
        if ($this->getMethod() === 'GET') {
            return $_GET;
        }
        if ($this->getMethod() === 'POST') {
            // Handle JSON body for POST
            if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $jsonBody = file_get_contents('php://input');
                $data = json_decode($jsonBody, true);
                return is_array($data) ? $data : [];
            }
            return $_POST; // Fallback to form data
        }
        // For PUT, DELETE, etc., handle JSON body
        if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $jsonBody = file_get_contents('php://input');
            $data = json_decode($jsonBody, true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    public function getHeaders(): array {
        return getallheaders(); // Requires Apache or specific server config
    }

    public function getHeader(string $name): ?string {
        $headers = $this->getHeaders();
        return $headers[$name] ?? null;
    }
}