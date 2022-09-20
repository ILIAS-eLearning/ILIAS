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

/**
 * News data
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsData
{
    protected ilNewsServiceDependencies $_deps;
    protected ilNewsService $service;

    public function __construct(
        ilNewsService $service,
        ilNewsServiceDependencies $_deps
    ) {
        $this->service = $service;
        $this->_deps = $_deps;
    }

    /**
     * Save news item
     */
    public function save(ilNewsItem $news_item): int
    {
        if ($news_item->getId() > 0) {
            $news_item->update(true);
        } else {
            $news_item->create();
        }
        return $news_item->getId();
    }

    /**
     * Get news of context
     *
     * @param ilNewsContext $context
     * @return ilNewsItem[]
     */
    public function getNewsOfContext(ilNewsContext $context): array
    {
        return ilNewsItem::getNewsOfContext(
            $context->getObjId(),
            $context->getObjType(),
            $context->getSubId(),
            $context->getSubType()
        );
    }

    /**
     * Delete a news item
     * @param ilNewsItem $news_item
     */
    public function delete(ilNewsItem $news_item): void
    {
        $news_item->delete();
    }
}
