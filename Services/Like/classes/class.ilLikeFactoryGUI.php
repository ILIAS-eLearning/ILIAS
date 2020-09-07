<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <killing@leifos.de
 * @ingroup
 */
class ilLikeFactoryGUI
{
    /**
     *
     *
     * @param
     */
    public function __construct()
    {
    }

    /**
     * Get widget
     *
     * @param array $a_obj_ids
     * @return ilLikeGUI
     */
    public function widget(array $a_obj_ids)
    {
        include_once("./Services/Like/classes/class.ilLikeGUI.php");
        include_once("./Services/Like/classes/class.ilLikeData.php");
        $data = new ilLikeData($a_obj_ids);
        $like_gui = new ilLikeGUI($data);
        return $like_gui;
    }
}
