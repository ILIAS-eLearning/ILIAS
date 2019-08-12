<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;

class AnswerOptionChoiceAr extends AbstractProjectionAr {
    
    const STORAGE_NAME = "asq_answer_option_img";
    
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     */
    protected $image_uuid;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     */
    protected $text;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $points_selected;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $points_unselected;
   

    public function setData(
        int $container_obj_id,
        string $question_id,
        string $revision_id, 
        string $text ,
        string $image_uuid,
        int $points_selected,
        int $points_unselected)
    {
        $this->container_obj_id = $container_obj_id;
        $this->question_id = $question_id;
        $this->revision_id = $revision_id;
        $this->image_uuid = $image_uuid;
        $this->text = $text;
        $this->points_selected = $points_selected;
        $this->points_unselected = $points_unselected;
    }
    
    /**
     * @return string
     */
    public function getImageUuid()
    {
        return $this->image_uuid;
    }
    
    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * @return number
     */
    public function getPointsSelected()
    {
        return $this->points_selected;
    }
    
    /**
     * @return number
     */
    public function getPointsUnselected()
    {
        return $this->points_unselected;
    }
    
    /**
     * @return string
     */
    static function returnDbTableName() {
        return self::STORAGE_NAME;
    }
    
    
    public function satisfy(string $storage_type) : bool
    {
        return (AnswerOptionFormFieldDefinition::TYPE_IMAGE == $storage_type) ? true : false;
    }
    
}