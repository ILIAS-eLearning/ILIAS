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

use ILIAS\StudyProgramme\Assignment\Node;
use ILIAS\StudyProgramme\Assignment\Zipper;

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");

class NodeMock extends Node
{
    protected int $score;
    public function withScore(int $score): self
    {
        $clone = clone $this;
        $clone->score = $score;
        return $clone;
    }
    public function getScore(): int
    {
        return $this->score;
    }
}

class ilStudyProgrammeProgressTreeTest extends \PHPUnit\Framework\TestCase
{
    protected function build(array $data, $node_id): Node
    {
        $subnodes = [];
        foreach ($data[$node_id] as $subnode_id) {
            $subnodes[] = $this->build($data, $subnode_id);
        }
        return (new NodeMock($node_id))->setSubnodes($subnodes);
    }

    public function setUp(): void
    {
        $data = [
            'top' => ['1.2', '1.1'],
            '1.1' => [],
            '1.2' => ['1.2.1', '1.2.2'],
            '1.2.1' => ['1.2.1.1'],
            '1.2.2' => [],
            '1.2.1.1' => []
        ];

        $this->topnode = $this->build($data, 'top');
    }

    public function testPGSTreeCreation(): void
    {
        $n = $this->topnode;
        $this->assertEquals($n->getId(), 'top');
        $this->assertInstanceOf(Node::class, $n->getSubnode('1.1'));

        $this->assertEquals(
            ['top', '1.2', '1.2.2'],
            $n->getSubnode('1.2')->getSubnode('1.2.2')->getPath()
        );

        $this->assertEquals(
            ['top', '1.2', '1.2.1'],
            $n->findSubnodePath('1.2.1')
        );
    }

    public function testPGSTreeZipperNav(): void
    {
        $zipper = new Zipper($this->topnode);
        $this->assertInstanceOf(Zipper::class, $zipper->toChild('1.1'));
        $this->assertInstanceOf(
            Zipper::class,
            $zipper->toPath($this->topnode->findSubnodePath('1.2.1'))->toParent()
        );
        $this->assertInstanceOf(
            Node::class,
            $zipper->toPath($this->topnode->findSubnodePath('1.2.1'))->getRoot()
        );
    }

    public function testPGSTreeZipperManipulation(): void
    {
        $zipper = new Zipper($this->topnode);

        $path = $this->topnode->findSubnodePath('1.2.1');
        $modified = $zipper
            ->toPath($path)->modifyFocus(fn ($n) => $n->withScore(7))
            ->toParent()->modifyFocus(fn ($n) => $n->withScore(6))
            ->getRoot();

        $zipper = new Zipper($modified);
        $other_path = $this->topnode->findSubnodePath('1.1');
        $modified = $zipper
            ->toPath($other_path)->modifyFocus(fn ($n) => $n->withScore(8))
            ->getRoot();
        $this->assertEquals(7, $modified->getSubnode('1.2')->getSubnode('1.2.1')->getScore());
        $this->assertEquals(6, $modified->getSubnode('1.2')->getScore());
        $this->assertEquals(8, $modified->getSubnode('1.1')->getScore());

        //should not change others...
        $zipper = new Zipper($modified);
        $modified = $zipper
            ->toPath($path)->modifyFocus(fn ($n) => $n->withScore(17))
            ->getRoot();
        $this->assertEquals(17, $modified->getSubnode('1.2')->getSubnode('1.2.1')->getScore());
        $this->assertEquals(6, $modified->getSubnode('1.2')->getScore());
        $this->assertEquals(8, $modified->getSubnode('1.1')->getScore());
    }

    public function testPGSTreeZipperManipulateAll(): void
    {
        $zipper = new Zipper($this->topnode);
        $modified = $zipper
            ->modifyAll(fn ($n) => $n->withScore(count($n->getSubnodes())))
            ->getRoot();

        $this->assertEquals(2, $modified->getScore());
        $this->assertEquals(0, $modified->getSubnode('1.1')->getScore());
        $this->assertEquals(2, $modified->getSubnode('1.2')->getScore());
        $this->assertEquals(1, $modified->getSubnode('1.2')->getSubnode('1.2.1')->getScore());
        $this->assertEquals(0, $modified->getSubnode('1.2')->getSubnode('1.2.1')->getSubnode('1.2.1.1')->getScore());
        $this->assertEquals(0, $modified->getSubnode('1.2')->getSubnode('1.2.2')->getScore());
    }
}
