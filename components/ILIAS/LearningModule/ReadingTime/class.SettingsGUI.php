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

namespace ILIAS\LearningModule\ReadingTime;

class SettingsGUI
{
    protected int $lm_id;
    protected \ilLanguage $lng;
    protected ReadingTimeManager $manager;

    public function __construct(int $lm_id)
    {
        global $DIC;

        $this->manager = new ReadingTimeManager();
        $this->lng = $DIC->language();
        $this->lm_id = $lm_id;
    }

    public function addSettingToForm(\ilPropertyFormGUI $form): void
    {
        if ($this->manager->isGloballyActivated()) {
            $cb = new \ilCheckboxInputGUI($this->lng->txt("lm_est_reading_time"), "est_reading_time");
            $cb->setChecked($this->manager->isActivated($this->lm_id));
            $form->addItem($cb);
        }
    }

    public function saveSettingFromForm(\ilPropertyFormGUI $form): void
    {
        if ($this->manager->isGloballyActivated()) {
            $this->manager->activate(
                $this->lm_id,
                (bool) $form->getInput("est_reading_time")
            );
        }
    }
}
