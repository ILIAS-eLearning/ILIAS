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

namespace ILIAS\MetaData\Editor\Full;

use ILIAS\MetaData\Editor\Full\Services\Services as FullEditorServices;
use ILIAS\MetaData\Services\InternalServices;

class FullEditorInitiator
{
    protected InternalServices $services;

    public function __construct(InternalServices $services)
    {
        $this->services = $services;
    }

    public function init(): FullEditor
    {
        return new FullEditor(
            $this->services->editor()->dictionary(),
            $this->services->paths()->navigatorFactory(),
            $services = new FullEditorServices(
                $this->services->dic(),
                $this->services->paths(),
                $this->services->repository(),
                $this->services->vocabularies(),
                $this->services->manipulator(),
                $this->services->editor(),
                $this->services->dataHelper()
            ),
            new FormContent($services),
            new TableContent($services),
            $panel_content = new PanelContent(
                $services,
                $ui_factory = $this->services->dic()->ui()->factory(),
                $presenter = $this->services->editor()->presenter()
            ),
            new RootContent(
                $services,
                $ui_factory,
                $presenter,
                $panel_content
            ),
        );
    }
}
