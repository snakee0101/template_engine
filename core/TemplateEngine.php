<?php

namespace Blade\Core;

class TemplateEngine
{
    /**
     * Returns the value of config parameter in the session
     * with possibility to temporary replace config value 
     * (without complex parameter names ('like p1.p2.p3') for now)
     */
    public function config($parameter, $temporary_replaced_value = null)
    {
        return $temporary_replaced_value !== null ? $_SESSION['config'][$parameter] = $temporary_replaced_value
            : $_SESSION['config'][$parameter];
    }

    /**
     * Loads config into session to make it available to any part of the application
     */
    public function loadConfig()
    {
        session_start();

        $_SESSION['config'] = require __DIR__ . "/../config.php";
    }

    /**
     * Returns the content of the view (view path could be encoded as "dot" syntax
     * Example: admin.payments ---> /compiled/views/admin/payments.blade.php)
     * and passes parameters to it if needed
     */
    public function view($view_path_encoded, ?array $__parameters = null)
    {
        $view_path_decoded = str_replace('.', '/', $view_path_encoded);

        $__view_path = __DIR__ . "/.." .
            $this->config('compiled_path') .
            "/$view_path_decoded" .
            $this->config('view_extension');

        if (!file_exists($__view_path))
            throw new \Exception("view $view_path_encoded was not found");

        //Prohibits variables from outside code from reaching the view
        $variable_isolation_closure = function () use ($__view_path, $__parameters) {
            if ($__parameters != null)
                extract($__parameters);     //turns array of parameters ["p1" => value, "p2" => value] into real variables $p1, $p2

            include $__view_path;
        };
        $variable_isolation_closure();
    }

    /**
     * Pass view content through transformers
     * and return compiled view
     */
    public function compile($view_full_path)
    {
        $initial_content = file_get_contents($view_full_path);

        return $initial_content;
    }
}
