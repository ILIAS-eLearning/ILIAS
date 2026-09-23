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
 * Screens of the object type ltiv: they deliver the stored certificate and link to it from a portfolio
 * page. Creating a verification needs the certificate of an LTI object, which is not supported yet.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIConsumerVerificationGUI extends ilObject2GUI
{
    public function getType(): string
    {
        return 'ltiv';
    }

    /**
     * @throws ilObjectException
     */
    public function deliver(): void
    {
        $verification = $this->getVerification();
        $file = $verification->getFilePath();
        if ($file !== '') {
            ilFileDelivery::deliverFileLegacy($file, $verification->getTitle() . '.pdf');
        }
    }

    /**
     * @throws ilObjectException
     */
    public function render(bool $a_return = false, bool $a_url = false): string
    {
        global $DIC;

        if (!$a_return) {
            $this->deliver();
            return '';
        }

        $verification = $this->getVerification();
        $tree = new ilWorkspaceTree($DIC->user()->getId());
        $node_id = $tree->lookupNodeId($verification->getId());
        $caption = $DIC->language()->txt('wsp_type_ltiv') . ' "' . $verification->getTitle() . '"';

        if (!file_exists($verification->getFilePath())) {
            return '<div>' . $caption . ' (' . $DIC->language()->txt('url_not_found') . ')</div>';
        }
        if (!$a_url && !new ilWorkspaceAccessHandler($tree)->checkAccess('read', '', $node_id)) {
            return '<div>' . $caption . ' (' . $DIC->language()->txt('permission_denied') . ')</div>';
        }

        $url = $a_url ?: $this->getAccessHandler()->getGotoLink($node_id, $verification->getId());

        return '<div><a href="' . $url . '">' . $caption . '</a></div>';
    }

    /**
     * @throws ilObjectException
     */
    public function downloadFromPortfolioPage(ilPortfolioPage $a_page): void
    {
        global $DIC;

        if (!ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $DIC['ilErr']->raiseError($this->lng->txt('permission_denied'), $DIC['ilErr']->MESSAGE);
        }

        $this->deliver();
    }

    /**
     * @throws ilCtrlException
     */
    public static function _goto(string $a_target): void
    {
        global $DIC;

        $DIC->ctrl()->setParameterByClass(ilSharedResourceGUI::class, 'wsp_id', explode('_', $a_target)[0]);
        $DIC->ctrl()->redirectByClass(ilSharedResourceGUI::class);
    }

    /**
     * @throws ilObjectException
     */
    private function getVerification(): ilObjLTIConsumerVerification
    {
        if (!$this->object instanceof ilObjLTIConsumerVerification) {
            throw new ilObjectException('no LTI verification given');
        }

        return $this->object;
    }
}
