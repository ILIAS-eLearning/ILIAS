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

class ilDataCollectionMailNotification extends ilMailNotification
{
    public const TYPE_RECORD_CREATE = 1;
    public const TYPE_RECORD_UPDATE = 2;
    public const TYPE_RECORD_DELETE = 3;

    private int $actor;
    private ilDclBaseRecordModel $record;

    public function addRecipient(int $a_rcp): void
    {
        $this->recipients[] = $a_rcp;
    }

    public function getActor(): int
    {
        return $this->actor;
    }

    public function setActor(int $actor): void
    {
        $this->actor = $actor;
    }

    public function getRecord(): ilDclBaseRecordModel
    {
        return $this->record;
    }

    public function setRecord(ilDclBaseRecordModel $record): void
    {
        $this->record = $record;
    }

    public function send(): void
    {
        $title = ilObject::_lookupTitle($this->getObjId());
        $actor = ilUserUtil::getNamePresentation($this->actor);
        $table = $this->record->getTable()->getTitle();
        $link = ilLink::_getLink($this->getRefId());
        foreach ($this->recipients as $user_id) {
            $views = $this->record->getTable()->getVisibleTableViews($user_id);
            $visible_ids = [];
            foreach ($views as $view) {
                foreach ($view->getVisibleFields() as $field) {
                    $visible_ids[] = $field->getId();
                }
            }
            $visible_ids = array_unique($visible_ids);
            $lng = ilLanguageFactory::_getLanguageOfUser($user_id);
            $lng->loadLanguageModule('dcl');

            $this->initMail();
            $this->setSubject(sprintf($lng->txt('dcl_change_notification_subject'), $title));

            $this->setBody('');
            $this->appendBody(ilMail::getSalutation($user_id, $lng));
            $this->appendBody($this->getAction($lng));
            $this->appendBody($lng->txt('obj_dcl') . ": $title");
            $this->appendBody($lng->txt('dcl_table') . ": $table");
            if ($visible_ids !== []) {
                $message = $lng->txt('dcl_record') . ":\n";
                $message .= "------------------------------------\n";
                foreach ($this->record->getTable()->getFields() as $field) {
                    if (in_array($field->getId(), $visible_ids)) {
                        $value = '-undefined-';
                        if ($field->isStandardField()) {
                            $value = $this->record->getStandardFieldPlainText($field->getId());
                        } elseif ($record_field = $this->record->getRecordField((int) $field->getId())) {
                            $record_field->setUser(new ilObjUser($user_id));
                            $value = $record_field->getPlainText();
                        }
                        $message .= $field->getTitle() . ': ' . $value . "\n";
                    }
                }
                $message .= "------------------------------------";
                $this->appendBody($message);
            }

            $this->appendBody($lng->txt('dcl_changed_by') . ": $actor");
            $this->appendBody($lng->txt('dcl_change_notification_link') . ": $link");
            $this->getMail()->appendInstallationSignature(true);
            $this->sendMail([$user_id]);
        }
    }

    private function getAction(ilLanguage $lng): string
    {
        switch ($this->getType()) {
            case self::TYPE_RECORD_CREATE:
                return $lng->txt('dcl_change_notification_dcl_new_record');
            case self::TYPE_RECORD_UPDATE:
                return $lng->txt('dcl_change_notification_dcl_update_record');
            case self::TYPE_RECORD_DELETE:
                return $lng->txt('dcl_change_notification_dcl_delete_record');
            default:
                return '';
        }
    }

    protected function appendBody(string $a_body): string
    {
        return parent::appendBody($a_body . "\n\n");
    }
}
