<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateSelectInputGUI
 */
class ilMailTemplateSelectInputGUI extends ilSelectInputGUI
{
    protected array $fields = [];
    protected string $url;

    public function __construct(string $a_title, string $a_postvar, string $url, array $fields)
    {
        parent::__construct($a_title, $a_postvar);

        $this->url = $url;
        $this->fields = $fields;
    }

    public function render($a_mode = '') : string
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
