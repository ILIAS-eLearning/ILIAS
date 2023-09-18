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

use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Editor\Full\Services\DataFinder;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Elements\ElementInterface;

class Table extends \ilTable2GUI
{
    public function __construct()
    {
        parent::__construct(null);
    }

    protected function fillRow(array $a_set): void
    {
        foreach ($a_set as $key => $item) {
            if ($key === 'dropdown') {
                continue;
            }
            $this->tpl->setCurrentBlock('data_column');
            $this->tpl->setVariable('COLUMN_VAL', $item);
            $this->tpl->parse('data_column');
        }
        $this->tpl->setCurrentBlock('action_column');
        $this->tpl->setVariable('ACTION_HTML', $a_set['dropdown']);
        $this->tpl->parse('action_column');
    }
}
