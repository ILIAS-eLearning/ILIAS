<?php

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
 * @author JKN Inc. <itstaff@cpkn.ca>
 * @version $Id$
 *
 * @ingroup Services
 */

abstract class ilLPGradebookGUI extends ilLPTableBaseGUI
{

    const GROUP_COLORS = [
        '#A93226', '#E74C3C', '#F39C12', '#F7DC6F', '#82E0AA',
        '#1E8449', '#AED6F1', '#2874A6', '#154360', '#A569BD', '#000000'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC["lng"];
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
    }


    protected function buildGradebookVersionsOptions()
    {
        $revision_txt = '';
        if (empty($this->versions)) {
            return '<option>' . 'No Current Existing Gradebooks' . '</option>';
        }
        foreach ($this->versions as $version) {
            if (!is_null($this->revision_id)) {
                if ($version->getRevisionId() == $this->revision_id) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }
            }
            $revision_txt .= '<option ' . $selected . ' value="' . $version->getRevisionId() . '"> Gradebook Revision: ' . $version->getRevisionId() . ' - ' . $this->user->_lookupFullname($version->getOwner()) . ' - ' . $version->getCreateDate() . '</option>';
        }
        return $revision_txt;
    }
}
