<?php

use PHPUnit\Framework\TestCase;

/**
 * Base class for al tests
 */
abstract class ilMathJaxBaseTest extends TestCase
{

    protected function getEmptyConfig() : ilMathJaxConfig {
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

    protected function getFactoryMock($imagefile = null)
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

    protected function getTemplateMock()
    {
        $template = $this
            ->getMockBuilder(ilGlobalTemplate::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addJavaScript'])
            ->getMock();
        return $template;
    }

    protected function getImageMock($imagefile)
    {
        $image = $this
            ->getMockBuilder(ilMathJaxImage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['exists', 'read', 'write', 'absolutePath', 'getCacheSize'])
            ->getMock();
        $image->method('exists')->willReturn(false);
        $image->method('read')->willReturn(file_get_contents(__DIR__ . '/'. $imagefile));
        $image->method('absolutePath')->willReturn(__DIR__ . '/'. $imagefile);
        $image->method('getCacheSize')->willReturn('10 KB');
        return $image;
    }

    protected function getServerMock()
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