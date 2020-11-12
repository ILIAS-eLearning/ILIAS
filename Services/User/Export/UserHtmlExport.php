<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\User\Export;

/**
 * Wiki HTML exporter class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class UserHtmlExport
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Export user images
     */
    public function exportUserImages($dir, $user_ids)
    {
        $base_dir = $dir."/data/".CLIENT_ID."/usr_images";
        \ilUtil::makeDirParents($base_dir);
        foreach ($user_ids as $id) {
            $source = "./data/".CLIENT_ID."/usr_images/usr_$id.jpg";
            if (is_file($source)) {
                copy($source, $base_dir."/usr_$id.jpg");
            }
        }
    }
}
