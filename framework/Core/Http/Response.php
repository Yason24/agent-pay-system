<?php

namespace Framework\Core\Http;

class Response
{

    public function __construct(
        protected mixed $content = '',
        protected int $status = 200,
        protected array $headers = []
    ) {}

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);

            foreach ($this->headers as $key => $value) {
                header("$key: $value");
            }
        }

        echo $this->content;
    }
}