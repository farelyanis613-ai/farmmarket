<?php

class Controller
{
    public function render($view, $data = [])
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/' . $view;
    }

    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
