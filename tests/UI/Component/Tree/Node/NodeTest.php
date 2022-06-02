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
 
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "../../../../Base.php");

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Tree\Node\Node;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component\Clickable;
use ILIAS\Refinery\URI\StringTransformation;

/**
 * Dummy-implementation for testing
 */
class TestingNode extends Node
{
    public function __construct(string $label, URI $link = null)
    {
        parent::__construct($label, $link);
    }

    /**
     * Create a new node object with an URI that will be added to the UI
     */
    public function withLink(URI $link) : \ILIAS\UI\Component\Tree\Node\Node
    {
        return new TestingNode(
            $this->label,
            $link
        );
    }
}

/**
 * Tests for the (Base-)Node.
 */
class NodeTest extends ILIAS_UI_TestBase
{
    public function testConstruction() : TestingNode
    {
        $node = new TestingNode("");
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Tree\\Node\\Node", $node);

        return $node;
    }

    /**
     * @depends testConstruction
     */
    public function testDefaults(TestingNode $node) : void
    {
        $this->assertFalse($node->isExpanded());
        $this->assertFalse($node->isHighlighted());
        $this->assertEquals([], $node->getSubnodes());
    }

    /**
     * @depends testConstruction
     */
    public function testWithExpanded(TestingNode $node) : void
    {
        $this->assertTrue(
            $node->withExpanded(true)->isExpanded()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testWithHighlighted(TestingNode $node) : void
    {
        $this->assertTrue(
            $node->withHighlighted(true)->isHighlighted()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testWithOnClick(TestingNode $node) : Clickable
    {
        $sig_gen = new I\SignalGenerator();
        $sig = $sig_gen->create();

        $node = $node->withOnClick($sig);
        $check = $node->getTriggeredSignals()[0]->getSignal();
        $this->assertEquals($sig, $check);
        return $node;
    }

    /**
     * @depends testWithOnClick
     */
    public function testWithAppendOnClick(Clickable $node) : void
    {
        $sig_gen = new I\SignalGenerator();
        $sig = $sig_gen->create();

        $node = $node->appendOnClick($sig);
        $check = $node->getTriggeredSignals()[1]->getSignal();
        $this->assertEquals($sig, $check);
    }

    /**
     * @depends testWithOnClick
     */
    public function testWithURI(Clickable $node) : void
    {
        $uri = new URI('http://google.de:8080');

        $node = $node->withLink($uri);

        $stringTransformation = new StringTransformation();

        $this->assertEquals('http://google.de:8080', $stringTransformation->transform($node->getLink()));
    }
}
