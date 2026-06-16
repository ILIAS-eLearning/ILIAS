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

namespace ILIAS\COPage;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\COPage\Page\PageManagerInterface;
use ILIAS\COPage\Compare\PageCompare;
use ILIAS\COPage\Page\PageContentManager;
use ILIAS\COPage\PC\PCDefinition;
use ILIAS\COPage\Link\LinkManager;
use ILIAS\COPage\Style\StyleManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected ?\ilLogger $copg_log = null;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
    }

    public function pc(?PCDefinition $def = null): PC\DomainService
    {
        return new PC\DomainService(
            $this->data_service,
            $this->repo_service,
            $this,
            $def
        );
    }

    public function history(): History\HistoryManager
    {
        return new History\HistoryManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function historyRetrieval(int $page_id, string $parent_type, string $lang): History\HistoryRetrieval
    {
        return new History\HistoryRetrieval(
            $this->repo_service->history(),
            $this->DIC->database(),
            $page_id,
            $parent_type,
            $lang
        );
    }

    public function xsl(): Xsl\XslManager
    {
        return new Xsl\XslManager();
    }

    public function domUtil(): Dom\DomUtil
    {
        return new Dom\DomUtil();
    }

    public function page(): Page\PageManagerInterface
    {
        return new Page\PageManager();
    }

    public function pageConfig(string $parent_type): \ilPageConfig
    {
        $def = \ilCOPageObjDef::getDefinitionByParentType($parent_type);
        $class = $def["class_name"] . "Config";
        $cfg = new $class();
        return $cfg;
    }

    public function htmlTransformUtil(): Html\TransformUtil
    {
        return new Html\TransformUtil();
    }

    public function contentIds(\ilPageObject $page): ID\ContentIdManager
    {
        return new ID\ContentIdManager($page);
    }

    public function contentIdGenerator(): ID\ContentIdGenerator
    {
        return new ID\ContentIdGenerator();
    }

    public function compare(): PageCompare
    {
        return new PageCompare();
    }

    public function link(): LinkManager
    {
        return new LinkManager();
    }

    public function style(): StyleManager
    {
        return new StyleManager();
    }

    public function log(): ?\ilLogger
    {
        if (isset($this->DIC["ilLoggerFactory"])) {
            if (is_null($this->copg_log)) {
                $this->copg_log = $this->logger()->copg();
            }
            return $this->copg_log;
        }
        return null;
    }

    public function layoutRetrieval(): Layout\PageLayoutRetrieval
    {
        return new Layout\PageLayoutRetrieval();
    }

    public function testQuestion(): \ILIAS\TestQuestionPool\Questions\PublicInterface
    {
        return $this->DIC->testQuestion();
    }
}
