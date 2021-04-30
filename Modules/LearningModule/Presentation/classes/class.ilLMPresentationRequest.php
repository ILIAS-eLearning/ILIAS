<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning module presentation request
 *
 * @author killing@leifos.de
 */
class ilLMPresentationRequest
{
    /**
     * Constructor
     */
    public function __construct(array $query_params)
    {
        $this->requested_ref_id = (int) $query_params["ref_id"];
        $this->requested_transl = (string) $query_params["transl"];     // handled by presentation status
        $this->requested_focus_id = (int) $query_params["focus_id"];    // handled by presentation status
        $this->requested_obj_id = (int) $query_params["obj_id"];        // handled by navigation status
        $this->requested_back_pg = (string) $query_params["back_pg"];
        $this->requested_frame = (string) $query_params["frame"];
        $this->requested_search_string = (string) $query_params["srcstring"];
        $this->requested_focus_return = (int) $query_params["focus_return"];
        $this->requested_from_page = (string) $query_params["from_page"];
        $this->requested_obj_type = (string) $query_params["obj_type"];
        $this->requested_mob_id = (int) $query_params["mob_id"];
        $this->requested_cmd = (string) $query_params["cmd"];
    }

    /**
     * @return int
     */
    public function getRequestedRefId() : int
    {
        return $this->requested_ref_id;
    }

    /**
     * @return int
     */
    public function getRequestedObjId() : int
    {
        return $this->requested_obj_id;
    }

    /**
     * @return string
     */
    public function getRequestedObjType() : string
    {
        return $this->requested_obj_type;
    }

    /**
     * @return string
     */
    public function getRequestedTranslation() : string
    {
        return $this->requested_transl;
    }

    /**
     * @return int
     */
    public function getRequestedFocusId() : int
    {
        return $this->requested_focus_id;
    }

    /**
     * @return int
     */
    public function getRequestedFocusReturn() : int
    {
        return $this->requested_focus_return;
    }

    /**
     * @return string
     */
    public function getRequestedBackPage() : string
    {
        return $this->requested_back_pg;
    }

    /**
     * @return string
     */
    public function getRequestedSearchString() : string
    {
        return $this->requested_search_string;
    }

    /**
     * @return string
     */
    public function getRequestedFrame() : string
    {
        return $this->requested_frame;
    }

    /**
     * @return string
     */
    public function getRequestedFromPage() : string
    {
        return $this->requested_from_page;
    }

    /**
     * @return int
     */
    public function getRequestedMobId() : int
    {
        return $this->requested_mob_id;
    }

    /**
     * @return string
     */
    public function getRequestedCmd() : string
    {
        return $this->requested_cmd;
    }

}
