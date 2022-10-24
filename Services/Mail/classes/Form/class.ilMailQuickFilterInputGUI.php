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

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\GlobalHttpState;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailQuickFilterInputGUI extends ilTextInputGUI
{
    protected ?Factory $refinery;
    protected GlobalHttpState $httpState;

    public function __construct($a_title, $a_postvar)
    {
        global $DIC;

        $this->refinery = $DIC->refinery();
        $this->httpState = $DIC->http();

        parent::__construct($a_title, $a_postvar);
    }

    public function checkInput(): bool
    {
        $ok = parent::checkInput();

        $query = '';
        if ($this->httpState->wrapper()->post()->has($this->getPostVar())) {
            $query = ilUtil::stripSlashes($this->httpState->wrapper()->post()->retrieve(
                $this->getPostVar(),
                $this->refinery->kindlyTo()->string()
            ));
        }

        if (!$ok) {
            return false;
        }

        try {
            ilMailLuceneQueryParser::validateQuery($query);
            return true;
        } catch (Exception $e) {
            $this->setAlert($this->lng->txt($e->getMessage()));
            return false;
        }
    }

    public function render(string $a_mode = ''): string
    {
        $tpl = new ilTemplate(
            'tpl.prop_mail_quick_filter_input.html',
            true,
            true,
            'Services/Mail'
        );
        if ($this->getValue() !== '') {
            $tpl->setCurrentBlock('prop_text_propval');
            $tpl->setVariable('PROPERTY_VALUE', ilLegacyFormElementsUtil::prepareFormOutput((string) $this->getValue()));
            $tpl->parseCurrentBlock();
        }
        if ($this->getInlineStyle() !== '') {
            $tpl->setCurrentBlock('stylecss');
            $tpl->setVariable('CSS_STYLE', ilLegacyFormElementsUtil::prepareFormOutput($this->getInlineStyle()));
            $tpl->parseCurrentBlock();
        }
        if ($this->getCssClass() !== '') {
            $tpl->setCurrentBlock('classcss');
            $tpl->setVariable('CLASS_CSS', ilLegacyFormElementsUtil::prepareFormOutput($this->getCssClass()));
            $tpl->parseCurrentBlock();
        }
        if ($this->getSubmitFormOnEnter()) {
            $tpl->touchBlock('submit_form_on_enter');
        }

        switch ($this->getInputType()) {
            case 'password':
                $tpl->setVariable('PROP_INPUT_TYPE', 'password');
                break;
            case 'hidden':
                $tpl->setVariable('PROP_INPUT_TYPE', 'hidden');
                break;
            case 'text':
            default:
                $tpl->setVariable('PROP_INPUT_TYPE', 'text');
        }
        $tpl->setVariable('ID', $this->getFieldId());
        $tpl->setVariable('ARIA_LABEL', $this->getTitle());
        $tpl->setVariable('SIZE', $this->getSize());
        if ($this->getMaxLength() !== null) {
            $tpl->setVariable('MAXLENGTH', $this->getMaxLength());
        }
        if ($this->getSuffix() !== '') {
            $tpl->setVariable('INPUT_SUFFIX', $this->getSuffix());
        }

        $postvar = $this->getPostVar();
        if ($this->getMulti() && substr($postvar, -2) !== '[]') {
            $postvar .= '[]';
        }

        if ($this->getDisabled()) {
            if ($this->getMulti()) {
                $value = $this->getMultiValues();
                $hidden = '';
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $hidden .= $this->getHiddenTag($postvar, $item);
                    }
                }
            } else {
                $hidden = $this->getHiddenTag($postvar, $this->getValue());
            }
            if ($hidden) {
                $tpl->setVariable('DISABLED', ' disabled=\'disabled\'');
                $tpl->setVariable('HIDDEN_INPUT', $hidden);
            }
        } else {
            $tpl->setVariable('POST_VAR', $postvar);
        }

        if ($a_mode === 'toolbar') {
            // block-inline hack, see: http://blog.mozilla.com/webdev/2009/02/20/cross-browser-inline-block/
            // -moz-inline-stack for FF2
            // zoom 1; *display:inline for IE6 & 7
            $tpl->setVariable(
                'STYLE_PAR',
                'display: -moz-inline-stack; display:inline-block; zoom: 1; *display:inline;'
            );
        } else {
            $tpl->setVariable('STYLE_PAR');
        }

        if ($this->sub_items !== []) {
            $tpl->setVariable('FIELD_ID', $this->getFieldId());
            $tpl->setVariable('TXT_PLACEHOLDER', $this->lng->txt('mail_filter_field_placeholder'));
            $tpl->setVariable('TXT_FILTER_MESSAGES_BY', $this->lng->txt('mail_filter_txt'));

            $subitem_html = '';
            foreach ($this->sub_items as $item) {
                $subitem_html .= $item->render('toolbar');
            }

            $tpl->setVariable('FIELD_SUBITEMS', $subitem_html);
        }

        return $tpl->get();
    }
}
