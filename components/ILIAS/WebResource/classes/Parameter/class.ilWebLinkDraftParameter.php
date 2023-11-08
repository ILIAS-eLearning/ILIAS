<?php

declare(strict_types=1);

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

/**
 * Draft class for creating and updating a parameter attached to Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkDraftParameter extends ilWebLinkBaseParameter
{
    /**
     * TODO: This can be removed when validate is.
     */
    public const LINKS_ERR_NO_NAME = 'links_no_name_given';
    public const LINKS_ERR_NO_VALUE = 'links_no_value_given';
    public const LINKS_ERR_NO_NAME_VALUE = 'links_no_name_no_value';


    protected ?ilWebLinkParameter $old_parameter = null;

    public function replaces(?ilWebLinkParameter $old_parameter): ilWebLinkDraftParameter
    {
        $this->old_parameter = $old_parameter;
        return $this;
    }

    public function getOldParameter(): ?ilWebLinkParameter
    {
        return $this->old_parameter;
    }

    /**
     * TODO: Modernizing the forms to input parameters will make this
     *   additional layer of input validation obsolete.
     */
    public function validate(): string
    {
        if (!strlen($this->getName()) && !$this->getValue()) {
            return self::LINKS_ERR_NO_NAME_VALUE;
        }
        if (!strlen($this->getName())) {
            return self::LINKS_ERR_NO_NAME;
        }
        if (!$this->getValue()) {
            return self::LINKS_ERR_NO_VALUE;
        }
        return '';
    }
}
