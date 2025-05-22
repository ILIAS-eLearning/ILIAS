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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Filesystem\Stream\Streams;

class ilManualPlaceholderInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected GlobalHttpState $http_state;
    /**
     * @var array<string, array{placeholder: string, title: string}>
     */
    protected array $placeholders = [];
    protected string $rerender_url = '';
    protected string $rerender_trigger_element_name = '';
    protected string $instruction_text = '';
    protected string $advise_text = '';
    protected ilGlobalTemplateInterface $tpl;
    /** @var mixed */
    protected $value;

    public function __construct(string $label, string $http_post_param_name, protected string $dependency_element_id)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->http_state = $DIC->http();

        parent::__construct($label, $http_post_param_name);

        $this->tpl->addJavaScript('assets/js/ilMailComposeFunctions.js');
    }

    public function getRerenderUrl(): ?string
    {
        return $this->rerender_url;
    }

    public function getRerenderTriggerElementName(): string
    {
        return $this->rerender_trigger_element_name;
    }

    public function supportsRerenderSignal(string $element_id, string $url): void
    {
        $this->rerender_trigger_element_name = $element_id;
        $this->rerender_url = $url;
    }

    public function getAdviseText(): string
    {
        return $this->advise_text;
    }

    public function setAdviseText(string $advise_text): void
    {
        $this->advise_text = $advise_text;
    }

    public function getInstructionText(): string
    {
        return $this->instruction_text;
    }

    public function setInstructionText(string $instruction_text): void
    {
        $this->instruction_text = $instruction_text;
    }

    public function addPlaceholder(string $placeholder, string $title): void
    {
        $this->placeholders[$placeholder]['placeholder'] = $placeholder;
        $this->placeholders[$placeholder]['title'] = $title;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $html);
        $a_tpl->parseCurrentBlock();
    }

    public function render(bool $ajax = false): string
    {
        $subtpl = new ilTemplate(
            'tpl.mail_manual_placeholders.html',
            true,
            true,
            'components/ILIAS/Mail'
        );
        $subtpl->setVariable('TXT_USE_PLACEHOLDERS', $this->lng->txt('mail_nacc_use_placeholder'));
        $subtpl->setVariable('DEPENDENCY_ELM_ID_OUTER', $this->dependency_element_id);
        if ($this->getAdviseText() !== '') {
            $subtpl->setVariable('TXT_PLACEHOLDERS_ADVISE', $this->getAdviseText());
        }

        foreach ($this->placeholders as $placeholder) {
            $subtpl->setCurrentBlock('man_placeholder');
            $subtpl->setVariable('DEPENDENCY_ELM_ID', $this->dependency_element_id);
            $subtpl->setVariable('PLACEHOLDER', '&lbrace;&lbrace;' . $placeholder['placeholder'] . '&rbrace;&rbrace;');
            $subtpl->setVariable('PLACEHOLDER_INTERACTION_INFO', sprintf(
                $this->lng->txt('mail_hint_add_placeholder_x'),
                '&lbrace;&lbrace;' . $placeholder['placeholder'] . '&rbrace;&rbrace;'
            ));
            $subtpl->setVariable('PLACEHOLDER_DESCRIPTION', $placeholder['title']);
            $subtpl->parseCurrentBlock();
        }

        if (!$ajax && $this->getRerenderTriggerElementName() && $this->getRerenderUrl()) {
            $subtpl->setVariable('RERENDER_URL', $this->getRerenderUrl());
            $subtpl->setVariable('RERENDER_DEPENDENCY_ELM_ID_OUTER', $this->dependency_element_id);
            $subtpl->setVariable('RERENDER_TRIGGER_ELM_NAME', $this->getRerenderTriggerElementName());
        }

        if ($ajax) {
            $this->http_state->saveResponse(
                $this->http_state
                    ->response()
                    ->withBody(Streams::ofString($subtpl->get()))
            );
            $this->http_state->sendResponse();
            $this->http_state->close();
        }

        return $subtpl->get();
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? null);
    }

    public function setValue($a_value): void
    {
        if (is_array($a_value) && $this->getMulti()) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }

    public function checkInput(): bool
    {
        return true;
    }
}
