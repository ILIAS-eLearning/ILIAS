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

namespace ILIAS\Tracking\Export\XML;

use ILIAS\Tracking\DB\LPCollection\Element\FactoryInterface as LPCollectionElementFactoryInterface;
use ILIAS\Tracking\DB\LPSettings\Element\FactoryInterface as LPSettingsElementFactoryInterface;
use ILIAS\Tracking\Status\FactoryInterface as LPStatusFactoryInterface;

class Factory implements FactoryInterface
{
    public function __construct(
        protected LPStatusFactoryInterface $lp_status_factory,
        protected LPSettingsElementFactoryInterface $lp_settings_element_factory,
        protected LPCollectionElementFactoryInterface $lp_collection_element_factory
    ) {
    }

    public function reader(): ReaderInterface
    {
        return new Reader(
            $this->lp_status_factory,
            $this->lp_settings_element_factory,
            $this->lp_collection_element_factory
        );
    }

    public function writer(): WriterInterface
    {
        return new Writer();
    }
}
