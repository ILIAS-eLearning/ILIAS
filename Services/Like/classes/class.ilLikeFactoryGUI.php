<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 *
 *
 * @author Alex Killing <killing@leifos.de
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
        $data = new ilLikeData($a_obj_ids);
        $like_gui = new ilLikeGUI($data);
        return $like_gui;
    }
}
