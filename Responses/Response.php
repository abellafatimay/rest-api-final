<?php

namespace Responses;

class Response {
    protected $data;
    private $statusCode;
    private array $headers = [];

    public function __construct($data, $statusCode = 200, array $headers = []) {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function addHeader(string $name, string $value) {
        $this->headers[$name] = $value;
        return $this; //Chaining
    }

    /**
     * Get the response data/body
     *
     * @return mixed The response data
     */
    public function getBody() {
        return $this->data;
    }

    public function send() {
        if (!headers_sent()) {
            // Set proper Content-Type
            if (!isset($this->headers['Content-Type'])) {
                $this->headers['Content-Type'] = $this->determineContentType();
            }
            
            // Send headers
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
            http_response_code($this->statusCode);
        }

        // Check if this is a view response
        if (isset($this->data['view'])) {
            $viewPath = $this->data['view'];
            $data = $this->data['data'] ?? [];
            
            // Extract variables for the view
            extract($data);
            
            $filePath = __DIR__ . '/../Views/' . $viewPath . '.php';
            
            if (!file_exists($filePath)) {
                echo "Error: View file not found: $filePath";
                return;
            }
            
            // Buffer the output to prevent header issues
            ob_start();
            include $filePath;
            $content = ob_get_clean();
            
            echo $content;
        } else {
            // Use the prepareOutput method instead of direct json_encode
            echo $this->prepareOutput();
        }
    }

    private function prepareOutput() {
        // If body is already a string, return as-is
        if (is_string($this->data)) {
            return $this->data;
        }
        
        // If body is array or object, convert to JSON
        if (is_array($this->data) || is_object($this->data)) {
            return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        
        // For other types, convert to string
        return (string) $this->data;
    }

    private function determineContentType(): string {
        // For view responses, always use text/html
        if (isset($this->data['view'])) {
            return 'text/html';
        }
        
        // For other responses, use JSON
        return 'application/json';
    }
}