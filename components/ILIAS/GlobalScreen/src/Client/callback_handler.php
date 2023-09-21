<?php

namespace ILIAS\GlobalScreen\Client;

chdir(strstr(__DIR__, '/src', true));
/** @noRector  */
require_once('./vendor/composer/vendor/autoload.php');

if (php_sapi_name() !== 'cli') {
    (new CallbackHandler())->run();
}
