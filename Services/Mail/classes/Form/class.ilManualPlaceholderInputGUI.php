<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilManualPlaceholderInputGUI
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilManualPlaceholderInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected \ILIAS\HTTP\GlobalHttpState $httpState;
    /**
     * @var array<string, array{placeholder: string, title: string}>
     */
    protected array $placeholders = [];
    protected string $rerenderUrl = '';
    protected string $rerenderTriggerElementName = '';
    protected string $dependencyElementId;
    protected string $instructionText = '';
    protected string $adviseText = '';
    protected ilGlobalTemplateInterface $tpl;
    protected $value;

    public function __construct(string $dependencyElementId)
    {
        /** @var $DIC \ILIAS\DI\Container */
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->httpState = $DIC->http();

        $this->dependencyElementId = $dependencyElementId;

        parent::__construct();

        $this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');
    }

    public function getRerenderUrl() : ?string
    {
        return $this->rerenderUrl;
    }

    public function getRerenderTriggerElementName() : string
    {
        return $this->rerenderTriggerElementName;
    }

    public function supportsRerenderSignal(string $elementId, string $url) : void
    {
        $this->rerenderTriggerElementName = $elementId;
        $this->rerenderUrl = $url;
    }

    public function getAdviseText() : string
    {
        return $this->adviseText;
    }

    public function setAdviseText(string $adviseText) : void
    {
        $this->adviseText = $adviseText;
    }

    public function getInstructionText() : string
    {
        return $this->instructionText;
    }

    public function setInstructionText(string $instructionText) : void
    {
        $this->instructionText = $instructionText;
    }

    public function addPlaceholder(string $placeholder, string $title) : void
    {
        $this->placeholders[$placeholder]['placeholder'] = $placeholder;
        $this->placeholders[$placeholder]['title'] = $title;
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $html);
        $a_tpl->parseCurrentBlock();
    }

    public function render(bool $ajax = false) : string
    {
        $subtpl = new ilTemplate(
            'tpl.mail_manual_placeholders.html',
            true,
            true,
            'Services/Mail'
        );
        $subtpl->setVariable('TXT_USE_PLACEHOLDERS', $this->lng->txt('mail_nacc_use_placeholder'));
        if ($this->getAdviseText()) {
            $subtpl->setVariable('TXT_PLACEHOLDERS_ADVISE', $this->getAdviseText());
        }

        if (count($this->placeholders) > 0) {
            foreach ($this->placeholders as $placeholder) {
                $subtpl->setCurrentBlock('man_placeholder');
                $subtpl->setVariable('DEPENDENCY_ELM_ID', $this->dependencyElementId);
                $subtpl->setVariable('MANUAL_PLACEHOLDER', $placeholder['placeholder']);
                $subtpl->setVariable('TXT_MANUAL_PLACEHOLDER', $placeholder['title']);
                $subtpl->parseCurrentBlock();
            }
        }

        if ($this->getRerenderTriggerElementName() && $this->getRerenderUrl()) {
            $subtpl->setVariable('RERENDER_URL', $this->getRerenderUrl());
            $subtpl->setVariable('RERENDER_TRIGGER_ELM_NAME', $this->getRerenderTriggerElementName());
        }

        if ($ajax) {
            $this->httpState->saveResponse(
                $this->httpState
                    ->response()
                    ->withBody(\ILIAS\Filesystem\Stream\Streams::ofString($subtpl->get()))
            );
            $this->httpState->sendResponse();
            $this->httpState->close();
        }

        return $subtpl->get();
    }

    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? null);
    }

    public function setValue($a_value) : void
    {
        if (is_array($a_value) && $this->getMulti()) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }
    
    public function checkInput() : bool
    {
        return true;
    }
}
