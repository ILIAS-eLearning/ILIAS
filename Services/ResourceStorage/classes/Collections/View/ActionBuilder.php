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

namespace ILIAS\Services\ResourceStorage\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\Data\URI;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Signal;
use ILIAS\Services\ResourceStorage\BinToHexSerializer;
use ILIAS\ResourceStorage\Services;
use ILIAS\UI\Component\Modal\Modal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ActionBuilder
{
    use BinToHexSerializer;

    private const ACTION_UNZIP = 'unzip';
    private const ACTION_DOWNLOAD = 'download';
    private const ACTION_REMOVE = 'remove';
    private ?\ILIAS\UI\Component\Modal\Interruptive $delete_modal = null;
    /**
     * @var Modal[]
     */
    private array $modals = [];

    public function __construct(
        private Request $request,
        private \ilCtrlInterface $ctrl,
        private Factory $ui_factory,
        private \ilLanguage $language,
        private Services $irss
    ) {
        $this->delete_modal = $this->ui_factory->modal()->interruptive(
            $this->language->txt('delete'),
            $this->language->txt('msg_delete_confirm'),
            $this->buildURI(\ilResourceCollectionGUI::CMD_REMOVE)->__toString()
        )->withAsyncRenderUrl($this->buildURI('renderConfirmRemove')->__toString());
    }

    public function buildAndAddDeleteModal(ResourceIdentification $rid): \ILIAS\UI\Component\Modal\Interruptive
    {
        $this->ctrl->setParameterByClass(
            \ilResourceCollectionGUI::class,
            \ilResourceCollectionGUI::P_RESOURCE_ID,
            $this->hash($rid->serialize())
        );
        $this->modals[] = $delete_modal = $this->ui_factory->modal()->interruptive(
            $this->language->txt('delete'),
            $this->language->txt('msg_delete_confirm'),
            '#'
        )->withAsyncRenderUrl($this->buildURI('renderConfirmRemove')->__toString());
        return $delete_modal;
    }

    public function getModals(): array
    {
        return $this->modals + [
                $this->delete_modal
            ];
    }

    /**
     * @deprecated this is only to allow data tables to have modals as well, we can rif of this later.
     */
    public function addModal(Modal $modal): void
    {
        $this->modals[] = $modal;
    }

    public function getActions(): array
    {
        // we init the fixed actions here
        $actions[self::ACTION_DOWNLOAD] = $this->ui_factory->table()->action()->single(
            $this->language->txt(self::ACTION_DOWNLOAD),
            \ilResourceCollectionGUI::P_RESOURCE_ID,
            $this->buildURI(\ilResourceCollectionGUI::CMD_DOWNLOAD)
        );

        if ($this->request->canUserAdministrate()) {
            $actions[self::ACTION_REMOVE] = $this->ui_factory->table()->action()->standard(
                $this->language->txt(self::ACTION_REMOVE),
                \ilResourceCollectionGUI::P_RESOURCE_ID,
                $this->delete_modal->getShowSignal()
            );

            $actions[self::ACTION_UNZIP] = $this->ui_factory->table()->action()->single(
                $this->language->txt(self::ACTION_UNZIP),
                \ilResourceCollectionGUI::P_RESOURCE_ID,
                $this->buildURI(\ilResourceCollectionGUI::CMD_UNZIP)
            );
        }

        return $actions;
    }

    public function buildDropDownForResource(
        ResourceIdentification $rid
    ): \ILIAS\UI\Implementation\Component\Dropdown\Standard {
        $items = [];
        foreach ($this->getActions() as $index => $a) {
            $revision = $this->irss->manage()->getCurrentRevision($rid);
            $mime_type = $revision->getInformation()->getMimeType();
            if ($index === self::ACTION_UNZIP
                && !in_array($mime_type, ['application/zip', 'application/x-zip-compressed'])
            ) {
                continue;
            }

            $target = $a->getTarget();

            if ($target instanceof URI) {
                $target = $target->withParameter(
                    \ilResourceCollectionGUI::P_RESOURCE_ID,
                    $this->hash($rid->serialize())
                );
                $items[] = $this->ui_factory->link()->standard(
                    $a->getLabel(),
                    (string) $target
                );
            } elseif ($target instanceof Signal) {
                $delete_modal = $this->buildAndAddDeleteModal($rid);
                $items[] = $this->ui_factory->button()->shy($a->getLabel(), $delete_modal->getShowSignal());
            }
        }

        return $this->ui_factory->dropdown()->standard(
            $items
        );
    }

    private function buildURI(
        string $command
    ): URI {
        return new URI(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                \ilResourceCollectionGUI::class,
                $command
            )
        );
    }
}
