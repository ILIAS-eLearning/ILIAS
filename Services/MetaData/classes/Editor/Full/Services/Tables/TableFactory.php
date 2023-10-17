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

namespace ILIAS\MetaData\Editor\Full\Services\Tables;

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface as PresenterInterface;
use ILIAS\MetaData\Editor\Full\Services\DataFinder;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Full\Services\Actions\ButtonFactory;

class TableFactory
{
    protected UIFactory $ui_factory;
    protected Renderer $renderer;
    protected PresenterInterface $presenter;
    protected DataFinder $data_finder;
    protected ButtonFactory $button_factory;

    public function __construct(
        UIFactory $ui_factory,
        Renderer $renderer,
        PresenterInterface $presenter,
        DataFinder $data_finder,
        ButtonFactory $button_factory
    ) {
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->presenter = $presenter;
        $this->data_finder = $data_finder;
        $this->button_factory = $button_factory;
    }

    public function table(): TableBuilder
    {
        return new TableBuilder(
            $this->ui_factory,
            $this->renderer,
            $this->presenter,
            $this->data_finder,
            $this->button_factory
        );
    }
}
