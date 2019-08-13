<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\Guid;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ILIAS\FileUpload\Location;
use ilImageFileInputGUI;

/**
 * Class ImageUploader
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ImageUploader {
    /**
     * @return string
     */
    public static function UploadImage(string $image_key) : string {
        global $DIC;
        $upload = $DIC->upload();
        $target_file = "";
        
        $image = $_FILES[$image_key];
        
        if (array_key_exists($image_key . "_delete" , $_POST)) {
            return "";
        }
        if ($image['size'] === 0) {
            return $_POST[$image_key . QuestionFormGUI::IMG_PATH_SUFFIX];
        }
        
        if ($upload->hasUploads()) {
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            
            foreach ($upload->getResults() as $res)
            {
                /** @var \ILIAS\FileUpload\DTO\UploadResult $result */
                if ($res->getName() == $image["name"]) {
                    $result = $res;
                    break;
                }
            }
            
            if ($result && $result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
                $pathinfo    = pathinfo($image["name"]);
                $target_file = Guid::create() . "." . $pathinfo['extension'];
                $upload->moveOneFileTo(
                    $result,
                    'AssessmentQuestion/Uploads',
                    Location::WEB,
                    $target_file,
                    true);
            }
        }
        
        return $target_file;
    }
}