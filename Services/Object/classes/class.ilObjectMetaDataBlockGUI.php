<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * Metadata block
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_IsCalledBy ilObjectMetaDataBlockGUI: ilColumnGUI
 */
class ilObjectMetaDataBlockGUI extends ilBlockGUI
{
    public static string $block_type = "advmd";
    protected static array $records = [];

    protected ilAdvancedMDRecord $record;
    protected ilAdvancedMDValues $values;
    protected ?string $callback;

    /**
    * Constructor
    */
    public function __construct(ilAdvancedMDRecord $record, string $decorator_callback = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();


        parent::__construct();
                        
        $this->record = $record;
        $this->callback = $decorator_callback;

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
    
    public function setValues(ilAdvancedMDValues $a_values) : void
    {
        $this->values = $a_values;
    }

    /**
    * execute command
    */
    public function executeCommand() : void
    {
        $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd("getHTML");
        $this->$cmd();
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

    protected bool $new_rendering = true;


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
                    $value->setSize(100, 200);
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
