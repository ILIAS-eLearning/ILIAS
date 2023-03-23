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

/**
 * Class ilMailTemplateSelectInputGUI
 */
class ilMailTemplateSelectInputGUI extends ilSelectInputGUI
{
    public function __construct(string $a_title, string $a_postvar, protected string $url, protected array $fields)
    {
        parent::__construct($a_title, $a_postvar);
    }

    public function render($a_mode = ''): string
    {
        $html = parent::render($a_mode);

        $tpl = new ilTemplate(
            'tpl.prop_template_select_container.html',
            true,
            true,
            'Services/Mail'
        );
        $tpl->setVariable('CONTENT', $html);
        $tpl->setVariable('FIELDS', json_encode($this->fields, JSON_THROW_ON_ERROR));
        $tpl->setVariable('URL', $this->url);
        $tpl->setVariable('ID', $this->getFieldId());

        return $tpl->get();
    }
}
