<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Learning module presentation request
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMPresentationRequest
{
    protected int $requested_pg_id;
    protected int $requested_embed_mode;
    protected string $requested_obj_type;
    protected int $requested_focus_return;
    protected string $requested_frame;
    protected int $requested_obj_id;
    protected string $requested_transl;
    protected int $requested_ntf;
    protected string $requested_pg_type;
    protected string $requested_cmd;
    protected int $requested_mob_id;
    protected string $requested_from_page;
    protected string $requested_search_string;
    protected string $requested_back_pg;
    protected int $requested_focus_id;
    protected int $requested_ref_id;

    public function __construct(
        array $query_params
    ) {
        $this->requested_ref_id = (int) ($query_params["ref_id"] ?? 0);
        $this->requested_transl = (string) ($query_params["transl"] ?? "");     // handled by presentation status
        $this->requested_focus_id = (int) ($query_params["focus_id"] ?? 0);    // handled by presentation status
        $this->requested_obj_id = (int) ($query_params["obj_id"] ?? 0);        // handled by navigation status
        $this->requested_back_pg = (string) ($query_params["back_pg"] ?? "");
        $this->requested_frame = (string) ($query_params["frame"] ?? "");
        $this->requested_search_string = (string) ($query_params["srcstring"] ?? "");
        $this->requested_focus_return = (int) ($query_params["focus_return"] ?? 0);
        $this->requested_from_page = (string) ($query_params["from_page"] ?? "");
        $this->requested_obj_type = (string) ($query_params["obj_type"] ?? "");
        $this->requested_mob_id = (int) ($query_params["mob_id"] ?? 0);
        $this->requested_embed_mode = (int) ($query_params["embed_mode"] ?? 0);
        $this->requested_cmd = (string) ($query_params["cmd"] ?? "");

        $this->requested_pg_id = (int) ($query_params["pg_id"] ?? 0);
        $this->requested_pg_type = (string) ($query_params["pg_type"] ?? "");
        $this->requested_ntf = (int) ($query_params["ntf"] ?? 0);
    }

    public function getRequestedRefId() : int
    {
        return $this->requested_ref_id;
    }

    public function getRequestedObjId() : int
    {
        return $this->requested_obj_id;
    }

    public function getRequestedObjType() : string
    {
        return $this->requested_obj_type;
    }

    public function getRequestedTranslation() : string
    {
        return $this->requested_transl;
    }

    public function getRequestedFocusId() : int
    {
        return $this->requested_focus_id;
    }

    public function getRequestedFocusReturn() : int
    {
        return $this->requested_focus_return;
    }

    public function getRequestedBackPage() : string
    {
        return $this->requested_back_pg;
    }

    public function getRequestedSearchString() : string
    {
        return $this->requested_search_string;
    }

    public function getRequestedFrame() : string
    {
        return $this->requested_frame;
    }

    public function getRequestedFromPage() : string
    {
        return $this->requested_from_page;
    }

    public function getRequestedMobId() : int
    {
        return $this->requested_mob_id;
    }

    public function getRequestedEmbedMode() : int
    {
        return $this->requested_embed_mode;
    }

    public function getRequestedCmd() : string
    {
        return $this->requested_cmd;
    }

    public function getRequestedPgId() : int
    {
        return $this->requested_pg_id;
    }

    public function getRequestedPgType() : string
    {
        return $this->requested_pg_type;
    }

    public function getRequestedNotificationSwitch() : int
    {
        return $this->requested_ntf;
    }
}
