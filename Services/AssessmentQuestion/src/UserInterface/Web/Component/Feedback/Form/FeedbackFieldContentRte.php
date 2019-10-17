<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form;

use ilFormPropertyGUI;
use ilObjAdvancedEditing;
use Sabre\CardDAV\ValidateFilterTest;

/**
 * Class FeedbackFieldContentRte
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FeedbackFieldContentRte
{

    /**
     * FeedbackFieldContentRte constructor.
     *
     * @param string $content
     * @param int    $container_obj_id
     * @param string $container_obj_type
     * @param string $post_var
     */
    public function __construct(?string $content, int $container_obj_id, string $container_obj_type, string $label, string $post_var) {
        $this->content = $content;
        $this->container_obj_id = $container_obj_id;
        $this->container_obj_type = $container_obj_type;
        $this->label = $label;
        $this->post_var = $post_var;
    }


    public function getField(): ilFormPropertyGUI {
        global $DIC;

        $field_content = new \ilTextAreaInputGUI($this->label,  $this->post_var);
        /*$field_content->setRequired(true);
        $field_content->setRows(10);
        $field_content->setUseRte(true);
        $field_content->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
        $field_content->addPlugin("latex");
        $field_content->addButton("latex");
        $field_content->addButton("pastelatex");
        $field_content->setRTESupport( $this->container_obj_id,$this->container_obj_type, "assessment");*/
        $field_content->setValue($this->content);


        return $field_content;
    }

    public static function getValueFromPost(string $post_var): ?string  {
        return filter_input(INPUT_POST, $post_var, FILTER_DEFAULT);
    }
}