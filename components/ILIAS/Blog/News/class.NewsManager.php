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

namespace ILIAS\Blog\News;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalRepoService;
use ILIAS\Blog\InternalDomainService;

/**
 * Domain class for handling news items of blog postings.
 */
class NewsManager
{
    protected \ILIAS\Blog\Posting\Service\GUIService $posting_gui;

    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain,
        \ILIAS\Blog\InternalGUIService $gui
    ) {
        $this->posting_gui = $gui->posting();
    }

    /**
     * Handle news item for a blog posting.
     */
    public function handle(\ilBlogPosting $page, bool $update = false): void
    {
        $lng = $this->domain->lng();
        $ilUser = $this->domain->user();

        if (!$page->getActive()) {
            return;
        }

        $news_item = null;

        if ($update) {
            $news_id = \ilNewsItem::getLastNewsIdForContext(
                $page->getBlogId(),
                "blog",
                $page->getId(),
                $page->getParentType(),
                true
            );
            if ($news_id > 0) {
                $news_item = new \ilNewsItem($news_id);
            }
        }

        if (!$news_item) {
            $news_set = new \ilSetting("news");
            $default_visibility = $news_set->get("default_visibility", "users");

            $news_item = new \ilNewsItem();
            $news_item->setContext(
                $page->getBlogId(),
                "blog",
                $page->getId(),
                $page->getParentType()
            );
            $news_item->setPriority(NEWS_NOTICE);
            $news_item->setVisibility($default_visibility);
        }

        $news_item->setUserId($ilUser->getId());

        $news_item->setTitle($page->getTitle());

        $contentKey = $update
            ? "blog_news_posting_updated"
            : "blog_news_posting_published";
        $content = sprintf(
            $lng->txt($contentKey),
            \ilUserUtil::getNamePresentation($ilUser->getId())
        );

        $contributors = [];
        foreach (\ilBlogPosting::getPageContributors($page->getParentType(), $page->getId()) as $user) {
            $contributors[] = $user["user_id"];
        }
        if (count($contributors) > 1 || !in_array($page->getAuthor(), $contributors, true)) {
            $authors = [\ilUserUtil::getNamePresentation($page->getAuthor())];
            foreach ($contributors as $user_id) {
                if ($user_id !== $page->getAuthor()) {
                    $authors[] = \ilUserUtil::getNamePresentation($user_id);
                }
            }
            $content .= "\n" . sprintf(
                $lng->txt("blog_news_posting_authors"),
                implode(", ", $authors)
            );
        }

        $news_item->setContentTextIsLangVar(false);
        $news_item->setContent($content);

        $snippet = $this->posting_gui->getSnippet($page->getId());
        $news_item->setContentLong($snippet);

        if (!$news_item->getId()) {
            $news_item->create();
        } else {
            $news_item->update(true);
        }
    }
}
