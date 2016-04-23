<?php

spl_autoload_register(
    function ($className) {
        $classPath = str_replace("\\", "/", $className);
        list($nameSpace,$classPath) = explode("/", $classPath, 2);

        $filePath = dirname(__FILE__) . '/src/' . $classPath . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }
);
