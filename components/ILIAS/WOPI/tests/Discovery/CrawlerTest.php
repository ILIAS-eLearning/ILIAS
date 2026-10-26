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

namespace ILIAS\WOPI\Discovery;

use PHPUnit\Framework\TestCase;
use ILIAS\Data\URI;

final class CrawlerTest extends TestCase
{
    /**
     * An excerpt of the discovery of OnlyOffice 9.4.
     */
    private const DISCOVERY = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <wopi-discovery>
          <net-zone name="external-https">
            <app name="Word" favIconUrl="https://office.example.org/word.ico">
              <action name="view" ext="docx" urlsrc="https://office.example.org/hosting/wopi/word/view?&lt;rs=DC_LLCC&amp;&gt;" />
              <action name="edit" ext="docx" requires="locks,update" urlsrc="https://office.example.org/hosting/wopi/word/edit?" />
              <action name="editnew" ext="docx" requires="locks,update" urlsrc="https://office.example.org/hosting/wopi/word/edit?" />
              <action name="editnew" ext="dotx" requires="locks,update" newext="docx" urlsrc="https://office.example.org/hosting/wopi/word/edit?" />
              <action name="editnew" ext="" requires="locks,update" urlsrc="https://office.example.org/hosting/wopi/word/edit?" />
              <action name="convert" ext="doc" targetext="docx" urlsrc="https://office.example.org/hosting/wopi/convert-and-edit/doc/docx?" />
              <action name="formsubmit" ext="pdf" urlsrc="https://office.example.org/hosting/wopi/word/submit?" />
              <action name="edit" urlsrc="https://office.example.org/hosting/wopi/word/edit?" />
            </app>
            <app name="Empty">
              <action name="formsubmit" ext="pdf" urlsrc="https://office.example.org/hosting/wopi/word/submit?" />
            </app>
          </net-zone>
          <proof-key value="ignored" modulus="bW9kdWx1cw==" exponent="AQAB" oldvalue="ignored" oldmodulus="b2xk" oldexponent="AQAB" />
        </wopi-discovery>
        XML;

    /**
     * @return array<string, Action> keyed by "name:ext"
     */
    private function actions(): array
    {
        $apps = (new Crawler())->crawlContent(self::DISCOVERY);
        $this->assertNotNull($apps);
        $this->assertCount(1, $apps->getApps());

        $actions = [];
        foreach ($apps->getApps()[0]->getActions() as $action) {
            $actions[$action->getName() . ':' . $action->getExtension()] = $action;
        }

        return $actions;
    }

    public function testDiscoveryUrlMustBeAWebUrl(): void
    {
        $this->assertFalse((new Crawler())->validate(new URI('file://localhost/etc/passwd')));
        $this->assertFalse((new Crawler())->validate(new URI('ftp://office.example.org/discovery')));
    }

    public function testLaunchersAndIconsWhichAreNoWebUrlsAreIgnored(): void
    {
        $apps = (new Crawler())->crawlContent(str_replace(
            [
                'urlsrc="https://office.example.org/hosting/wopi/word/view?',
                'favIconUrl="https://office.example.org/word.ico"',
            ],
            [
                'urlsrc="javascript://x/%0Aalert(1)%2F%2F?',
                'favIconUrl="file://localhost/word.ico"',
            ],
            self::DISCOVERY
        ));

        $this->assertNotNull($apps);
        $app = $apps->getApps()[0];
        $this->assertNull($app->getFavicon());
        foreach ($app->getActions() as $action) {
            $this->assertSame('https', $action->getLauncherUrl()->getSchema());
            $this->assertNotSame('view', $action->getName());
        }
    }

    public function testInvalidContentIsRejected(): void
    {
        $this->assertNull((new Crawler())->crawlContent('this is not xml'));
    }

    public function testOnlyActionsWithNameExtensionAndUrlAreCrawledAndUnsupportedActionsAreIgnored(): void
    {
        $this->assertSame(
            ['view:docx', 'edit:docx', 'editnew:docx', 'editnew:dotx', 'editnew:', 'convert:doc'],
            array_keys($this->actions())
        );
    }

    public function testAppsWithoutCrawledActionsAreSkipped(): void
    {
        $apps = (new Crawler())->crawlContent(self::DISCOVERY);

        $this->assertSame(['Word'], array_map(static fn(App $app): string => $app->getName(), $apps->getApps()));
    }

    public function testUrlParametersAreSplitOff(): void
    {
        $view = $this->actions()['view:docx'];

        $this->assertSame('https://office.example.org/hosting/wopi/word/view', (string) $view->getLauncherUrl());
        $this->assertSame('<rs=DC_LLCC&>', $view->getUrlAppendix());
    }

    public function testProofKeysAreRead(): void
    {
        $crawler = new Crawler();
        $crawler->crawlContent(self::DISCOVERY);
        $keys = $crawler->getProofKeys();

        $this->assertSame('bW9kdWx1cw==', $keys->current->modulus);
        $this->assertSame('AQAB', $keys->current->exponent);
        $this->assertSame('b2xk', $keys->old->modulus);
    }

    public function testDiscoveryWithoutProofKeysHasNone(): void
    {
        $crawler = new Crawler();
        $crawler->crawlContent(preg_replace('/<proof-key[^>]*>/', '', self::DISCOVERY));

        $this->assertNull($crawler->getProofKeys());
    }

    public function testTargetExtensionIsReadFromTargetextAndNewext(): void
    {
        $actions = $this->actions();

        $this->assertSame('docx', $actions['convert:doc']->getTargetExtension());
        $this->assertSame('docx', $actions['editnew:dotx']->getTargetExtension());
        $this->assertNull($actions['editnew:docx']->getTargetExtension());
    }
}
