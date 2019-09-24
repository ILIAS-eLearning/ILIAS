<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\Guid;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\DTO\ProcessingStatus;

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
    const BASE_PATH = 'asq/images/%d/%d/';
    
    /**
     * @var ImageUploader
     */
    private static $instance;
    
    /**
     * @var array
     */
    private $request_uploads;
    
    public static function getInstance() : ImageUploader {
        if (self::$instance === null) {
            self::$instance = new ImageUploader();
        }
        
        return self::$instance;
    }
    
    private function __construct() {
        $this->request_uploads = [];
    }
    
    /**
     * @return string
     */
    public function processImage(string $image_key) : string {
        global $DIC;
        $upload = $DIC->upload();
        $target_file = "";
        
        if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
            $upload->process();
            
            foreach ($upload->getResults() as $result)
            {
                if ($result && $result->getStatus()->getCode() === ProcessingStatus::OK) {
                    $pathinfo    = pathinfo($result->getName());
                    $target_file = Guid::create() . "." . $pathinfo['extension'];
                    $upload->moveOneFileTo(
                        $result,
                        self::processBasePath($target_file),
                        Location::WEB,
                        $target_file);
                    
                    foreach ($_FILES as $key => $value) {
                        if ($value['name'] === $result->getName()) {
                            $this->request_uploads[$key] = $this->getImagePath($target_file);
                        }
                    }
                }
            }
        }
        
        // delete selected
        //TODO search ilias source for hopefully existing _delete constant
        if (array_key_exists($image_key . '_delete', $_POST)) {
            return '';
        }
        
        // new file uploaded
        if (array_key_exists($image_key, $this->request_uploads)) {
            return $this->request_uploads[$image_key];
        }
        
        // old file exists
        if (!empty($_POST[$image_key])) {
            return $_POST[$image_key];
        }
       
        // no file
        return '';
    }
    
    private function getImagePath(string $filename) : string {
        return ILIAS_HTTP_PATH . '/' . ILIAS_WEB_DIR . '/' . CLIENT_ID .  '/' . self::processBasePath($filename) . $filename;
    }
    
    private function processBasePath(string $filename) : string {
        if (strlen($filename) < 2) {
            $first = '0';
            $second = '0';
        }
        else {
            $first = $filename[0];
            $second = $filename[1];
        }
        
        return sprintf(self::BASE_PATH, $first, $second);
    }
}