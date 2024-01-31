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

namespace ILIAS\Component\Tests\Resource;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Dependencies\Name;
use ILIAS\Component\Resource as R;

class PublicAssetManagerTest extends TestCase
{
    protected R\PublicAssetManager $manager;

    protected function newPublicAsset($source, $target)
    {
        return new class ($source, $target) implements R\PublicAsset {
            public function __construct(
                protected string $source,
                protected string $target,
            ) {
            }

            public function getSource(): string
            {
                return $this->source;
            }

            public function getTarget(): string
            {
                return $this->target;
            }
        };
    }

    public function setUp(): void
    {
        $this->manager = new class () extends R\PublicAssetManager {
            public $copied = [];
            public $purged = [];
            public $madeDir = [];

            protected function copy(string $source, $target): void
            {
                $this->copied[] = [$source, $target];
            }

            protected function purge(string $path): void
            {
                $this->purged[] = $path;
            }

            protected function makeDir(string $path): void
            {
                $this->madeDir[] = $path;
            }
        };
    }

    public function testTargetCanOnlyBeUsedOnce()
    {
        $this->expectException(\LogicException::class);

        $asset1 = $this->newPublicAsset("some/source", "target");
        $asset2 = $this->newPublicAsset("some/other/source", "target");

        $this->manager->addAssets($asset1, $asset2);
    }

    public function testTargetCanNotBeWithinOtherTarget1()
    {
        $this->expectException(\LogicException::class);

        $asset1 = $this->newPublicAsset("some/source", "target");
        $asset2 = $this->newPublicAsset("some/other/source", "target/sub");

        $this->manager->addAssets($asset1, $asset2);
    }

    public function testTargetCanNotBeWithinOtherTarget2()
    {
        $this->expectException(\LogicException::class);

        $asset1 = $this->newPublicAsset("some/source", "target/sub");
        $asset2 = $this->newPublicAsset("some/other/source", "target");

        $this->manager->addAssets($asset1, $asset2);
    }

    public function testBuildAssetFolderEmpty()
    {
        $this->manager->buildPublicFolder("/base", "/target");
        $this->assertEquals([], $this->manager->copied);
        $this->assertEquals(["/target"], $this->manager->purged);
        $this->assertEquals(["/target"], $this->manager->madeDir);
    }

    public function testBuildAssetFolder()
    {
        $this->manager->addAssets(
            $this->newPublicAsset("source1", "target1"),
            $this->newPublicAsset("source2", "second/target")
        );

        $this->manager->buildPublicFolder("/base", "/public");

        $this->assertEquals(["/public"], $this->manager->purged);
        $this->assertEquals(["/public", "/public/second"], $this->manager->madeDir);
        $this->assertEquals([["/base/source1", "/public/target1"], ["/base/source2", "/public/second/target"]], $this->manager->copied);
    }
}
