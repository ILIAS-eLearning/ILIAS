<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/gradebook/class.ilLPGradebookGUI.php");

/**
 * @author JKN Inc. <itstaff@cpkn.ca>
 * @version $Id$
 *
 * @ingroup Services
 */

class ilLPGradebookWeightGUI extends ilLPGradebookGUI
{
    protected $lng;
    protected $tpl;
    protected $versions;
    protected $gradebook_data;
    protected $revision_id;

    /**
     * @return mixed
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param mixed $versions
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getGradebookData()
    {
        return $this->gradebook_data;
    }
    public function setGradebookData($data)
    {
        $this->gradebook_data = $data;
    }

    /**
     * @return mixed
     */
    public function getRevisionId()
    {
        return $this->revision_id;
    }

    /**
     * @param mixed $revision_id
     */
    public function setRevisionId($revision_id)
    {
        $this->revision_id = $revision_id;
    }

    /**
     * Builds the Weighting Screen View.
     */
    function view()
    {

        $my_tpl = new ilTemplate('tpl.lp_gradebook_weight.html', true, true, "Services/Tracking");
        $this->tpl->addJavascript('https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js', true, 1);

        // Custom
        $this->tpl->addCss('./Services/Tracking/css/ilGradebook.css');
        $this->tpl->addJavascript('./Services/Tracking/js/ilGradebookWeight.js');
        $sortableIndex = 1;
        
        $gradeBookHTML = $this->makeList($this->gradebook_data, $sortableIndex);
        if(!is_null($this->revision_id)) {
           foreach ($this->versions as $version) {
                if ($version->getRevisionId() == $this->revision_id) {
                        $passing_grade = $version->getPassingGrade();
                }
            }
        } else {
            if($version = reset($this->versions) ) {
                //if there is a previous version.
                $passing_grade = $version->getPassingGrade();
            } 
        }

        $versions = $this->buildGradebookVersionsOptions();

        $my_tpl->setVariable("LOADED_GRADEBOOKS", $versions);
        $my_tpl->setVariable("GRADEBOOK", $gradeBookHTML);
        $my_tpl->setVariable("PASSING_GRADE_VALUE", $passing_grade ? $passing_grade : 0);

        $this->tpl->setContent($my_tpl->get());
    }


    /**
     * @param $a
     * @param $sortableIndex
     * @return string
     */
    private function makeListItems($a, $sortableIndex)
    {
        $out = '';
        foreach ($a as $item) {
            (is_numeric($item['color']) ? $color = $item['color'] : $color = 10);

            $out .= '<li ' . ($item['type'] == 'grp' ? 'data-grp-color="' . $color . '"' : "") . ' data-obj-id="' . $item['obj_id'] . '" class="' . (array_key_exists('children', $item) ? 'parent-style' : '') . '"><div class="row-color">';
            $out .= ($item['toggle'] == 1 ? '<input class="toggleButton" type="checkbox" checked data-onstyle="success" data-toggle="toggle">' : '<input class="toggleButton" data-onstyle="success" type="checkbox" data-toggle="toggle">');
            $out .= ($item['toggle'] == 1 ? '<input type="text" data-parent-id="' . $item['parent_id'] . '" class="weight weight-enabled" name="weight" value="' . $item['weight'] . '">' : '<input type="text" class="weight" data-parent-id="' . $item['parent_id'] . '" name="weight" disabled>');
            $out .= ($item['has_lp'] == 1 ? '<span class="obj-learning-progress glyphicon glyphicon-pencil" aria-hidden="true"></span>' : '<span class="obj-learning-progress glyphicon glyphicon-ok" aria-hidden="true"></span>');
            if ($item['type'] == 'grp') {
                $out .= '<span class="listObject" style="border-color:' . self::GROUP_COLORS[$color] . '" ><a target="_blank" href="' . $item['url'] . '">
                        <img alt="' . $item['type_Alt'] . '" title="' . $item['type_Alt'] . '" src="./templates/default/images/icon_' . $item['type'] . '.svg" class="ilListItemIcon"> 
                        ' . $item['title'] . '</a>';
                $out .= ($item['type'] == 'grp' ? '<span style="color:' . self::GROUP_COLORS[$color] . '" class="color-picker glyphicon glyphicon-certificate aria-hidden="true"></span>' : '');
            } else {
                $out .= '<span class="listObject"><a target="_blank" href="' . $item['url'] . '">
                        <img alt="' . $item['type_Alt'] . '" title="' . $item['type_Alt'] . '" src="./templates/default/images/icon_' . $item['type'] . '.svg" class="ilListItemIcon"> 
                        ' . $item['title'] . '</a>';
            }



            $out .= '</span></div>';
            if (array_key_exists('children', $item)) {
                $out .= $this->makeList($item['children'], $sortableIndex);
                $sortableIndex++;
            }
            $out .= '</li>';
        }
        return $out;
    }

    /**
     * @param $a
     * @param $sortableIndex
     * @return string
     */
    private function makeList($a, $sortableIndex)
    {
        $out = '<ul id="sortable_' . $sortableIndex . '">';
        $sortableIndex++;
        $out .= $this->makeListItems($a, $sortableIndex);
        $out .= '</ul>';
        return $out;
    }
}
