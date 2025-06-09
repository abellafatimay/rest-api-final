<?php

namespace Requests\Request;
use Requests\RequestInterface\RequestInterface;

class Request implements RequestInterface {
    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getPath(): string 
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        error_log("Original path from REQUEST_URI: " . $path);
        
        // Always return '/' for empty or root paths
        if (empty($path) || $path === '/') {
            return '/';
        }
        
        // Remove trailing slash for non-root paths
        return rtrim($path, '/');
    }

    public function getUri(): string {
        return $_SERVER['REQUEST_URI'];
    }

    public function getBody(): array {
        if ($this->getMethod() === 'GET') {
            return $_GET;
        }
        if ($this->getMethod() === 'POST') {
            
            if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $jsonBody = file_get_contents('php://input');
                $data = json_decode($jsonBody, true);
                return is_array($data) ? $data : [];
            }
            return $_POST; 
        }
        if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $jsonBody = file_get_contents('php://input');
            $data = json_decode($jsonBody, true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    public function getHeaders(): array {
        return getallheaders();
    }

    public function getHeader(string $name): ?string {
        $headers = $this->getHeaders();
        return $headers[$name] ?? null;
    }

    /**
     * Check if the request method matches the given method
     *
     * @param string $method HTTP method to check (GET, POST, etc.)
     * @return bool True if the method matches
     */
    public function isMethod($method) {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }
    
    /**
     * Get a parameter from the request
     * 
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    public function getParam($name, $default = null) {
        if ($this->isMethod('GET')) {
            return $_GET[$name] ?? $default;
        } else if ($this->isMethod('POST')) {
            return $_POST[$name] ?? $default;
        }
        
        // For other methods, check the parsed request body
        $body = $this->getBody();
        return $body[$name] ?? $default;
    }

    public function get(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get a POST parameter from the request
     *
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    public function post($name = null, $default = null) {
        if ($name === null) {
            return $_POST;
        }
        return $_POST[$name] ?? $default;
    }

    /**
     * Get a file from the request (for file uploads)
     *
     * @param string $name File input name
     * @return array|null File array or null
     */
    public function file($name = null) {
        if ($name === null) {
            return $_FILES;
        }
        return $_FILES[$name] ?? null;
    }
}