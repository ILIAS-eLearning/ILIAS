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
 * News service
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsService
{
    protected ilNewsServiceDependencies $_deps;

    public function __construct(
        ilLanguage $lng,
        ilSetting $settings,
        ilObjUser $user,
        ilNewsObjectAdapterInterface $obj_adapter = null
    ) {
        if (is_null($obj_adapter)) {
            $obj_adapter = new ilNewsObjectAdapter();
        }
        $this->_deps = new ilNewsServiceDependencies($lng, $settings, $user, $obj_adapter);
    }

    public function data() : ilNewsData
    {
        return new ilNewsData($this, $this->_deps);
    }

    /**
     * Get a new news item for a context
     */
    public function item(ilNewsContext $context) : ilNewsItem
    {
        $news = new ilNewsItem();
        $news->setContext($context->getObjId(), $context->getObjType(), $context->getSubId(), $context->getSubType());
        $news->setPriority(NEWS_NOTICE);
        $news->setUserId($this->_deps->user()->getId());
        return $news;
    }

    /**
     * Get context object for news
     */
    public function contextForRefId(
        int $ref_id,
        int $subid = 0,
        string $subtype = ""
    ) : ilNewsContext {
        $obj_id = $this->_deps->obj()->getObjIdForRefId($ref_id);
        $obj_type = $this->_deps->obj()->getTypeForObjId($obj_id);
        return new ilNewsContext($obj_id, $obj_type, $subid, $subtype);
    }
}
