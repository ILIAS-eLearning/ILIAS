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

namespace ILIAS\Calendar\Recurrence\Input;

use ilCalendarRecurrence;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ilLanguage;
use ilCalendarUserSettings;

class FactoryImpl implements Factory
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected Refinery $refinery,
        protected ilLanguage $lng,
        protected ilCalendarUserSettings $user_settings
    ) {
    }

    public function build(ilCalendarRecurrence $recurrence): Builder
    {
        return new BuilderImpl(
            $recurrence,
            $this->ui_factory,
            $this->refinery,
            $this->lng,
            $this->user_settings
        );
    }
}
