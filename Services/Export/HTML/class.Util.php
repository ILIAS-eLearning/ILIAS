<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\Export\HTML;

/**
 * Util
 *
 * This class is an interim solution for the HTML export handling with
 * 6.0. Parts of it might move to the GlobalScreen service or other places.
 *
 * @author killing@leifos.de
 */
class Util
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Export resource files
     *
     * @param \ILIAS\GlobalScreen\Services $global_screen
     * @param string $target_dir
     */
    public function exportResourceFiles(\ILIAS\GlobalScreen\Services $global_screen, string $target_dir)
    {
        $css = $global_screen->layout()->meta()->getCss();
        foreach ($css->getItemsInOrderOfDelivery() as $item) {
            $this->exportResourceFile($target_dir, $item->getContent());
        }
        $js = $global_screen->layout()->meta()->getJs();
        foreach ($js->getItemsInOrderOfDelivery() as $item) {
            $this->exportResourceFile($target_dir, $item->getContent());
        }
    }

    /**
     * Export resource file
     *
     * @param string $target_dir
     * @param string $file
     */
    protected function exportResourceFile(string $target_dir, string $file)
    {
        if (is_int(strpos($file, "?"))) {
            $file = substr($file, 0, strpos($file, "?"));
        }
        if (is_file($file)) {
            $dir = dirname($file);
            \ilUtil::makeDirParents($target_dir."/".$dir);
            if (!is_file($target_dir."/".$file)) {
                copy ($file, $target_dir."/".$file);
            }
        }
    }


}