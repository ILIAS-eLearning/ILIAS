<?php

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

declare(strict_types=1);

use ILIAS\Mail\TemplateEngine\Mustache\MustacheTemplateEngineFactory;
use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;
use ILIAS\Mail\TemplateEngine\TemplateEngineInterface;
use PHPUnit\Framework\TestCase;

class MustacheTemplateEngineFactoryTest extends TestCase
{
    public function testFactoryImplementsInterface(): void
    {
        $f = new MustacheTemplateEngineFactory();
        $this->assertInstanceOf(TemplateEngineFactoryInterface::class, $f);
    }

    public function testBasicEngineCanBeRetrieved(): void
    {
        $f = new MustacheTemplateEngineFactory();
        $engine = $f->getBasicEngine();
        $this->assertInstanceOf(TemplateEngineInterface::class, $engine);
    }
}
