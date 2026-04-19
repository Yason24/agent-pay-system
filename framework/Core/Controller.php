<?php

namespace Framework\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): string
    {
        return view($view, $data);
    }
}