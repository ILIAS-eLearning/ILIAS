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

namespace ILIAS\Blog\ReadingTime;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class BlogSettingsGUI
{
    /**
     * @var int
     */
    protected $blog_id;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var ReadingTimeManager
     */
    protected $manager;

    public function __construct(int $blog_id)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->manager = new ReadingTimeManager();
        $this->lng = $DIC->language();
        $this->blog_id = $blog_id;
    }

    public function addSettingToForm(\ilPropertyFormGUI $form): void
    {
        if ($this->manager->isGloballyActivated()) {
            $cb = new \ilCheckboxInputGUI($this->lng->txt("blog_est_reading_time"), "est_reading_time");
            $cb->setChecked($this->manager->isActivated($this->blog_id));
            $form->addItem($cb);
        }
    }

    public function addValueToArray(array $values): array
    {
        $values["est_reading_time"] = $this->manager->isActivated($this->blog_id);
        return $values;
    }

    public function saveSettingFromForm(\ilPropertyFormGUI $form): void
    {
        if ($this->manager->isGloballyActivated()) {
            $this->manager->activate(
                $this->blog_id,
                (bool) $form->getInput("est_reading_time")
            );
        }
    }
}
