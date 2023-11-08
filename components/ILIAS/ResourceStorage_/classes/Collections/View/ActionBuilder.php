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

namespace ILIAS\components\ResourceStorage_\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\Data\URI;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Signal;
use ILIAS\components\ResourceStorage_\BinToHexSerializer;
use ILIAS\ResourceStorage\Services;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Implementation\Component\Table\Action\Action;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ActionBuilder
{
    use BinToHexSerializer;

    private const ACTION_UNZIP = 'unzip';
    private const ACTION_DOWNLOAD = 'download';
    private const ACTION_REMOVE = 'remove';
    public const ACTION_NAMESPACE = 'rcgui';
    /**
     * @var Modal[]
     */
    private array $modals = [];
    private URLBuilder $url_builder;
    private \ILIAS\UI\URLBuilderToken $url_token;

    public function __construct(
        private Request $request,
        private \ilCtrlInterface $ctrl,
        private Factory $ui_factory,
        private \ilLanguage $language,
        private Services $irss
    ) {
        $this->initURIBuilder();
    }

    private function initURIBuilder(): void
    {
        $uri_builder = new URLBuilder(
            new URI(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    \ilResourceCollectionGUI::class,
                    \ilResourceCollectionGUI::CMD_INDEX
                )
            )
        );
        $parameters = $uri_builder->acquireParameter(
            [self::ACTION_NAMESPACE],
            \ilResourceCollectionGUI::P_RESOURCE_ID
        );

        $this->url_builder = $parameters[0];
        $this->url_token = $parameters[1];
    }

    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }

    public function getUrlToken(): \ILIAS\UI\URLBuilderToken
    {
        return $this->url_token;
    }

    public function getModals(): array
    {
        return $this->modals;
    }

    /**
     * @return Action[]
     */
    public function getActions(): array
    {
        // we init the fixed actions here
        $actions[self::ACTION_DOWNLOAD] = $this->ui_factory->table()->action()->single(
            $this->language->txt(self::ACTION_DOWNLOAD),
            $this->url_builder->withURI($this->buildURI(\ilResourceCollectionGUI::CMD_DOWNLOAD)),
            $this->url_token
        );

        if ($this->request->canUserAdministrate()) {
            $actions[self::ACTION_REMOVE] = $this->ui_factory->table()->action()->standard(
                $this->language->txt(self::ACTION_REMOVE),
                $this->url_builder->withURI($this->buildURI(\ilResourceCollectionGUI::CMD_RENDER_CONFIRM_REMOVE)),
                $this->url_token
            )->withAsync(true);

            $actions[self::ACTION_UNZIP] = $this->ui_factory->table()->action()->single(
                $this->language->txt(self::ACTION_UNZIP),
                $this->url_builder->withURI($this->buildURI(\ilResourceCollectionGUI::CMD_UNZIP)),
                $this->url_token
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
            $target = $this->url_builder->withURI($target)
                                        ->withParameter(
                                            $this->url_token,
                                            $this->hash($rid->serialize())
                                        )->buildURI();

            if (!$a->isAsync()) {
                $items[] = $this->ui_factory->link()->standard(
                    $a->getLabel(),
                    (string) $target
                );
            } else {
                $this->modals[] = $modal = $this->ui_factory->modal()->interruptive(
                    $a->getLabel(),
                    $a->getLabel(),
                    '#'
                )->withAsyncRenderUrl($target->__toString());

                $items[] = $this->ui_factory->button()->shy(
                    $a->getLabel(),
                    $modal->getShowSignal()
                );
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
