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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Wiki util test. Tests mostly mediawiki code.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WikiUtilTest extends TestCase
{
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        $db_mock = $this->createMock(ilDBInterface::class);
        $this->setGlobalVariable(
            "ilDB",
            $db_mock
        );
    }

    protected function tearDown(): void
    {
    }

    public function testMakeUrlTitle(): void
    {
        $input_expected = [
            ["a", "a"]
            ,["z", "z"]
            ,["0", "0"]
            ,[" ", "_"]
            ,["_", "_"]
            ,["!", "%21"]
            ,["§", "%C2%A7"]
            ,["$", "%24"]
            ,["%", "%25"]
            ,["&", "%26"]
            ,["/", "%2F"]
            ,["(", "%28"]
            ,["+", "%2B"]
            ,[";", "%3B"]
            ,[":", "%3A"]
            ,["-", "-"]
            ,["#", "%23"]
            ,["?", "%3F"]
            ,["\x00", ""]
            ,["\n", ""]
            ,["\r", ""]
        ];
        foreach ($input_expected as $ie) {
            $result = ilWikiUtil::makeUrlTitle($ie[0]);

            $this->assertEquals(
                $ie[1],
                $result
            );
        }
    }

    public function testMakeDbTitle(): void
    {
        $input_expected = [
            ["a", "a"]
            ,["z", "z"]
            ,["0", "0"]
            ,[" ", " "]
            ,["_", " "]
            ,["!", "!"]
            ,["§", "§"]
            ,["$", "$"]
            ,["%", "%"]
            ,["&", "&"]
            ,["/", "/"]
            ,["(", "("]
            ,["+", "+"]
            ,[";", ";"]
            ,[":", ":"]
            ,["-", "-"]
            ,["#", "#"]
            ,["?", "?"]
            ,["\x00", ""]
            ,["\n", ""]
            ,["\r", ""]
        ];
        foreach ($input_expected as $ie) {
            $result = ilWikiUtil::makeDbTitle($ie[0]);

            $this->assertEquals(
                $ie[1],
                $result
            );
        }
    }

    protected function processInternalLinksExtCollect(string $xml):array
    {
        return ilWikiUtil::collectInternalLinks(
            $xml,
            0,
            true,
            IL_WIKI_MODE_EXT_COLLECT
        );
    }

    public function testProcessInternalLinksExtCollect(): void
    {
        $input_expected = [
            ["", []]
            ,["<Foo></Foo>", []]
        ];
        foreach ($input_expected as $ie) {
            $result = $this->processInternalLinksExtCollect($ie[0]);

            $this->assertEquals(
                $ie[1],
                $result
            );
        }
    }

    public function testProcessInternalLinksExtCollectOneSimple(): void
    {
        $xml = "<Foo>[[bar]]</Foo>";
        $r = $this->processInternalLinksExtCollect($xml);
        $this->assertEquals(
            "bar",
            $r[0]["nt"]->mTextform
        );
        $this->assertEquals(
            "bar",
            $r[0]["text"]
        );
    }

    public function testProcessInternalLinksExtCollectMultipleSimple(): void
    {
        $xml = "<Foo>[[bar]]</Foo><Par>[[bar1]] some text [[bar2]]</Par>";
        $r = $this->processInternalLinksExtCollect($xml);
        $this->assertEquals(
            "bar",
            $r[0]["nt"]->mTextform
        );
        $this->assertEquals(
            "bar1",
            $r[1]["nt"]->mTextform
        );
        $this->assertEquals(
            "bar2",
            $r[2]["nt"]->mTextform
        );
    }

    public function testProcessInternalLinksExtCollectMultipleSame(): void
    {
        $xml = "<Foo>[[bar]]</Foo><Par>[[bar1]] some text [[bar]]</Par>";
        $r = $this->processInternalLinksExtCollect($xml);
        $this->assertEquals(
            "bar",
            $r[0]["nt"]->mTextform
        );
        $this->assertEquals(
            "bar1",
            $r[1]["nt"]->mTextform
        );
        $this->assertEquals(
            "bar",
            $r[2]["nt"]->mTextform
        );
    }

    public function testProcessInternalLinksExtCollectOneText(): void
    {
        $xml = "<Foo>[[bar|some text]]</Foo>";
        $r = $this->processInternalLinksExtCollect($xml);
        $this->assertEquals(
            "bar",
            $r[0]["nt"]->mTextform
        );
        $this->assertEquals(
            "some text",
            $r[0]["text"]
        );
    }

    public function testProcessInternalLinksExtCollectMultiText(): void
    {
        $xml = "<Foo>lore [[bar|some text]] ipsum</Foo><Par>More [[second link|some text for second]]</Par>";
        $r = $this->processInternalLinksExtCollect($xml);
        $this->assertEquals(
            "bar",
            $r[0]["nt"]->mTextform
        );
        $this->assertEquals(
            "some text",
            $r[0]["text"]
        );
        $this->assertEquals(
            "second link",
            $r[1]["nt"]->mTextform
        );
        $this->assertEquals(
            "some text for second",
            $r[1]["text"]
        );
    }

    protected function processInternalLinksCollect(string $xml):array
    {
        return ilWikiUtil::collectInternalLinks(
            $xml,
            0,
            true,
            IL_WIKI_MODE_COLLECT
        );
    }

    public function testProcessInternalLinksCollectOneSimple(): void
    {
        $xml = "<Foo>[[bar]]</Foo>";
        $r = $this->processInternalLinksCollect($xml);
        $this->assertEquals(
            ["bar"],
            $r
        );
    }

    public function testProcessInternalLinksCollectMultipleSame(): void
    {
        $xml = "<Foo>[[bar]]</Foo><Par>[[bar1]] some text [[bar]]</Par>";
        $r = $this->processInternalLinksCollect($xml);
        $this->assertEquals(
            ["bar", "bar1"],
            $r
        );
    }

    public function testProcessInternalLinksCollectMultiText(): void
    {
        $xml = "<Foo>lore [[bar|some text]] ipsum</Foo><Par>More [[second link|some text for second]]</Par>";
        $r = $this->processInternalLinksCollect($xml);
        $this->assertEquals(
            ["bar", "second link"],
            $r
        );
    }

    protected function processInternalLinksReplace(string $xml):string
    {
        return ilWikiUtil::replaceInternalLinks(
            $xml,
            0,
            false
        );
    }

    public function testProcessInternalLinksReplaceWithoutLink(): void
    {
        $xml = "<Foo>Some text without a link</Par>";
        $r = $this->processInternalLinksReplace($xml);
        $this->assertEquals(
            $xml,
            $r
        );
    }

    /*
    public function testProcessInternalLinksReplaceSimple(): void
    {
        $xml = "<Foo>Some text with [[simple]] a link</Par>";
        $r = $this->processInternalLinksReplace($xml);
        $this->assertEquals(
            "todo",
            $r
        );
    }*/

}
