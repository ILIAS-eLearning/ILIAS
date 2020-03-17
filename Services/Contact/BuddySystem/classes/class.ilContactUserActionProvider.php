<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/Actions/classes/class.ilUserActionProvider.php';

/**
 * Adds link to contact
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilContactUserActionProvider extends ilUserActionProvider
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->user = $DIC['ilUser'];
    }

    /**
     * @inheritdoc
     */
    public function getComponentId()
    {
        return "contact";
    }

    /**
     * @inheritdoc
     */
    public function getActionTypes()
    {
        $this->lng->loadLanguageModule('buddysystem');
        return array(
            "handle_req" => $this->lng->txt("buddy_handle_contact_request")
        );
    }

    /**
     * @var array
     */
    private static $state_to_perm_link_map = array(
        'ilBuddySystemLinkedRelationState' => '_contact_approved',
        'ilBuddySystemIgnoredRequestRelationState' => '_contact_ignored'
    );

    /**
     * {@inheritdoc}
     */
    public function collectActionsForTargetUser($a_target_user)
    {
        require_once 'Services/User/Actions/classes/class.ilUserAction.php';
        $coll = ilUserActionCollection::getInstance();

        require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            return $coll;
        }

        if (ilObjUser::_isAnonymous($this->getUserId()) || $this->user->isAnonymous()) {
            return $coll;
        }

        require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
        $buddylist = ilBuddyList::getInstanceByGlobalUser();
        $requested_contacts = $buddylist->getRequestRelationsForOwner()->getKeys();

        if (in_array($a_target_user, $requested_contacts)) {
            require_once 'Services/Utilities/classes/class.ilStr.php';
            require_once 'Services/Link/classes/class.ilLink.php';

            $this->lng->loadLanguageModule('buddysystem');

            $relation = $buddylist->getRelationByUserId($a_target_user);
            foreach ($relation->getCurrentPossibleTargetStates() as $target_state) {
                $f = new ilUserAction();
                $f->setText(
                    $this->lng->txt('buddy_bs_act_btn_txt_requested_to_' .
                    ilStr::convertUpperCamelCaseToUnderscoreCase($target_state->getName()))
                );
                $f->setType("handle_req");
                $f->setHref(ilLink::_getStaticLink($a_target_user, 'usr', true, self::$state_to_perm_link_map[get_class($target_state)]));
                $f->setData(
                    array(
                    'current-state' => get_class($relation->getState()),
                    'target-state' => get_class($target_state),
                    'buddy-id' => $a_target_user,
                    'action' => $target_state->getAction())
                );
                $coll->addAction($f);
            }
        }

        return $coll;
    }
}
