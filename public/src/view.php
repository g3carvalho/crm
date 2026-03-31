<?php

function render(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    include __DIR__ . '/../templates/' . $template . '.php';
}

function render_layout(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    include __DIR__ . '/../templates/layout.php';
}
