<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

require_once('./libs/composer/vendor/autoload.php');


class ilLanguageMock extends \ilLanguage
{
    public $requested = array();
    public function __construct()
    {
    }
    public function txt($a_topic, $a_default_lang_fallback_mod = "")
    {
        $this->requested[] = $a_topic;
        return $a_topic;
    }
    public function toJS($a_lang_key, ilGlobalTemplateInterface $a_tpl = null)
    {
    }
    public $lang_module = 'common';
    public function loadLanguageModule($lang_module)
    {
    }
}

abstract class TestCase extends PHPUnitTestCase
{
    public function getLanguage()
    {
        return new ilLanguageMock();
    }
}
