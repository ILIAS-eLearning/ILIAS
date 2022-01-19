<?php

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');
include_once('Services/Style/System/test/Exceptions/ilSystemStyleExceptionBaseTest.php');

class ilSystemStyleIconExceptionTest extends ilSystemStyleExceptionBaseTest
{
    protected function getClassName() : string
    {
        return 'ilSystemStyleIconException';
    }
}
