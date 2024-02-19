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
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class EditForm
{
    private Factory $ui_factory;
    private \ilLanguage $lng;
    private \ILIAS\ResourceStorage\Manager\Manager $manager;
    private \ILIAS\ResourceStorage\Revision\Revision $revision;
    private \ILIAS\Refinery\Factory $refinery;

    public function __construct(
        private Request $request,
        private ResourceIdentification $rid
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->lng = $DIC->language();
        $this->manager = $DIC->resourceStorage()->manage();
        $this->revision = $this->manager->getCurrentRevision($this->rid);
        $this->refinery = $DIC->refinery();
    }

    private function customTrafo(callable $trafo): Transformation
    {
        return $this->refinery->custom()->transformation($trafo);
    }

    public function getFields(): array
    {
        return [
            'title' => $this->ui_factory // currently we use the filename as title in collection guis
            ->input()
            ->field()
            ->text(
                $this->lng->txt('title')
            )
            ->withRequired(true)
            ->withValue(
                $this->revision->getInformation()->getTitle()
            )
            ->withAdditionalTransformation(
                $this->customTrafo(
                    function (?string $value): ?string {
                        // we store the title with the suffix. the suffix must be preserved
                        $new_title = empty($value) ? $this->revision->getInformation()->getTitle() : $value;
                        $new_title_without_suffix = preg_replace('/\.\w+$/', '', $new_title);
                        $new_title_with_suffix = $new_title_without_suffix . '.' . $this->revision->getInformation(
                        )->getSuffix();

                        $this->revision->getInformation()->setTitle(
                            $new_title_with_suffix
                        );

                        return $new_title_with_suffix;
                    }
                )
            ),
        ];
    }

    public function getAsSection(): Section
    {
        return $this->ui_factory->input()->field()->section(
            $this->getFields(),
            $this->lng->txt('edit')
        )->withAdditionalTransformation(
            $this->customTrafo(
                fn(array $values) => $this->manager->updateRevision($this->revision)
            )
        );
    }

    public function getAsForm(string $post_url): Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $post_url,
            $this->getFields()
        )->withAdditionalTransformation(
            $this->customTrafo(
                fn(array $values) => $this->manager->updateRevision($this->revision)
            )
        );
    }
}
