<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use PHPUnit\Framework\TestCase;

/**
 * Base class for al tests
 */
abstract class ilMathJaxBaseTest extends TestCase
{
    /**
     * Get a config without active settings
     */
    protected function getEmptyConfig(): ilMathJaxConfig
    {
        return new ilMathJaxConfig(
            false,
            '',
            '',
            0,
            false,
            '',
            0,
            false,
            false,
            false
        );
    }

    /**
     * Get a factory mockup that will deliver other mockups
     */
    protected function getFactoryMock(?string $imagefile = null): ilMathJaxFactory
    {
        $factory = $this
            ->getMockBuilder(ilMathJaxFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['template', 'image', 'server'])
            ->getMock();
        $factory->method('template')->willReturn($this->getTemplateMock());
        $factory->method('server')->willReturn($this->getServerMock());
        if (isset($imagefile)) {
            $factory->method('image')->willReturn($this->getImageMock($imagefile));
        }
        return $factory;
    }

    /**
     * Get a global template mockup
     */
    protected function getTemplateMock(): ilGlobalTemplate
    {
        $template = $this
            ->getMockBuilder(ilGlobalTemplate::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addJavaScript'])
            ->getMock();
        return $template;
    }

    /**
     * Get a cached image mockup from an example file
     * @param string $imagefile name of the example file in the test directory
     */
    protected function getImageMock(string $imagefile): ilMathJaxImage
    {
        $image = $this
            ->getMockBuilder(ilMathJaxImage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['exists', 'read', 'write', 'absolutePath', 'getCacheSize'])
            ->getMock();
        $image->method('exists')->willReturn(false);
        $image->method('read')->willReturn(file_get_contents(__DIR__ . '/' . $imagefile));
        $image->method('absolutePath')->willReturn(__DIR__ . '/' . $imagefile);
        $image->method('getCacheSize')->willReturn('10 KB');
        return $image;
    }

    /**
     * Get a mockup of the class for server calls
     */
    protected function getServerMock(): ilMathJaxServer
    {
        $server = $this
            ->getMockBuilder(ilMathJaxServer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();
        $server->method('call')->willReturn('server call result');
        return $server;
    }
}
