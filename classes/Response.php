<?php
class Response {
    private $statusCode;
    private $body;

    public function __construct($statusCode, $body) {
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getBody() {
        return $this->body;
    }

    public function send() {
        header('Content-Type: application/json');
        http_response_code($this->statusCode);
        echo json_encode($this->body);
        exit;
    }
}
