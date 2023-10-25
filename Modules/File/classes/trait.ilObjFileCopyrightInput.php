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

use ILIAS\UI\Implementation\Component\Input\Field\Radio;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
trait ilObjFileCopyrightInput
{
    public function getCopyrightSelectionInput(string $lang_var_title): Radio
    {
        $copyright_input = $this->getUIFactory()->input()->field()->radio($this->getLanguage()->txt($lang_var_title));
        $copyright_options = ilMDCopyrightSelectionEntry::_getEntries();
        $default_entry_id = ilMDCopyrightSelectionEntry::getDefault();
        foreach ($copyright_options as $copyright_option) {
            $entry_id = $copyright_option->getEntryId();
            $copyright_input = $copyright_input->withOption(
                (string) $entry_id,
                $copyright_option->getTitle(),
                $copyright_option->getDescription()
            );
            if ($entry_id === $default_entry_id) {
                $copyright_input = $copyright_input->withValue($entry_id);
            }
        }

        return $copyright_input;
    }

    abstract protected function getUIFactory(): ILIAS\UI\Factory;

    abstract protected function getLanguage(): \ilLanguage;
}
