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

namespace ILIAS\Tests\FileDelivery\Token;

use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class SecretKeyRotationTest extends TestCase
{
    /** @var list<string> */
    private array $artefacts = [];

    public function testArtefactIsReadNewestKeyFirst(): void
    {
        $rotation = SecretKeyRotation::fromArtefact(
            $this->artefact("<?php return ['newest', 'older', 'oldest'];")
        );

        $this->assertSame('newest', $rotation->getCurrentKey()->get());
        $this->assertSame(
            ['older', 'oldest'],
            array_map(static fn($key): string => $key->get(), $rotation->getOlderKeys())
        );
        $this->assertSame(
            ['newest', 'older', 'oldest'],
            array_map(static fn($key): string => $key->get(), $rotation->getAllKeys())
        );
    }

    public function testSingleKeyArtefactHasNoOlderKeys(): void
    {
        $rotation = SecretKeyRotation::fromArtefact($this->artefact("<?php return ['only'];"));

        $this->assertSame('only', $rotation->getCurrentKey()->get());
        $this->assertSame([], $rotation->getOlderKeys());
    }

    public function testNonStringEntriesAreIgnored(): void
    {
        $rotation = SecretKeyRotation::fromArtefact(
            $this->artefact("<?php return [null, 'usable', 42];")
        );

        $this->assertSame('usable', $rotation->getCurrentKey()->get());
        $this->assertSame([], $rotation->getOlderKeys());
    }

    public function testMissingArtefactIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);

        SecretKeyRotation::fromArtefact(sys_get_temp_dir() . '/does_not_exist_key_rotation.php');
    }

    public function testEmptyArtefactIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);

        SecretKeyRotation::fromArtefact($this->artefact('<?php return [];'));
    }

    public function testArtefactWithoutReturnValueIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);

        SecretKeyRotation::fromArtefact($this->artefact('<?php // nothing returned'));
    }

    private function artefact(string $php): string
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'key_rotation_') . '.php';
        file_put_contents($path, $php);
        $this->artefacts[] = $path;

        return $path;
    }

    protected function tearDown(): void
    {
        foreach ($this->artefacts as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->artefacts = [];
    }
}
