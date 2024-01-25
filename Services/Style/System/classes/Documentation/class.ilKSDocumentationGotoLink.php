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

use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\BaseHandler;

/**
 * Generatates and reads Goto Links
 */
class ilKSDocumentationGotoLink extends BaseHandler implements Handler
{
    public function getNamespace(): string
    {
        return 'stys';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        return $response_factory->can(
            $this->generateRedirectURL(
                $context->ctrl(),
                $request->getReferenceId()?->toInt() ?? 0,
                $request->getAdditionalParameters()[0] ?? '',
                $request->getAdditionalParameters()[1] ?? '',
                $request->getAdditionalParameters()[2] ?? ''
            )
        );
    }

    public function generateGotoLink(string $node_id, string $skin_id, string $style_id): string
    {
        return implode('/', [$node_id, $skin_id, $style_id]);
    }

    public function generateRedirectURL(
        ilCtrl $ctrl,
        int $ref_id,
        string $node_id,
        string $skin_id,
        string $style_id,
    ): string {
        $ctrl->setParameterByClass(ilSystemStyleDocumentationGUI::class, 'skin_id', $skin_id);
        $ctrl->setParameterByClass(
            ilSystemStyleDocumentationGUI::class,
            'style_id',
            $style_id
        );
        $ctrl->setParameterByClass(ilSystemStyleDocumentationGUI::class, 'node_id', $node_id);
        $ctrl->setParameterByClass(ilSystemStyleDocumentationGUI::class, 'ref_id', $ref_id);

        $cmd_classes = [
            ilAdministrationGUI::class,
            ilObjStyleSettingsGUI::class,
            ilSystemStyleMainGUI::class,
            ilSystemStyleDocumentationGUI::class
        ];

        $ctrl->setTargetScript('ilias.php');

        return $ctrl->getLinkTargetByClass($cmd_classes, 'entries');
    }

    /**
     * @deprecated this is only present for backwards compatibility and the testcase.
     */
    public function redirectWithGotoLink(string $ref_id, array $params, ilCtrl $ctrl): void
    {
        $ref_id = (int) $ref_id;
        $node_id = $params[2] ?? '';
        $skin_id = $params[3] ?? '';
        $style_id = $params[4] ?? '';

        $ctrl->redirectToURL(
            $this->generateRedirectURL(
                $ctrl,
                $ref_id,
                $node_id,
                $skin_id,
                $style_id
            )
        );
    }
}
