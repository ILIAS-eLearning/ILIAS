<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Symbol\Icon\Custom;

/**
 * Unit tests for class ilLPStatusIcons
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilLPStatusIconsTest extends TestCase
{
    protected $path = 'sample/path';
    protected $alt = 'alt';
    protected $size = Icon::SMALL;

    /**
     * @return array<string, ilLPStatusIcons>
     */
    public function testTripleton() : array
    {
        $utilMock = Mockery::mock('alias:' . ilUtil::class);
        $utilMock->shouldReceive('getImagePath')
                 ->with(Mockery::type('string'))
                 ->andReturnUsing(function ($arg) {
                     return 'test/' . $arg;
                 });

        $renderer = Mockery::mock(Renderer::class);
        $renderer->shouldReceive('render')
                 ->andReturnUsing(function ($arg1) {
                     return 'rendered: path(' . $arg1->getIconPath() .
                         '), alt(' . $arg1->getLabel() .
                         '), size(' . $arg1->getSize() . ')';
                 });

        $custom_icon = Mockery::mock(Custom::class);
        $custom_icon->shouldReceive('getIconPath')
                    ->andReturn($this->path);
        $custom_icon->shouldReceive('getSize')
                    ->andReturn($this->size);
        $custom_icon->shouldReceive('getLabel')
                    ->andReturn($this->alt);

        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('symbol->icon->custom')
                ->andReturn($custom_icon);

        $long1 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG, $renderer, $factory);
        $long2 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG, $renderer, $factory);

        $short1 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT, $renderer, $factory);
        $short2 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT, $renderer, $factory);

        $scorm1 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SCORM, $renderer, $factory);
        $scorm2 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SCORM, $renderer, $factory);

        $this->assertSame($short1, $short2);
        $this->assertSame($long1, $long2);
        $this->assertSame($scorm1, $scorm2);

        $this->assertNotSame($long1, $short1);
        $this->assertNotSame($long1, $scorm1);
        $this->assertNotSame($short1, $scorm1);

        return ['long' => $long1, 'short' => $short1, 'scorm' => $scorm1];
    }

    public function testGetInstanceForInvalidVariant() : void
    {
        $renderer = $this->getMockBuilder(Renderer::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $factory = $this->getMockBuilder(Factory::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->expectException(ilLPException::class);
        ilLPStatusIcons::getInstance(793, $renderer, $factory);
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testSomeExamplesForImagePathsByStatus(array $instances) : void
    {
        $path1 = $instances['long']->getImagePathInProgress();
        $path2 = $instances['long']->getImagePathForStatus(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
        $this->assertSame($path1, $path2);

        $path1 = $instances['short']->getImagePathCompleted();
        $path2 = $instances['short']->getImagePathForStatus(ilLPStatus::LP_STATUS_COMPLETED_NUM);
        $this->assertSame($path1, $path2);

        $path1 = $instances['scorm']->getImagePathFailed();
        $path2 = $instances['scorm']->getImagePathForStatus(ilLPStatus::LP_STATUS_FAILED_NUM);
        $this->assertSame($path1, $path2);
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testImagePathRunningForLongVariant(array $instances) : void
    {
        $this->expectException(ilLPException::class);
        $instances['long']->getImagePathRunning();
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testImagePathAssetForLongVariant(array $instances) : void
    {
        $this->expectException(ilLPException::class);
        $instances['long']->getImagePathAsset();
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testSomeExamplesForRenderedIcons(array $instances) : void
    {
        //try rendering some icons
        $this->assertSame(
            'rendered: path(' . $this->path .
            '), alt(' . $this->alt .
            '), size(' . $this->size . ')',
            $instances['long']->renderIcon($this->path, $this->alt)
        );

        $this->assertSame(
            'rendered: path(' . $this->path .
            '), alt(' . $this->alt .
            '), size(' . $this->size . ')',
            $instances['short']->renderIcon($this->path, $this->alt)
        );
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testRenderScormIcons(array $instances) : void
    {
        $this->expectException(ilLPException::class);
        $instances['scorm']->renderIcon('path', 'alt');
    }
}
