<?php

include 'vendor/autoload.php';

$e = new \Blade\Core\TemplateEngine();
$e->loadConfig();

return $e->view('test_view_123123124');