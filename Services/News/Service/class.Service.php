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

namespace ILIAS\News;

use ILIAS\DI\Container;
use ILIAS\News\Items\NewsItemManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Service
{
    protected \ilObjUser $user;
    protected \ilNewsObjectAdapter $obj_adapter;
    protected Container $DIC;

    public function __construct(Container $DIC)
    {
        $this->DIC = $DIC;
        $this->obj_adapter = new \ilNewsObjectAdapter();
        $this->user = $DIC->user();
    }

    /**
     * Internal service, do not use in other components
     */
    public function internal(): InternalService
    {
        return new InternalService($this->DIC);
    }

    public function data(): NewsItemManager
    {
        return new NewsItemManager();
    }

    /**
     * Get a new news item for a context
     */
    public function item(\ilNewsContext $context): \ilNewsItem
    {
        $news = new \ilNewsItem();
        $news->setContext($context->getObjId(), $context->getObjType(), $context->getSubId(), $context->getSubType());
        $news->setPriority(NEWS_NOTICE);
        $news->setUserId($this->user->getId());
        return $news;
    }

    /**
     * Get context object for news
     */
    public function contextForRefId(
        int $ref_id,
        int $subid = 0,
        string $subtype = ""
    ): \ilNewsContext {
        $obj_id = $this->obj_adapter->getObjIdForRefId($ref_id);
        $obj_type = $this->obj_adapter->getTypeForObjId($obj_id);
        return new \ilNewsContext($obj_id, $obj_type, $subid, $subtype);
    }
}
