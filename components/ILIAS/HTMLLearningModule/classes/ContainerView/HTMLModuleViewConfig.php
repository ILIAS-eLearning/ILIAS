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
 */

declare(strict_types=1);

namespace ILIAS\HTMLLearningModule\ContainerView;

use ILIAS\components\ResourceStorage\Container\View\PathStatusInfo;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\components\ResourceStorage\Container\View\Mode;
use ILIAS\ResourceStorage\Resource\StorableContainerResource;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class HTMLModuleViewConfig implements PathStatusInfo
{
    private \ILIAS\components\ResourceStorage\Container\View\Configuration $view_config;

    public function __construct(
        ?StorableContainerResource $resource,
        string $title,
        bool $writeable,
        private string $start_file,
        private string $start_file_label
    ) {
        $this->view_config = (new \ILIAS\components\ResourceStorage\Container\View\Configuration(
            $resource,
            new \ilHTLMStakeholder(),
            $title,
            Mode::DATA_TABLE,
            250,
            $writeable,
            $writeable
        ))->withPathStatusInfo($this);
    }

    public function getConfiguration(): \ILIAS\components\ResourceStorage\Container\View\Configuration
    {
        return $this->view_config;
    }

    public function statusTextForPath(string $path): ?string
    {
        if ($path === $this->start_file) {
            return $this->start_file_label;
        }
        return null;
    }

}
