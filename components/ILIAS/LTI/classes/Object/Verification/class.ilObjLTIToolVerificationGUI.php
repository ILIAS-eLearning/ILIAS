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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

/**
 * Screens of the object type ltiv: a verification is created in the personal workspace from the
 * certificate of an LTI object, delivers the stored certificate and is linked from portfolio pages.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIToolVerificationGUI extends ilObject2GUI implements DataRetrieval
{
    private const string OBJECT_PARAM = 'lti_id';

    public function getType(): string
    {
        return 'ltiv';
    }

    /**
     * The certificates of LTI objects the user has, to pick the one to keep.
     *
     * @throws ilCtrlException
     */
    public function create(): void
    {
        $this->lng->loadLanguageModule('ltiv');
        $this->tabs_gui->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'cancel'));

        $column = $this->ui_factory->table()->column();
        $table = $this->ui_factory->table()->data($this, $this->lng->txt('ltiv_create'), [
            'title' => $column->link($this->lng->txt('title')),
            'passed' => $column->text($this->lng->txt('passed')),
        ])
            ->withId('ltiv_create')
            ->withRequest($this->request);

        $this->tpl->setContent($this->ui_renderer->render([
            $this->ui_factory->messageBox()->info($this->lng->txt('ltiv_create_info')),
            $table,
        ]));
    }

    /**
     * Stores the certificate of the chosen LTI object as a verification in the workspace.
     *
     * @throws ilCtrlException
     */
    public function save(): void
    {
        $obj_id = $this->request_wrapper->has(self::OBJECT_PARAM)
            ? $this->request_wrapper->retrieve(self::OBJECT_PARAM, $this->refinery->kindlyTo()->int())
            : 0;
        if ($obj_id === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->create();
            return;
        }

        $verification = null;
        try {
            $certificate = new ilUserCertificateRepository()->fetchActiveCertificateForPresentation($this->user->getId(), $obj_id);
            $verification = $this->getFileService()->createFile($certificate);
        } catch (Exception $e) {
            global $DIC;

            $DIC->logger()->root()->warning('The verification of the LTI certificate could not be created: ' . $e->getMessage());
            $this->lng->loadLanguageModule('cert');
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_creating_certificate_pdf'));
            $this->create();
            return;
        }

        if ($verification === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_failed'));
            $this->create();
            return;
        }

        $parent_id = $this->node_id;
        $this->node_id = null;
        $this->putObjectInTree($verification, $parent_id);
        $this->afterSave($verification);
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        foreach (array_slice($this->getCertificates(), $range->getStart(), $range->getLength()) as $certificate) {
            $obj_id = $certificate->getUserCertificate()->getObjId();
            $this->ctrl->setParameter($this, self::OBJECT_PARAM, $obj_id);
            yield $row_builder->buildDataRow((string) $obj_id, [
                'title' => $this->ui_factory->link()->standard(
                    $certificate->getObjectTitle(),
                    $this->ctrl->getLinkTarget($this, 'save')
                ),
                'passed' => $this->lng->txt('yes'),
            ]);
        }
        $this->ctrl->setParameter($this, self::OBJECT_PARAM, null);
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getCertificates());
    }

    /**
     * @return array
     */
    private function getCertificates(): array
    {
        return new ilUserCertificateRepository()->fetchActiveCertificatesByTypeForPresentation($this->user->getId(), 'lti');
    }

    private function getFileService(): ilCertificateVerificationFileService
    {
        global $DIC;

        return new ilCertificateVerificationFileService(
            $this->lng,
            $DIC->database(),
            $DIC->logger()->root(),
            new ilCertificateVerificationClassMap()
        );
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
    private function getVerification(): ilObjLTIToolVerification
    {
        if (!$this->object instanceof ilObjLTIToolVerification) {
            throw new ilObjectException('no LTI verification given');
        }

        return $this->object;
    }
}
