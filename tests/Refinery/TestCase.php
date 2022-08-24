<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Tests\Refinery;

use ilGlobalTemplateInterface;
use ilLanguage;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class ilLanguageMock extends ilLanguage
{
    /** @var string[] */
    public array $requested = [];
    public string $lang_module = 'common';

    public function __construct()
    {
    }

    public function txt(string $a_topic, string $a_default_lang_fallback_mod = ''): string
    {
        $this->requested[] = $a_topic;
        return $a_topic;
    }

    public function toJS($a_lang_key, ilGlobalTemplateInterface $a_tpl = null): void
    {
    }

    public function loadLanguageModule(string $a_module): void
    {
    }
}

abstract class TestCase extends PHPUnitTestCase
{
    public function getLanguage(): ilLanguageMock
    {
        return new ilLanguageMock();
    }
}
