<?php

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

declare(strict_types=1);

namespace ILIAS\Conditions\Export;

use ilCondition;
use ilConditionSet;
use SimpleXMLElement;

class XMLWriter
{
    protected SimpleXMLElement $xml_root;
    public function __construct()
    {
        $this->xml_root = new SimpleXMLElement('<Conditions></Conditions>');
    }

    public function write(
        Info $info
    ): void {
        $cond_xml = $this->xml_root->addChild('Condition');
        $cond_xml->addAttribute('object_id', (string) $info->getObjectId());
        $cond_xml->addAttribute('reference_id', (string) $info->getReferenceId());
        $cond_xml->addAttribute('object_type', $info->getObjectType());
        $cond_xml->addAttribute('hide_object_enabled', (string) ((int) $info->getConditionSet()->getHiddenStatus()));
        $cond_xml->addAttribute('all_obligatory_enabled', (string) ((int) $info->getConditionSet()->getAllObligatory()));
        $cond_xml->addAttribute('number_of_required_materials', (string) $info->getConditionSet()->getNumObligatory());
        $p_conds_xml = $cond_xml->addChild('Preconditions');
        foreach ($info->getConditionSet()->getConditions() as $precondition) {
            $p_cond_xml = $p_conds_xml->addChild('Precondition');
            $p_cond_xml->addAttribute('id', (string) $precondition->getId());
            $p_cond_xml->addAttribute('is_obligatory', (string) ((int) $precondition->getObligatory()));
            $p_cond_xml->addAttribute('operator', $precondition->getOperator());
            $p_cond_xml->addAttribute('value', $precondition->getValue() ?? '');
            $p_cond_trigger_xml = $p_cond_xml->addChild('Trigger');
            $p_cond_trigger_xml->addAttribute('ref_id', (string) $precondition->getTrigger()->getRefId());
            $p_cond_trigger_xml->addAttribute('obj_id', (string) $precondition->getTrigger()->getObjId());
            $p_cond_trigger_xml->addAttribute('type', $precondition->getTrigger()->getType());
        }
    }

    public function writeAll(
        InfoCollection $infos
    ): void {
        foreach ($infos as $info) {
            $this->write($info);
        }
    }

    public function __toString(): string
    {
        return trim(str_replace('<?xml version="1.0"?>', '', $this->xml_root->asXML()));
    }
}
