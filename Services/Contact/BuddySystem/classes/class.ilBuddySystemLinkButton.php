<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemLinkButton
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemLinkButton implements ilBuddySystemLinkButtonType
{
    /** @var int */
    protected $usrId;

    /** @var ilBuddyList */
    protected $buddyList;

    /** @var ilLanguage */
    protected $lng;

    /** @var ilObjUser */
    protected $user;

    /**
     * ilBuddySystemLinkButton constructor.
     * @param int $usrId
     * @throws ilBuddySystemException
     */
    protected function __construct(int $usrId)
    {
        global $DIC;

        $this->usrId = $usrId;
        $this->buddyList = ilBuddyList::getInstanceByGlobalUser();

        $this->user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];
    }

    /**
     * @param int $usrId
     * @return ilBuddySystemLinkButton
     * @throws ilBuddySystemException
     */
    public static function getInstanceByUserId(int $usrId) : self
    {
        return new self($usrId);
    }

    /**
     * @inheritDoc
     */
    public function getUsrId() : int
    {
        return $this->usrId;
    }

    /**
     * @param int $usrId
     */
    public function setUsrId(int $usrId)
    {
        $this->usrId = $usrId;
    }

    /**
     * @inheritDoc
     */
    public function getBuddyList() : ilBuddyList
    {
        return $this->buddyList;
    }

    /**
     * @return string
     */
    public function getHtml() : string
    {
        $this->lng->loadLanguageModule('buddysystem');

        if (!ilBuddySystem::getInstance()->isEnabled()) {
            return '';
        }

        $relation = $this->buddyList->getRelationByUserId($this->getUsrId());

        // The ILIAS JF decided to add a new personal setting
        if (
            $relation->isUnlinked() &&
            !ilUtil::yn2tf(ilObjUser::_lookupPref($this->getUsrId(), 'bs_allow_to_contact_me'))
        ) {
            return '';
        }

        $buttonTemplate = new ilTemplate(
            'tpl.buddy_system_link_button.html',
            true,
            true,
            'Services/Contact/BuddySystem'
        );
        $buttonTemplate->setVariable(
            'BUTTON_HTML',
            ilBuddySystemRelationStateFactory::getInstance()->getStateButtonRendererByOwnerAndRelation(
                (int) $this->user->getId(),
                $relation
            )->getHtml()
        );
        $buttonTemplate->setVariable('BUTTON_BUDDY_ID', $this->getUsrId());
        $buttonTemplate->setVariable('BUTTON_CSS_CLASS', 'ilBuddySystemLinkWidget');
        $buttonTemplate->setVariable('BUTTON_CURRENT_STATE', get_class($relation->getState()));

        return $buttonTemplate->get();
    }
}
