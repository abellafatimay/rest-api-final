<?php
interface RequestInterface {
    public function getMethod(): string;
    public function getPath(): string;
    public function getBody(): array;
}