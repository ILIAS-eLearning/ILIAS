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

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\HasHelpTopics;
use ILIAS\UI\Component\HasHelpTopics as IHasHelpTopics;
use ILIAS\UI\Help\Topic;

require_once("libs/composer/vendor/autoload.php");

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class HasHelpTopicsTest extends TestCase
{
    public function setUp(): void
    {
        $this->mock = new class () implements IHasHelpTopics {
            use HasHelpTopics;
        };
    }

    public function testEmptyAtCreation()
    {
        $this->assertEquals([], $this->mock->getHelpTopics());
    }

    public function testWithHelpTopics()
    {
        $topics = [new Topic("a"), new Topic("b"), new Topic("c")];
        $mock = $this->mock->withHelpTopics(...$topics);
        $this->assertEquals([], $this->mock->getHelpTopics());
        $this->assertEquals($topics, $mock->getHelpTopics());
    }

    public function testAdditionalHelpTopics()
    {
        $a = new Topic("a");
        $topics = [new Topic("b"), new Topic("c")];
        $mock = $this->mock->withHelpTopics($a);
        $mock2 = $mock->withAdditionalHelpTopics(...$topics);
        $this->assertEquals([$a], $mock->getHelpTopics());
        $all_topics = [new Topic("a"), new Topic("b"), new Topic("c")];
        $this->assertEquals($all_topics, $mock2->getHelpTopics());
    }

    public function testWithHelpTopicsOverwrites()
    {
        $topics1 = [new Topic("a"), new Topic("b"), new Topic("c")];
        $topics2 = [new Topic("d"), new Topic("e")];
        $mock = $this->mock->withHelpTopics(...$topics1)->withHelpTopics(...$topics2);
        $this->assertEquals($topics2, $mock->getHelpTopics());
    }

    public function testWithHelpTopicsDeduplicates()
    {
        $dup_topics = [new Topic("a"), new Topic("b"), new Topic("c"), new Topic("a"), new Topic("a"), new Topic("b")];
        $topics = [new Topic("a"), new Topic("b"), new Topic("c")];
        $mock = $this->mock->withHelpTopics(...$dup_topics);
        $this->assertEquals($topics, $mock->getHelpTopics());
    }

    public function testWithAdditionalHelpTopicsDeduplicates()
    {
        $dup_topics = [new Topic("a"), new Topic("b"), new Topic("c"), new Topic("a"), new Topic("a"), new Topic("b")];
        $topics = [new Topic("a"), new Topic("b"), new Topic("c")];
        $mock = $this->mock->withHelpTopics(new Topic("c"))->withAdditionalHelpTopics(...$dup_topics);
        $this->assertEquals($topics, $mock->getHelpTopics());
    }
}
