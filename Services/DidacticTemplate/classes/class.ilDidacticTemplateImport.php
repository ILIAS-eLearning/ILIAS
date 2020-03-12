<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/exceptions/class.ilDidacticTemplateImportException.php';

/**
 * Description of ilDidacticTemplateImport
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateImport
{
    const IMPORT_FILE = 1;

    private $type = 0;
    private $xmlfile = '';


    /**
     * Constructor
     * @param <type> $a_type
     */
    public function __construct($a_type)
    {
        $this->type = $a_type;
    }

    /**
     * Set input file
     * @param string $a_file
     */
    public function setInputFile($a_file)
    {
        $this->xmlfile = $a_file;
    }

    /**
     * Get inputfile
     * @return <type>
     */
    public function getInputFile()
    {
        return $this->xmlfile;
    }

    /**
     * Get input type
     * @return string
     */
    public function getInputType()
    {
        return $this->type;
    }

    /**
     * Do import
     */
    public function import($a_dtpl_id = 0)
    {
        libxml_use_internal_errors(true);

        switch ($this->getInputType()) {
            case self::IMPORT_FILE:

                $root = simplexml_load_file($this->getInputFile());
                if ($root == false) {
                    throw new ilDidacticTemplateImportException(
                        $this->parseXmlErrors()
                    );
                }
                break;
        }

        $settings = $this->parseSettings($root);
        $this->parseActions($settings, $root->didacticTemplate->actions);

        return $settings;
    }

    /**
     * Parse settings
     * @param SimpleXMLElement $el
     * @return ilDidacticTemplateSetting
     */
    protected function parseSettings(SimpleXMLElement $root)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';
        $setting = new ilDidacticTemplateSetting();

        foreach ($root->didacticTemplate as $tpl) {
            switch ((string) $tpl->attributes()->type) {
                case 'creation':
                default:
                    $setting->setType(ilDidacticTemplateSetting::TYPE_CREATION);
                    break;
            }
            $setting->setTitle(trim((string) $tpl->title));
            $setting->setDescription(trim((string) $tpl->description));

            $info = '';
            foreach ((array) $tpl->info->p as $paragraph) {
                if (strlen($info)) {
                    $info .= "\n";
                }
                $info .= trim((string) $paragraph);
            }
            $setting->setInfo($info);

            if (isset($tpl->effectiveFrom) && (string) $tpl->effectiveFrom["nic_id"] == $ilSetting->get('inst_id')) {
                $node = array();
                foreach ($tpl->effectiveFrom->node as $element) {
                    $node[] = (int) $element;
                }
                
                $setting->setEffectiveFrom($node);
            }

            if (isset($tpl->exclusive)) {
                $setting->setExclusive(true);
            }

            foreach ($tpl->assignments->assignment as $element) {
                $setting->addAssignment(trim((string) $element));
            }
        }
        $setting->save();

        include_once("./Services/Multilingualism/classes/class.ilMultilingualism.php");
        $trans = ilMultilingualism::getInstance($setting->getId(), "dtpl");

        if (isset($root->didacticTemplate->translations)) {
            $trans->fromXML($root->didacticTemplate->translations);
        }
        $trans->save();
        
        return $setting;
    }

    /**
     * Parse template action from xml
     * @param ilDidacticTemplateSetting $set
     * @param SimpleXMLElement $root
     * @return void
     */
    protected function parseActions(ilDidacticTemplateSetting $set, SimpleXMLElement $actions = null)
    {
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateActionFactory.php';

        if ($actions === null) {
            return;
        }

        ////////////////////////////////////////////////
        // Local role action
        ///////////////////////////////////////////////
        foreach ($actions->localRoleAction as $ele) {
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateLocalRoleAction.php';
            $act = new ilDidacticTemplateLocalRoleAction();
            $act->setTemplateId($set->getId());

            foreach ($ele->roleTemplate as $tpl) {
                // extract role
                foreach ($tpl->role as $roleDef) {
                    include_once './Services/AccessControl/classes/class.ilRoleXmlImporter.php';
                    $rimporter = new ilRoleXmlImporter(ROLE_FOLDER_ID);
                    $role_id = $rimporter->importSimpleXml($roleDef);
                    $act->setRoleTemplateId($role_id);
                }
                $act->save();
            }
        }

        ////////////////////////////////////////////////
        // Block role action
        //////////////////////////////////////////////
        foreach ($actions->blockRoleAction as $ele) {
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateBlockRoleAction.php';
            $act = new ilDidacticTemplateBlockRoleAction();
            $act->setTemplateId($set->getId());

            // Role filter
            foreach ($ele->roleFilter as $rfi) {
                $act->setFilterType((string) $rfi->attributes()->source);
                foreach ($rfi->includePattern as $pat) {
                    // @TODO other subtypes
                    include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateIncludeFilterPattern.php';
                    $pattern = new ilDidacticTemplateIncludeFilterPattern();
                    $pattern->setPatternSubType(ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX);
                    $pattern->setPattern((string) $pat->attributes()->preg);
                    $act->addFilterPattern($pattern);
                }
                foreach ($rfi->excludePattern as $pat) {
                    // @TODO other subtypes
                    include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateExcludeFilterPattern.php';
                    $pattern = new ilDidacticTemplateExcludeFilterPattern();
                    $pattern->setPatternSubType(ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX);
                    $pattern->setPattern((string) $pat->attributes()->preg);
                    $act->addFilterPattern($pattern);
                }
            }

            $act->save();
        }



        ////////////////////////////////////////////
        // Local policy action
        /////////////////////////////////////////////
        foreach ($actions->localPolicyAction as $ele) {
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateLocalPolicyAction.php';
            $act = new ilDidacticTemplateLocalPolicyAction();
            $act->setTemplateId($set->getId());

            // Role filter
            foreach ($ele->roleFilter as $rfi) {
                $act->setFilterType((string) $rfi->attributes()->source);
                foreach ($rfi->includePattern as $pat) {
                    // @TODO other subtypes
                    include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateIncludeFilterPattern.php';
                    $pattern = new ilDidacticTemplateIncludeFilterPattern();
                    $pattern->setPatternSubType(ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX);
                    $pattern->setPattern((string) $pat->attributes()->preg);
                    $act->addFilterPattern($pattern);
                }
                foreach ($rfi->excludePattern as $pat) {
                    // @TODO other subtypes
                    include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateExcludeFilterPattern.php';
                    $pattern = new ilDidacticTemplateExcludeFilterPattern();
                    $pattern->setPatternSubType(ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX);
                    $pattern->setPattern((string) $pat->attributes()->preg);
                    $act->addFilterPattern($pattern);
                }
            }

            // role template assignment
            foreach ($ele->localPolicyTemplate as $lpo) {
                $act->setFilterType(ilDidacticTemplateLocalPolicyAction::FILTER_SOURCE_TITLE);
                switch ((string) $lpo->attributes()->type) {
                    case 'overwrite':
                        $act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_OVERWRITE);
                        break;

                    case 'union':
                        $act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_UNION);
                        break;

                    case 'intersect':
                        $act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_INTERSECT);
                        break;
                }

                // extract role
                foreach ($lpo->role as $roleDef) {
                    include_once './Services/AccessControl/classes/class.ilRoleXmlImporter.php';
                    $rimporter = new ilRoleXmlImporter(ROLE_FOLDER_ID);
                    $role_id = $rimporter->importSimpleXml($roleDef);
                    $act->setRoleTemplateId($role_id);
                }
            }

            // Save action including all filter patterns
            $act->save();
        }
    }

    /**
     * Parse xml errors from libxml_get_errors
     *
     * @return string
     */
    protected function parseXmlErrors()
    {
        $errors = '';
        foreach (libxml_get_errors() as $err) {
            $errors .= $err->code . '<br/>';
        }
        return $errors;
    }
}
