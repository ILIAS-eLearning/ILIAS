<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News service
 *
 * @author killing@leifos.de
 * @ingroup ServiceNews
 */
class ilNewsService
{
    /**
     * @var ilNewsServiceDependencies
     */
    protected $_deps;

    /**
     * Constructor
     * @param ilLanguage $lng
     */
    public function __construct(ilLanguage $lng, ilSetting $settings, ilObjUser $user, ilNewsObjectAdapterInterface $obj_adapter = null)
    {
        if (is_null($obj_adapter)) {
            $obj_adapter = new ilNewsObjectAdapter();
        }
        $this->_deps = new ilNewsServiceDependencies($lng, $settings, $user, $obj_adapter);
    }

    /**
     * @inheritdoc
     */
    public function data() : ilNewsData
    {
        return new ilNewsData($this, $this->_deps);
    }

    /**
     * Get a new news item for a context
     *
     * @param ilNewsContext $context
     * @return ilNewsItem
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
     *
     * @param int $ref_id
     * @param string $subtype
     * @param int $subid
     * @return ilNewsContext
     */
    public function contextForRefId(int $ref_id, int $subid = 0, string $subtype = "") : ilNewsContext
    {
        $obj_id = $this->_deps->obj()->getObjIdForRefId($ref_id);
        $obj_type = $this->_deps->obj()->getTypeForObjId($obj_id);
        return new ilNewsContext($obj_id, $obj_type, $subid, $subtype);
    }
}
