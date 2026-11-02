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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use PHPUnit\Framework\MockObject\Stub;

class HiddenListInputTest extends \ILIAS_UI_TestBase
{
    protected I\Input\Field\Hidden & Stub $hidden_stub;
    protected string $hidden_stub_html;

    protected function setUp(): void
    {
        $this->hidden_stub_html = sha1(I\Input\Field\Hidden::class);
        $this->hidden_stub = $this->createStub(I\Input\Field\Hidden::class);
        $this->hidden_stub->method('getCanonicalName')->willReturn($this->hidden_stub_html);

        parent::setUp();
    }

    public function testRenderWithValue(): void
    {
        $this->hidden_stub->method('withValue')->willReturnSelf();

        $value = ['a', 'b', 'c'];

        $hidden_list = $this->getUIFactory()->input()->field()->hiddenList();
        $hidden_list = $hidden_list->withValue($value);

        $value_html = str_repeat($this->hidden_stub_html, count($value));

        $expected = <<<HTML
<div id="id_1" class="c-input c-input-hidden-list">
    $value_html
</div>
HTML;

        $actual = $this->getDefaultRenderer(null, [$this->hidden_stub])->render($hidden_list);

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    public function testRenderWithoutValue(): void
    {
        $hidden_list = $this->getUIFactory()->input()->field()->hiddenList();

        $expected = '';

        $actual = $this->getDefaultRenderer(null, [$this->hidden_stub])->render($hidden_list);

        $this->assertSame($expected, $actual);
    }

    public function getUIFactory(): \NoUIFactory
    {
        $partial_field_factory_mock = $this
            ->getMockBuilder(I\Input\Field\Factory::class)
            ->onlyMethods(['hidden'])
            ->setConstructorArgs([
                $this->createStub(I\Input\Field\Node\Factory::class),
                $this->getUploadLimitResolver(),
                $this->createStub(I\SignalGeneratorInterface::class),
                $this->getDataFactory(),
                $this->getRefinery(),
                $this->getLanguage(),
            ])->getMock();

        $partial_field_factory_mock->method('hidden')->willReturn($this->hidden_stub);

        $input_factory_stub = $this->createStub(I\Input\Factory::class);
        $input_factory_stub->method('field')->willReturn($partial_field_factory_mock);

        return new class($input_factory_stub) extends \NoUIFactory {
            public function __construct(protected I\Input\Factory $input_factory)
            {
            }
            public function input(): I\Input\Factory
            {
                return $this->input_factory;
            }
        };
    }
}
