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
 * Collects actions from all action providers
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionCollector
{
    protected static array $instances = array();

    protected ilUserActionCollection $collection;
    protected int $user_id;
    protected ilUserActionContext $action_context;

    protected function __construct(int $a_user_id, ilUserActionContext $a_context)
    {
        $this->user_id = $a_user_id;
        $this->action_context = $a_context;
    }


    /**
     * Get instance (for a user)
     */
    public static function getInstance(
        int $a_user_id,
        ilUserActionContext $a_context
    ) : self {
        if (!isset(self::$instances[$a_user_id])) {
            self::$instances[$a_user_id] = new ilUserActionCollector($a_user_id, $a_context);
        }

        return self::$instances[$a_user_id];
    }

    public function getActionsForTargetUser(int $a_target_user) : ilUserActionCollection
    {
        // overall collection of users
        $this->collection = ilUserActionCollection::getInstance();
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
