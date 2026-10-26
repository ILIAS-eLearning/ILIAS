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

use ILIAS\DI\Container;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTI1p1ConsumerLaunchContextTest extends TestCase
{
    private const int OBJECT_REF_ID = 99;

    public static function contextProvider(): array
    {
        $root = ['child' => 1, 'title' => 'Repository', 'type' => 'root'];
        $category = ['child' => 10, 'title' => 'Faculty', 'type' => 'cat'];
        $course = ['child' => 20, 'title' => 'Course', 'type' => 'crs'];
        $group = ['child' => 30, 'title' => 'Group', 'type' => 'grp'];
        $object = ['child' => self::OBJECT_REF_ID, 'title' => 'Tool', 'type' => 'lti'];

        return [
            'outermost course over an inner group' => [
                [$root, $category, $course, $group, $object],
                ['crs', 'grp', 'cat', 'root'],
                ['id' => 20, 'title' => 'Course', 'type' => 'crs'],
            ],
            'innermost category without a course' => [
                [$root, $category, $object],
                ['crs', 'grp', 'cat', 'root'],
                ['id' => 10, 'title' => 'Faculty', 'type' => 'cat'],
            ],
            'root without a category' => [
                [$root, $object],
                ['crs', 'grp', 'cat', 'root'],
                ['id' => 1, 'title' => 'Repository', 'type' => 'root'],
            ],
            'restricted to groups' => [
                [$root, $category, $course, $group, $object],
                ['grp'],
                ['id' => 30, 'title' => 'Group', 'type' => 'grp'],
            ],
            'no valid context' => [
                [$root, $category, $object],
                ['crs'],
                [],
            ],
        ];
    }

    #[DataProvider('contextProvider')]
    public function testGetContext(array $path, array $valid_types, array $expected): void
    {
        $tree = $this->createMock(ilTree::class);
        $tree->expects($this->once())
             ->method('getPathFull')
             ->with(self::OBJECT_REF_ID)
             ->willReturn($path);
        $this->setTree($tree);

        $context = new ilLTI1p1ConsumerLaunchContext(self::OBJECT_REF_ID);

        $this->assertSame($expected, $context->getContext($valid_types));
    }

    public function testContextIsLookedUpOnce(): void
    {
        $tree = $this->createMock(ilTree::class);
        $tree->expects($this->once())
             ->method('getPathFull')
             ->willReturn([['child' => 20, 'title' => 'Course', 'type' => 'crs']]);
        $this->setTree($tree);

        $context = new ilLTI1p1ConsumerLaunchContext(self::OBJECT_REF_ID);
        $context->getContext();

        $this->assertSame(['id' => 20, 'title' => 'Course', 'type' => 'crs'], $context->getContext());
    }

    public function testLtiContextType(): void
    {
        $this->assertSame(
            'http://purl.imsglobal.org/vocab/lis/v2/course#Group',
            ilLTI1p1ConsumerLaunchContext::getLTIContextType('grp')
        );
        $this->assertSame(
            'http://purl.imsglobal.org/vocab/lis/v2/course#CourseOffering',
            ilLTI1p1ConsumerLaunchContext::getLTIContextType('crs')
        );
        $this->assertSame(
            'http://purl.imsglobal.org/vocab/lis/v2/course#CourseOffering',
            ilLTI1p1ConsumerLaunchContext::getLTIContextType('cat')
        );
    }

    private function setTree(ilTree $tree): void
    {
        $dic = new Container();
        $dic['tree'] = static fn(): ilTree => $tree;
        $GLOBALS['DIC'] = $dic;
    }
}
