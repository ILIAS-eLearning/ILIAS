<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collects actions from all action providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionCollector
{
    protected static $instances = array();

    /**
     * @var ilUserActionCollection
     */
    protected $collection;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var ilUserActionContext
     */
    protected $action_context;

    /**
     * Constructor
     *
     * @param int $a_user_id user id (usually the current user logged in)
     * @param ilUserActionContext $a_context
     */
    protected function __construct($a_user_id, ilUserActionContext $a_context)
    {
        $this->user_id = $a_user_id;
        $this->action_context = $a_context;
    }


    /**
     * Get instance (for a user)
     *
     * @param int $a_user_id user id
     * @param ilUserActionContext $a_context
     * @return ilUserActionCollector
     */
    public static function getInstance($a_user_id, ilUserActionContext $a_context)
    {
        if (!isset(self::$instances[$a_user_id])) {
            self::$instances[$a_user_id] = new ilUserActionCollector($a_user_id, $a_context);
        }

        return self::$instances[$a_user_id];
    }

    /**
     * Collect actions
     *
     * @return ilUserActionCollection action
     */
    public function getActionsForTargetUser($a_target_user)
    {
        // overall collection of users
        include_once("./Services/User/Actions/classes/class.ilUserActionCollection.php");
        $this->collection = ilUserActionCollection::getInstance();

        include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");

        include_once("./Services/User/Actions/classes/class.ilUserActionProviderFactory.php");
        foreach (ilUserActionProviderFactory::getAllProviders() as $prov) {
            if (!$this->hasProviderActiveActions($prov)) {
                continue;
            }
            $prov->setUserId($this->user_id);
            $coll = $prov->collectActionsForTargetUser($a_target_user);
            foreach ($coll->getActions() as $action) {
                if (ilUserActionAdmin::lookupActive(
                    $this->action_context->getComponentId(),
                    $this->action_context->getContextId(),
                    $prov->getComponentId(),
                    $action->getType()
                )) {
                    $this->collection->addAction($action);
                }
            }
        }

        return $this->collection;
    }

    protected function hasProviderActiveActions(ilUserActionProvider $prov) : bool
    {
        foreach ($prov->getActionTypes() as $act_type => $act_txt) {
            if (ilUserActionAdmin::lookupActive(
                $this->action_context->getComponentId(),
                $this->action_context->getContextId(),
                $prov->getComponentId(),
                $act_type
            )) {
                return true;
            }
        }
        return false;
    }

}
