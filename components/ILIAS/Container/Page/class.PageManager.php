<?php

/* Copyright (c) 1998-2022 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Container\Page;

use ILIAS\Container\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageManager
{
    protected InternalDomainService $domain_service;
    protected \ilContainer $container;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function __construct(
        InternalDomainService $domain_service,
        \ILIAS\Style\Content\DomainService $content_style_domain,
        \ilContainer $container
    ) {
        $this->content_style_domain = $content_style_domain;
        $this->domain_service = $domain_service;
        $this->container = $container;
    }

    public function getHtml(): string
    {
        $settings = $this->domain_service->settings();
        $user = $this->domain_service->user();

        if (!$settings->get("enable_cat_page_edit") || $this->container->filteredSubtree()) {
            return "";
        }

        // if page does not exist, return nothing
        if (!\ilPageUtil::_existsAndNotEmpty(
            "cont",
            $this->container->getId()
        )) {
            return "";
        }

        // get page object
        $ot = \ilObjectTranslation::getInstance($this->container->getId());
        $lang = $ot->getEffectiveContentLang($user->getCurrentLanguage(), "cont");
        $page_gui = new \ilContainerPageGUI($this->container->getId(), 0, $lang);
        $style = $this->content_style_domain->styleForRefId($this->container->getRefId());
        $page_gui->setStyleId($style->getEffectiveStyleId());

        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $html = $page_gui->showPage();

        return $html;
    }
}
