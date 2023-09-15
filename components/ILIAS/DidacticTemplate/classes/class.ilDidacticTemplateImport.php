<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of ilDidacticTemplateImport
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateImport
{
    public const IMPORT_FILE = 1;

    private int $type = 0;
    private string $xmlfile = '';

    private ilLogger $logger;
    protected ilObjectDefinition $objDefinition;
    protected ilSetting $settings;

    public function __construct(int $a_type)
    {
        global $DIC;

        $this->logger = $DIC->logger()->otpl();
        $this->type = $a_type;
        $this->objDefinition = $DIC['objDefinition'];
        $this->settings = $DIC->settings();
    }

    public function setInputFile(string $a_file): void
    {
        $this->xmlfile = $a_file;
    }

    public function getInputFile(): string
    {
        return $this->xmlfile;
    }

    public function getInputType(): int
    {
        return $this->type;
    }

    /**
     * Do import
     */
    public function import(int $a_dtpl_id = 0): ilDidacticTemplateSetting
    {
        $root = null;
        $use_internal_errors = libxml_use_internal_errors(true);
        switch ($this->getInputType()) {
            case self::IMPORT_FILE:
                $root = simplexml_load_string(file_get_contents($this->getInputFile()));
                break;
        }
        libxml_use_internal_errors($use_internal_errors);
        if (!$root instanceof SimpleXMLElement) {
            throw new ilDidacticTemplateImportException(
                $this->parseXmlErrors()
            );
        }
        $settings = $this->parseSettings($root);
        $this->parseActions($settings, $root->didacticTemplate->actions);
        return $settings;
    }

    /**
     * Parse settings
     */
    protected function parseSettings(SimpleXMLElement $root): ilDidacticTemplateSetting
    {
        $icon = '';
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

            $icon = (string) $tpl->icon;

            $info = '';
            foreach ((array) $tpl->info->p as $paragraph) {
                if ($info !== '') {
                    $info .= "\n";
                }
                $info .= trim((string) $paragraph);
            }
            $setting->setInfo($info);

            if (isset($tpl->effectiveFrom) && (string) $tpl->effectiveFrom["nic_id"] == $this->settings->get('inst_id')) {
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

        if ($icon !== '' && $this->canUseIcons($setting)) {
            $setting->getIconHandler()->writeSvg($icon);
        }
        $trans = ilMultilingualism::getInstance($setting->getId(), "dtpl");
        if (isset($root->didacticTemplate->translations)) {
            $trans->fromXML($root->didacticTemplate->translations);
        }
        $trans->save();

        return $setting;
    }

    protected function canUseIcons(ilDidacticTemplateSetting $setting): bool
    {
        foreach ($setting->getAssignments() as $assignment) {
            if (!$this->objDefinition->isContainer($assignment)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse template action from xml
     */
    protected function parseActions(ilDidacticTemplateSetting $set, SimpleXMLElement $actions = null): void
    {
        if ($actions === null) {
            return;
        }
        ////////////////////////////////////////////////
        // Local role action
        ///////////////////////////////////////////////
        foreach ($actions->localRoleAction as $ele) {
            $act = new ilDidacticTemplateLocalRoleAction();
            $act->setTemplateId($set->getId());

            foreach ($ele->roleTemplate as $tpl) {
                // extract role
                foreach ($tpl->role as $roleDef) {
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
            $act = new ilDidacticTemplateBlockRoleAction();
            $act->setTemplateId($set->getId());

            // Role filter
            foreach ($ele->roleFilter as $rfi) {
                switch ((string) $rfi->attributes()->source) {
                    case 'title':
                        $act->setFilterType(\ilDidacticTemplateAction::FILTER_SOURCE_TITLE);
                        break;

                    case 'objId':
                        $act->setFilterType(\ilDidacticTemplateAction::FILTER_SOURCE_OBJ_ID);
                        break;

                    case 'parentRoles':
                        $act->setFilterType(\ilDidacticTemplateAction::FILTER_PARENT_ROLES);
                        break;
                }
                foreach ($rfi->includePattern as $pat) {
                    // @TODO other subtypes

                    $pattern = new ilDidacticTemplateIncludeFilterPattern();
                    $pattern->setPatternSubType(ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX);
                    $pattern->setPattern((string) $pat->attributes()->preg);
                    $act->addFilterPattern($pattern);
                }
                foreach ($rfi->excludePattern as $pat) {
                    // @TODO other subtypes

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
            $act = new ilDidacticTemplateLocalPolicyAction();
            $act->setTemplateId($set->getId());

            // Role filter
            foreach ($ele->roleFilter as $rfi) {
                $this->logger->dump($rfi->attributes(), \ilLogLevel::DEBUG);
                $this->logger->debug(
                    'Current filter source: ' . $rfi->attributes()->source
                );

                switch ((string) $rfi->attributes()->source) {
                    case 'title':
                        $act->setFilterType(\ilDidacticTemplateAction::FILTER_SOURCE_TITLE);
                        break;

                    case 'objId':
                        $act->setFilterType(\ilDidacticTemplateAction::FILTER_SOURCE_OBJ_ID);
                        break;

                    case 'parentRoles':
                        $act->setFilterType(\ilDidacticTemplateAction::FILTER_PARENT_ROLES);
                        break;

                    case 'localRoles':
                        $act->setFilterType(\ilDidacticTemplateAction::FILTER_LOCAL_ROLES);
                        break;
                }
                foreach ($rfi->includePattern as $pat) {
                    // @TODO other subtypes

                    $pattern = new ilDidacticTemplateIncludeFilterPattern();
                    $pattern->setPatternSubType(ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX);
                    $pattern->setPattern((string) $pat->attributes()->preg);
                    $act->addFilterPattern($pattern);
                }
                foreach ($rfi->excludePattern as $pat) {
                    // @TODO other subtypes

                    $pattern = new ilDidacticTemplateExcludeFilterPattern();
                    $pattern->setPatternSubType(ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX);
                    $pattern->setPattern((string) $pat->attributes()->preg);
                    $act->addFilterPattern($pattern);
                }
            }

            // role template assignment
            foreach ($ele->localPolicyTemplate as $lpo) {
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
     */
    protected function parseXmlErrors(): string
    {
        $errors = '';
        foreach (libxml_get_errors() as $err) {
            $errors .= $err->code . '<br/>';
        }
        return $errors;
    }
}
