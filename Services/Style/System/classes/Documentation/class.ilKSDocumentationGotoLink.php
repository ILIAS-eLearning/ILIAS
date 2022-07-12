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

/**
 * Generatates and reads Goto Links
 */
class ilKSDocumentationGotoLink
{
    public function generateGotoLink(string $node_id, string $skin_id, string $style_id) : string
    {
        return '_' . $node_id . '_' . $skin_id . '_' . $style_id;
    }

    public function redirectWithGotoLink(string $ref_id, array $params, ilCtrl $ctrl) : void
    {
        $node_id = $params[2];
        $skin_id = $params[3];
        $style_id = $params[4];

        $ctrl->setParameterByClass('ilSystemStyleDocumentationGUI', 'skin_id', $skin_id);
        $ctrl->setParameterByClass(
            'ilSystemStyleDocumentationGUI',
            'style_id',
            $style_id
        );
        $ctrl->setParameterByClass('ilSystemStyleDocumentationGUI', 'node_id', $node_id);
        $ctrl->setParameterByClass('ilSystemStyleDocumentationGUI', 'ref_id', $ref_id);

        $cmd_classes = [
            'ilAdministrationGUI',
            'ilObjStyleSettingsGUI',
            'ilSystemStyleMainGUI',
            'ilSystemStyleDocumentationGUI'
        ];

        $ctrl->setTargetScript('ilias.php');
        $ctrl->redirectByClass($cmd_classes, 'entries');
    }
}
