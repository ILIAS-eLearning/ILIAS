<?php

include_once "Services/WebDAV/classes/class.ilWebDAVMountInstructions.php";

/**
 * Class ilWebDAVMountInstructionsGUI
 *
 * This class represents the GUI for the WebDAV mount instructions page. It uses the ilWebDAVMountInstructions to
 * generate its content
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVMountInstructionsGUI
{
    
    /**
     *
     * @var $mount_instruction ilWebDAVMountInstructions
     */
    protected $protocol_prefixes;
    protected $base_url;
    protected $ref_id;
    protected $mount_instruction;
    
    public function __construct()
    {
        $this->mount_instruction = new ilWebDAVMountInstructions();
    }
    
    public function showMountInstructionPage()
    {
        global $DIC;
        
        $instruction_tpl = $this->getInstructionTemplate();
        $instruction_text = $this->mount_instruction->setInstructionPlaceholders($instruction_tpl);
        $this->displayInstructionPage($instruction_text);
        
        exit;
    }
    
    protected function displayInstructionPage($instruction_text)
    {
        global $DIC;
        
        header('Content-Type: text/html; charset=UTF-8');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN\"\n";
        echo "	\"http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
        echo "  <head>\n";
        echo "  <title>" . sprintf($DIC->language()->txt('webfolder_instructions_titletext'), $this->mount_instruction->getWebfolderTitle()) . "</title>\n";
        echo "  </head>\n";
        echo "  <body>\n";
        echo $instruction_text;
        echo "  </body>\n";
        echo "</html>\n";
    }
    
    protected function getInstructionTemplate()
    {
        global $DIC;

        $settings = new ilSetting('file_access');
        $instruction_tpl = '';
        
        if ($this->mount_instruction->instructionsTplFileExists()) {
            $instruction_tpl = $this->mount_instruction->getInstructionsFromTplFile();
        } elseif ($settings->get('custom_webfolder_instructions_enabled')) {
            $instruction_tpl = $this->mount_instruction->getCustomInstruction();
        }
        
        if (strlen($instruction_tpl) == 0) {
            $instruction_tpl = $this->mount_instruction->getDefaultInstruction();
        }
        
        return utf8_encode($instruction_tpl);
    }
}
