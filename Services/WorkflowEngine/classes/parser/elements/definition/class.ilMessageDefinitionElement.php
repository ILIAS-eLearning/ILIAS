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

/**
 * Class ilMessageDefinitionElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilMessageDefinitionElement extends ilBaseElement
{
    public function getMessageDefinitionArray($message) : string// TODO PHP8-REVIEW Type hint or corresponding PHPDoc missing
    {
        $message_definition = [];

        $message_definition['name'] = $message['attributes']['name'];
        $message_definition['id'] = $message['attributes']['id'];
        $message_definition['content'] = ilBPMN2ParserUtils::extractILIASMessageDefinitionFromElement($message);

        $message_definition_array_string = " '" . $message_definition['id'] . "' =>  array(
		'name' => '" . $message_definition['name'] . "',
		'content' => '" . ($message_definition['content']['mailtext'] ?? '') . "')";

        return $message_definition_array_string;
    }
}
