<?php

/**
 * Trait ilObjFileNews
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilObjFileNews
{
    /**
     * @var bool
     */
    protected $just_notified = false;

    public function notifyCreation(int $obj_id, string $additional_message = null) : void
    {
        $this->addNewsNotification($obj_id, 'file_created', $additional_message);
        $this->just_notified = true;
    }

    public function notifyUpdate(int $obj_id, string $additional_message = null) : void
    {
        if (!$this->just_notified) {
            $this->addNewsNotification($obj_id, 'file_updated', $additional_message);
            $this->just_notified = true;
        }
    }

    protected function addNewsNotification(int $obj_id, string $a_lang_var, string $description = null) : void
    {
        global $DIC;

        // ilHistory::_createEntry($this->getId(), "create", $this->getFileName() . ",1" . ",1");

        // Add Notification to news
        $news_item = new ilNewsItem();
        $news_item->setContext($obj_id, 'file');
        $news_item->setPriority(NEWS_NOTICE);
        $news_item->setTitle($a_lang_var);
        $news_item->setContentIsLangVar(true);
        if ($description && $description !== '') {
            $news_item->setContent("<p>" . $description . "</p>");
        }
        $news_item->setUserId($DIC->user()->getId());
        $news_item->setVisibility(NEWS_USERS);
        $news_item->create();
    }
}
