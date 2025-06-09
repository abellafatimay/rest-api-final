<?php

namespace Responses;

class Response {
    protected $data;
    private $statusCode;
    private array $headers = [];

    private static $macros = [];
    private static $viewEngine = null;

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
        return $this;
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

    // --- Factory methods for standardized responses ---

    public static function view(string $viewPath, array $data = [], int $statusCode = 200): Response {
        // Ensure 'error' key always exists to prevent undefined variable errors
        if (!isset($data['error'])) {
            $data['error'] = null;
        }
        
        return new self([
            'view' => $viewPath,
            'data' => $data
        ], $statusCode, ['Content-Type' => 'text/html']);
    }

    public static function json($data, int $statusCode = 200): Response {
        return new self($data, $statusCode, ['Content-Type' => 'application/json']);
    }

    public static function html(string $html, int $statusCode = 200): Response {
        return new self($html, $statusCode, ['Content-Type' => 'text/html']);
    }

    public static function redirect(string $url, int $statusCode = 303): Response {
        return new self(['redirect' => $url], $statusCode, ['Location' => $url]);
    }

    public static function error(string $message, int $statusCode = 400, bool $asJson = false): Response {
        if ($asJson) {
            return self::json(['error' => $message], $statusCode);
        }
        return self::view('Errors/error', [
            'title' => 'Error',
            'errorCode' => $statusCode,
            'error' => $message
        ], $statusCode);
    }

    // --- Flash message support ---

    public function withFlash(string $type, string $message): Response {
        $_SESSION['flash'][$type] = $message;
        return $this;
    }

    // --- Macro/extension support ---

    public static function macro(string $name, callable $macro): void {
        static::$macros[$name] = $macro;
    }

    public static function __callStatic($method, $parameters) {
        if (isset(static::$macros[$method])) {
            return call_user_func_array(static::$macros[$method], $parameters);
        }
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    // --- View engine support ---

    public static function setViewEngine($engine): void {
        static::$viewEngine = $engine;
    }

    protected function renderView(string $view, array $data = []): string {
        if (static::$viewEngine !== null) {
            return static::$viewEngine->render($view, $data);
        }
        
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        return ob_get_clean();
    }
}