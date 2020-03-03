<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * Metadata block
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilObjectMetaDataBlockGUI: ilColumnGUI
 *
 * @ingroup ServicesObject
 */
class ilObjectMetaDataBlockGUI extends ilBlockGUI
{
    public static $block_type = "advmd";
    
    protected $record; // [ilAdvancedMDRecord]
    protected $values; // [ilAdvancedMDValues]
    protected $callback; // [string]
    
    protected static $records = array(); // [array]
    
    /**
    * Constructor
    */
    public function __construct(ilAdvancedMDRecord $a_record, $a_decorator_callback = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::__construct();
                        
        $this->record = $a_record;
        $this->callback = $a_decorator_callback;
        
        $this->setTitle($this->record->getTitle());
        $this->setBlockId("advmd_" . $this->record->getRecordId());
        $this->setEnableNumInfo(false);
        // $this->setAvailableDetailLevels(3);
        $this->allow_moving = false;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }
    
    /**
    * Get Screen Mode for current command.
    */
    public static function getScreenMode()
    {
        return IL_SCREEN_SIDE;
    }
    
    public function setValues(ilAdvancedMDValues $a_values)
    {
        $this->values = $a_values;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        $lng = $this->lng;
        
        $btpl = new ilTemplate("tpl.advmd_block.html", true, true, "Services/Object");
        
        // see ilAdvancedMDRecordGUI::parseInfoPage()
        
        $old_dt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
        include_once('Services/ADT/classes/class.ilADTFactory.php');
        
        // this correctly binds group and definitions
        $this->values->read();

        $defs = $this->values->getDefinitions();
        foreach ($this->values->getADTGroup()->getElements() as $element_id => $element) {
            $btpl->setCurrentBlock("item");
            $btpl->setVariable("CAPTION", $defs[$element_id]->getTitle());
            if ($element->isNull()) {
                $value = "-";
            } else {
                $value = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);

                if ($element instanceof ilADTLocation) {
                    $value->setSize("100%", "200px");
                }
                
                if (in_array($element->getType(), array("MultiEnum", "Enum", "Text"))) {
                    $value->setDecoratorCallBack($this->callback);
                }

                $value = $value->getHTML();
            }
            $btpl->setVariable("VALUE", $value);
            $btpl->parseCurrentBlock();
        }
                    
        $this->setDataSection($btpl->get());
        
        ilDatePresentation::setUseRelativeDates($old_dt);
        
        return;
    }
}
