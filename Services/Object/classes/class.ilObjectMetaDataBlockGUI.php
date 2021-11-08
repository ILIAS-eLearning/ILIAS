<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Metadata block
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_IsCalledBy ilObjectMetaDataBlockGUI: ilColumnGUI
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

        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
        $this->setTitle($translations->getTitleForLanguage($this->lng->getLangKey()));
        $this->setBlockId("advmd_" . $this->record->getRecordId());
        $this->setEnableNumInfo(false);
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
    public static function getScreenMode() : string
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
    public function fillDataSection() : void
    {
        $this->setDataSection($this->getLegacyContent());
    }

    //
    // New rendering
    //

    protected $new_rendering = true;


    /**
     * @inheritdoc
     */
    protected function getLegacyContent() : string
    {
        $btpl = new ilTemplate("tpl.advmd_block.html", true, true, "Services/Object");
        
        // see ilAdvancedMDRecordGUI::parseInfoPage()
        
        $old_dt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        
        // this correctly binds group and definitions
        $this->values->read();

        $defs = $this->values->getDefinitions();
        foreach ($this->values->getADTGroup()->getElements() as $element_id => $element) {
            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($defs[$element_id]->getRecordId());

            $btpl->setCurrentBlock("item");
            $btpl->setVariable("CAPTION", $field_translations->getTitleForLanguage($element_id, $this->lng->getLangKey()));
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
                    
        $html = $btpl->get();
        
        ilDatePresentation::setUseRelativeDates($old_dt);
        
        return $html;
    }
}
