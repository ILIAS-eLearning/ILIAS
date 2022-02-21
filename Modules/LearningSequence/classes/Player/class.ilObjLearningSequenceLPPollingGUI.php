<?php declare(strict_types=1);

use ILIAS\HTTP\Wrapper\RequestWrapper;

class ilObjLearningSequenceLPPollingGUI
{
    const PARAM_LSO_LP_OBJID = LSUrlBuilder::PARAM_LSO_PARAMETER;

    protected ilCtrl $ctrl;
    protected int $current_user_id;
    protected ilObjectDataCache $obj_data_cache;
    protected ILIAS\Refinery\Factory $refinery;
    protected RequestWrapper $request_wrapper;

    public function __construct(
        ilCtrl $ctrl,
        int $current_user_id,
        ilObjectDataCache $obj_data_cache,
        ILIAS\Refinery\Factory $refinery,
        ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper
    ) {
        $this->ctrl = $ctrl;
        $this->current_user_id = $current_user_id;
        $this->obj_data_cache = $obj_data_cache;
        $this->refinery = $refinery;
        $this->request_wrapper = $request_wrapper;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case LSControlBuilder::CMD_CHECK_CURRENT_ITEM_LP:
                $this->getCurrentItemLearningProgress();
                // no break
            default:
                throw new ilException("Command not supported: $cmd");
        }
    }
    
    protected function getCurrentItemLearningProgress()
    {
        $obj_id = $this->request_wrapper->retrieve(self::PARAM_LSO_LP_OBJID, $this->refinery->kindlyTo()->int());
        $il_lp_status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (ilObjectLP::isSupportedObjectType($this->obj_data_cache->lookupType($obj_id))) {
            $il_lp_status = ilLPStatus::_lookupStatus($obj_id, $this->current_user_id, true);
        }
        print $il_lp_status;
        exit;
    }
}
