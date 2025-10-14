<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Aligne le fuseau horaire PHP sur l'heure locale
\date_default_timezone_set('Europe/Paris');

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
