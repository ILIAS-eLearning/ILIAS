<?php

namespace ILIAS\GlobalScreen\Client;

chdir(strstr(__DIR__, '/components/ILIAS', true));
/** @noRector  */
require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/vendor/composer/vendor/autoload.php');

if (php_sapi_name() !== 'cli') {
    (new CallbackHandler())->run();
}
