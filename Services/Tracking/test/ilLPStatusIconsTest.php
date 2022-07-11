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

/**
 * Unit tests for class ilLPStatusIcons
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilLPStatusIconsTest extends TestCase
{
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

        $long1 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);
        $long2 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);

        $short1 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);
        $short2 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);

        $scorm1 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SCORM);
        $scorm2 = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SCORM);

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
        $this->expectException(ilLPException::class);
        $wrong = ilLPStatusIcons::getInstance(793);
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
        $path = $instances['long']->getImagePathRunning();
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testImagePathAssetForLongVariant(array $instances) : void
    {
        $this->expectException(ilLPException::class);
        $path = $instances['long']->getImagePathAsset();
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testSomeExamplesForRenderedIcons(array $instances) : void
    {
        //set up a mock template
        $stored_variant = null;
        $stored_path = null;

        $tplMock = Mockery::mock('overload:' . ilTemplate::class);

        $tplMock->shouldReceive('setVariable')
                ->with('ICON_VARIANT', Mockery::type('string'))
                ->andReturnUsing(function ($arg1, $arg2) use (&$stored_variant) {
                    $stored_variant = $arg2;
                });
        $tplMock->shouldReceive('setVariable')
                ->with('IMAGE_PATH', Mockery::type('string'))
                ->andReturnUsing(function ($arg1, $arg2) use (&$stored_path) {
                    $stored_path = $arg2;
                });
        $tplMock->shouldReceive('setVariable')
                ->with('ALT_TEXT', Mockery::type('string'));
        $tplMock->shouldReceive('get')
                ->andReturnUsing(function () use (&$stored_variant, &$stored_path) {
                    return 'variant: ' . $stored_variant . ', ' . 'path: ' . $stored_path;
                });

        //try rendering some icons
        $path = 'sample/path';

        $this->assertSame(
            'variant: ilLPIconLong, path: ' . $path,
            $instances['long']->renderIcon($path, 'alt')
        );

        $stored_variant = null;
        $stored_path = null;

        $this->assertSame(
            'variant: ilLPIconShort, path: ' . $path,
            $instances['short']->renderIcon($path, 'alt')
        );
    }

    /**
     * @depends testTripleton
     * @param array<string, ilLPStatusIcons> $instances
     */
    public function testRenderScormIcons(array $instances) : void
    {
        //set up a mock template, to be safe
        $tplMock = Mockery::mock('overload:' . ilTemplate::class);

        $this->expectException(ilLPException::class);
        $instances['scorm']->renderIcon('path', 'alt');
    }
}
