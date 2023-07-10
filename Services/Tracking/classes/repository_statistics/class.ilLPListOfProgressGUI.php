<?php

declare(strict_types=0);

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
 * Class ilLPListOfProgress
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilLPListOfProgressGUI: ilLPProgressTableGUI
 * @package      ilias-tracking
 */
class ilLPListOfProgressGUI extends ilLearningProgressBaseGUI
{
    protected ?ilObjUser $tracked_user = null;
    protected int $details_id = 0;
    protected int $details_obj_id = 0;
    protected string $details_type = '';
    protected int $details_mode = 0;

    public function __construct(int $a_mode, int $a_ref_id, int $a_user_id = 0)
    {
        parent::__construct($a_mode, $a_ref_id, $a_user_id);
        $this->__initUser($a_user_id);

        // Set item id for details
        $this->__initDetails($this->initDetailsIdFromQuery());
        $this->ctrl->saveParameter($this, 'details_id');
    }

    protected function initDetailsIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('details_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'details_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        $this->ctrl->setReturn($this, "show");
        $this->ctrl->setParameter($this, 'user_id', $this->getUserId());
        switch ($this->ctrl->getNextClass()) {
            case 'illpprogresstablegui':
                $table_gui = new ilLPProgressTableGUI(
                    $this,
                    "",
                    $this->tracked_user
                );
                $this->ctrl->setReturn($this, 'show');
                $this->ctrl->forwardCommand($table_gui);
                break;

            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();
        }
    }

    public function show(): void
    {
        switch ($this->getMode()) {
            // Show only detail of current repository item if called from repository
            case self::LP_CONTEXT_REPOSITORY:
                $this->__initDetails($this->getRefId());
                $this->details();
                return;

            case self::LP_CONTEXT_USER_FOLDER:
            case self::LP_CONTEXT_ORG_UNIT:
                // if called from user folder obj_id is id of current user
                $this->__initUser($this->getUserId());
                break;
        }
        // not called from repository
        $this->__showProgressList();
    }

    /**
     *
     */
    protected function saveProgress(): void
    {
        $info = new ilInfoScreenGUI($this);
        $info->setContextRefId($this->ref_id);
        $info->setContextObjId($this->details_obj_id);
        $info->setContentObjType((string) $this->obj_type);
        $info->saveProgress(false);
        $this->ctrl->redirect($this);
    }

    public function details(): void
    {
        // Show back button to crs if called from crs. Otherwise if called from personal desktop or administration
        // show back to list
        $crs_id = 0;
        if ($this->http->wrapper()->query()->has('crs_id')) {
            $crs_id = $this->http->wrapper()->query()->retrieve(
                'crs_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($crs_id) {
            $this->ctrl->setParameter($this, 'details_id', $crs_id);
            $this->toolbar->addButton(
                $this->lng->txt('trac_view_crs'),
                $this->ctrl->getLinkTarget($this, 'details')
            );
        } elseif ($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP or
            $this->getMode() == self::LP_CONTEXT_ADMINISTRATION or
            $this->getMode() == self::LP_CONTEXT_USER_FOLDER) {
            $this->toolbar->addButton(
                $this->lng->txt('trac_view_list'),
                $this->ctrl->getLinkTarget($this, 'show')
            );
        }

        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.lp_progress_container.html',
            'Services/Tracking'
        );

        $info = new ilInfoScreenGUI($this);
        $info->setContextRefId($this->details_id);
        $info->setContextObjId($this->details_obj_id);
        $info->setContentObjType((string) $this->obj_type);
        $info->enableLearningProgress(true);
        $info->setFormAction($this->ctrl->getFormAction($this));
        $this->__appendLPDetails(
            $info,
            $this->details_obj_id,
            $this->tracked_user->getId()
        );
        $this->__showObjectDetails($info, $this->details_obj_id, false);

        // Finally set template variable
        $this->tpl->setVariable("LM_INFO", $info->getHTML());

        $olp = ilObjectLP::getInstance($this->details_obj_id);
        $collection = $olp->getCollectionInstance();
        $obj_ids = array();
        if ($collection) {
            foreach ($collection->getItems() as $item_id) {
                if ($collection instanceof ilLPCollectionOfRepositoryObjects) {
                    $obj_id = ilObject::_lookupObjectId($item_id);
                    if ($this->access->checkAccessOfUser(
                        $this->tracked_user->getId(),
                        'visible',
                        '',
                        $item_id
                    )) {
                        $obj_ids[$obj_id] = array($item_id);
                    }
                } else {
                    $obj_ids[] = $item_id;
                }
            }
        }

        // #15247
        if (count($obj_ids) > 0) {
            // seems obsolete
            $personal_only = !ilLearningProgressAccess::checkPermission(
                'read_learning_progress',
                $this->getRefId()
            );
            $lp_table = new ilLPProgressTableGUI(
                $this,
                "details",
                $this->tracked_user,
                $obj_ids,
                true,
                $this->details_mode,
                $personal_only,
                $this->details_obj_id,
                $this->details_id
            );
            $this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
        }

        $this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
    }

    public function __showProgressList(): void
    {
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.lp_list_progress.html',
            'Services/Tracking'
        );

        // User info
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));
        $lp_table = new ilLPProgressTableGUI(
            $this,
            "",
            $this->tracked_user,
            null,
            false,
            null,
            false,
            null,
            null,
            $this->getMode()
        );
        $this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());

        $this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
    }

    /**
     * @todo check the access checks.
     */
    public function __initUser(int $a_usr_id = 0): bool
    {
        if ($this->http->wrapper()->post()->has('user_id')) {
            $a_usr_id = $this->http->wrapper()->post()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->int()
            );
            $this->ctrl->setParameter($this, 'user_id', $a_usr_id);
        }
        if ($a_usr_id) {
            $user = ilObjectFactory::getInstanceByObjId($a_usr_id);
            if (!$user instanceof ilObjUser) {
                throw new ilObjectNotFoundException(
                    'Invalid user id given: ' . $a_usr_id
                );
            }
            $this->tracked_user = $user;
        } else {
            $this->tracked_user = $this->user;
        }

        // #8762: see ilObjUserGUI->getTabs()
        if ($this->mode == self::LP_CONTEXT_USER_FOLDER &&
            $this->rbacsystem->checkAccess('read', $this->ref_id)) {
            return false;
        }

        if ($this->mode == self::LP_CONTEXT_ORG_UNIT &&
            ilObjOrgUnitAccess::_checkAccessToUserLearningProgress(
                $this->ref_id,
                $a_usr_id
            )) {
            return false;
        }

        // Check access
        if (!$this->rbacreview->isAssigned(
            $this->user->getId(),
            SYSTEM_ROLE_ID
        )) {
            $this->tracked_user = $this->user;
            return false;
        }
        return true;
    }

    public function __initDetails(int $a_details_id): void
    {
        if (!$a_details_id) {
            $a_details_id = $this->getRefId();
        }
        if ($a_details_id) {
            $ref_ids = ilObject::_getAllReferences($a_details_id);

            $this->details_id = $a_details_id;
            $this->details_obj_id = $this->ilObjectDataCache->lookupObjId(
                $this->details_id
            );
            $this->details_type = $this->ilObjectDataCache->lookupType(
                $this->details_obj_id
            );

            $olp = ilObjectLP::getInstance($this->details_obj_id);
            $this->details_mode = $olp->getCurrentMode();
        }
    }
}
