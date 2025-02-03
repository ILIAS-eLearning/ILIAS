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

namespace ILIAS\Container\Page;

use ILIAS\Container\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageManager
{
    protected ?string $lang = null;
    protected InternalDomainService $domain_service;
    protected \ilContainer $container;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function __construct(
        InternalDomainService $domain_service,
        \ILIAS\Style\Content\DomainService $content_style_domain,
        \ilContainer $container,
        ?string $lang = null
    ) {
        $this->content_style_domain = $content_style_domain;
        $this->domain_service = $domain_service;
        $this->container = $container;
        $user = $this->domain_service->user();
        if (is_null($lang)) {
            $ot = \ilObjectTranslation::getInstance($this->container->getId());
            $this->lang = $ot->getEffectiveContentLang($user->getCurrentLanguage(), "cont");
        } else {
            $this->lang = $lang;
        }
    }

    public function getHtml(): string
    {
        $settings = $this->domain_service->settings();

        if (!$settings->get("enable_cat_page_edit") || $this->container->filteredSubtree()) {
            return "";
        }

        // if page does not exist, return nothing
        if (!\ilPageUtil::_existsAndNotEmpty(
            "cont",
            $this->container->getId(),
            $this->lang
        )) {
            return "";
        }

        // get page object
        $page_gui = new \ilContainerPageGUI($this->container->getId(), 0, $this->lang);
        $style = $this->content_style_domain->styleForRefId($this->container->getRefId());
        $page_gui->setStyleId($style->getEffectiveStyleId());

        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $html = $page_gui->showPage();

        return $html;
    }

    public function getDom(): ?\DOMDocument
    {
        $settings = $this->domain_service->settings();

        if (!$settings->get("enable_cat_page_edit") || $this->container->filteredSubtree()) {
            return null;
        }

        // if page does not exist, return nothing
        if (!\ilPageUtil::_existsAndNotEmpty(
            "cont",
            $this->container->getId(),
            $this->lang
        )) {
            return null;
        }

        // get page object
        $page_gui = new \ilContainerPageGUI($this->container->getId(), 0, $this->lang);
        $page = $page_gui->getPageObject();
        $page->buildDom();
        return $page->getDomDoc();
    }

}
