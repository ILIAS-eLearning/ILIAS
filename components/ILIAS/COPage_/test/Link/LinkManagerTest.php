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

namespace ILIAS\COPage\Test\Link;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\Link\LinkManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LinkManagerTest extends \COPageTestBase
{
    public function testGetInternalLinks(): void
    {
        $page = $this->getEmptyPageWithDom();
        $lm = new LinkManager();

        $cases = [
            'xx [iln cat="106"] xx [/iln] xx' => [
                "Target" => "il__obj_106",
                "Type" => "RepositoryItem",
                "TargetFrame" => "",
                "Anchor" => ""
            ],
            'xx [iln page="107"] xx [/iln] xx' => [
                "Target" => "il__pg_107",
                "Type" => "PageObject",
                "TargetFrame" => "",
                "Anchor" => ""
            ],
            'xx [iln chap="106"] xx [/iln] xx' => [
                "Target" => "il__st_106",
                "Type" => "StructureObject",
                "TargetFrame" => "",
                "Anchor" => ""
            ],
            'xx [iln inst="123" page="106"] xx [/iln] xx' => [
                "Target" => "il_123_pg_106",
                "Type" => "PageObject",
                "TargetFrame" => "",
                "Anchor" => ""
            ],
            'xx [iln page="106" target="New" anchor="test"] xx [/iln] xx' => [
                "Target" => "il__pg_106",
                "Type" => "PageObject",
                "TargetFrame" => "New",
                "Anchor" => "test"
            ],
            'xx [iln term="106" target="New"] xx [/iln] xx' => [
                "Target" => "il__git_106",
                "Type" => "GlossaryItem",
                "TargetFrame" => "New",
                "Anchor" => ""
            ],
            'xx [iln wpage="106" anchor="test"] xx [/iln] xx' => [
                "Target" => "il__wpage_106",
                "Type" => "WikiPage",
                "TargetFrame" => "",
                "Anchor" => "test"
            ],
            'xx [iln ppage="106"] xx [/iln] xx' => [
                "Target" => "il__ppage_106",
                "Type" => "PortfolioPage",
                "TargetFrame" => "",
                "Anchor" => ""
            ],
            'xx [iln media="545"/] xx ' => [
                "Target" => "il__mob_545",
                "Type" => "MediaObject",
                "TargetFrame" => "",
                "Anchor" => ""
            ],
            'xx [iln media="108" target="Media"] xx [/iln] xx' => [
                "Target" => "il__mob_108",
                "Type" => "MediaObject",
                "TargetFrame" => "Media",
                "Anchor" => ""
            ],
            'xx [iln dfile="546"] xx [/iln] xx' => [
                "Target" => "il__dfile_546",
                "Type" => "File",
                "TargetFrame" => "",
                "Anchor" => ""
            ]
        ];

        foreach ($cases as $html => $expected) {
            $html = $this->legacyHtmlToXml(
                '<div id="1:1234" class="ilc_text_block_Standard">' . $html . '</div>'
            );
            $this->insertParagraphAt($page, "pg", $html);
            $page->insertPCIds();

            $links = $lm->getInternalLinks($page->getDomDoc());

            //var_dump($links);
            //exit;

            $this->assertEquals(
                $expected,
                current($links)
            );
        }
    }

    public function testContainsFileLinkId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $lm = new LinkManager();

        $html = 'xx [iln dfile="546"] xx [/iln] xx';
        $html = $this->legacyHtmlToXml(
            '<div id="1:1234" class="ilc_text_block_Standard">' . $html . '</div>'
        );
        $this->insertParagraphAt($page, "pg", $html);
        $page->insertPCIds();

        $this->assertEquals(
            true,
            $lm->containsFileLinkId($page->getDomDoc(), "il__file_546")
        );

        $this->assertEquals(
            true,
            $lm->containsFileLinkId($page->getDomDoc(), "il__dfile_546")
        );

        $this->assertEquals(
            false,
            $lm->containsFileLinkId($page->getDomDoc(), "il__file_555")
        );
    }

    public function testExtractFileFromLinkId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $lm = new LinkManager();

        $this->assertEquals(
            555,
            $lm->extractFileFromLinkId("il__file_555")
        );
    }

    public function testResolveInternalLinks(): void
    {
        $lm = new LinkManager();

        $cases = [
            [
                "html" => 'xx [iln inst="20" page="107"] xx [/iln] xx',
                "map" => [
                    "il_20_pg_107" => "il_0_pg_108"
                ],
                "expected" => '<IntLink Target="il__pg_108" Type="PageObject">'
            ],
            [
                "html" => 'xx [iln inst="20" chap="11"] xx [/iln] xx',
                "map" => [
                    "il_20_st_11" => "il_0_st_12"
                ],
                "expected" => '<IntLink Target="il__st_12" Type="StructureObject">'
            ]
        ];

        foreach ($cases as $case) {
            $page = $this->getEmptyPageWithDom();
            $html = $this->legacyHtmlToXml(
                '<div id="1:1234" class="ilc_text_block_Standard">' . $case["html"] . '</div>'
            );
            $this->insertParagraphAt($page, "pg", $html);
            $page->insertPCIds();

            $dom = $page->getDomDoc();
            $links = $lm->resolveIntLinks($dom, $case["map"]);

            $this->assertStringContainsString(
                $case["expected"],
                $page->getXMLFromDom()
            );
        }
    }

    public function testMoveInternalLinks(): void
    {
        $lm = new LinkManager();

        $cases = [
            [
                "html" => 'xx [iln page="107"] xx [/iln] xx',
                "map" => [
                    107 => 108
                ],
                "expected" => '<IntLink Target="il__pg_108" Type="PageObject">'
            ],
            [
                "html" => 'xx [iln chap="10"] xx [/iln] xx',
                "map" => [
                    10 => 11
                ],
                "expected" => '<IntLink Target="il__st_11" Type="StructureObject">'
            ]
        ];

        foreach ($cases as $case) {
            $page = $this->getEmptyPageWithDom();
            $html = $this->legacyHtmlToXml(
                '<div id="1:1234" class="ilc_text_block_Standard">' . $case["html"] . '</div>'
            );
            $this->insertParagraphAt($page, "pg", $html);
            $page->insertPCIds();

            $dom = $page->getDomDoc();
            $links = $lm->moveIntLinks($dom, $case["map"], function (int $id) {
                $type = [
                    11 => "st",
                    108 => "pg"
                ];
                return $type[$id];
            });

            $this->assertStringContainsString(
                $case["expected"],
                $page->getXMLFromDom()
            );
        }
    }
}
