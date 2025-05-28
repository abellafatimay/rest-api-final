<?php

namespace Responses;

class Response {
    private $statusCode;
    private $body;
    private array $headers = [];

    public function __construct($body, $statusCode = 200, array $headers = []) {
        $this->statusCode = $statusCode;
        $this->body = $body;
        // Don't set default Content-Type - let it be determined by the response type
        $this->headers = $headers;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getBody() {
        return $this->body;
    }

    public function addHeader(string $name, string $value) {
        $this->headers[$name] = $value;
        return $this; //Chaining
    }

    public function send() {
        if (!headers_sent()) {
            // For debugging
            error_log('Response Status Code: ' . $this->statusCode);
            error_log('Response Headers: ' . print_r($this->headers, true));
            error_log('Response Body Type: ' . gettype($this->body));
            
            // Set Content-Type based on response type if not already set
            if (!isset($this->headers['Content-Type'])) {
                $this->headers['Content-Type'] = $this->determineContentType();
            }

            // Send headers
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
            http_response_code($this->statusCode);
        }

        echo $this->body;
    }

    private function determineContentType(): string {
        // If body is array or object, assume JSON
        if (is_array($this->body) || is_object($this->body)) {
            return 'application/json; charset=UTF-8';
        }
        
        // If body looks like HTML, set HTML content type
        if (is_string($this->body) && 
            (str_contains($this->body, '<html') || str_contains($this->body, '<!DOCTYPE'))) {
            return 'text/html; charset=UTF-8';
        }
        
        // Default to plain text
        return 'text/plain; charset=UTF-8';
    }
}
