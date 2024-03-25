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

namespace ILIAS\components\ResourceStorage\Container\View\ActionBuilder;

use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
final class ExternalSingleAction extends SingleAction
{
    public function __construct(
        string $label,
        private string $target_class,
        private string $target_cmd,
        private string $path_parameter_name,
        private string $parameter_namespace,
        bool $async = false,
        bool $bulk = false,
        bool $supports_directories = false,
        array $supported_mime_types = ['*']
    ) {
        global $DIC;
        $action = new URI(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' . $DIC->ctrl()->getLinkTargetByClass(
                $this->target_class,
                $this->target_cmd
            )
        );

        parent::__construct(
            $label,
            $action,
            $async,
            $bulk,
            $supports_directories,
            $supported_mime_types
        );
    }

    public function getTargetClass(): string
    {
        return $this->target_class;
    }

    public function getTargetCmd(): string
    {
        return $this->target_cmd;
    }

    public function getPathParameterName(): string
    {
        return $this->path_parameter_name;
    }

    public function getParameterNamespace(): string
    {
        return $this->parameter_namespace;
    }

}
