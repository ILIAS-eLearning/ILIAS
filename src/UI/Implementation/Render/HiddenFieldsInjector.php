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

namespace ILIAS\UI\Implementation\Render;

trait HiddenFieldsInjector
{
    protected function getHiddenFieldsTemplate(): Template
    {
        $path = 'src/UI/templates/default/tpl.hiddenfields.html';
        return $this->getTemplateFactory()->getTemplate($path, true, true);
    }
    protected function getHiddenFieldsHTML(array $parameters): string
    {
        $tpl = $this->getHiddenFieldsTemplate();
        foreach($parameters as $key => $value) {
            if(is_array($value)) {
                $key .= "[]";
                foreach($value as $entry) {
                    $tpl->setCurrentBlock('params');
                    $tpl->setVariable('PARAM_NAME', $key);
                    $tpl->setVariable('PARAM_VALUE', $entry);
                    $tpl->parseCurrentBlock();
                }
            } else {
                $tpl->setCurrentBlock('params');
                $tpl->setVariable('PARAM_NAME', $key);
                $tpl->setVariable('PARAM_VALUE', $value);
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }
}
