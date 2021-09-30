<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Guido Vollbach <gvollbach@databay.de>
 * Class ilMailTemplateGenericContext
 */
class ilMailTemplateGenericContext extends ilMailTemplateContext
{
    public function getId() : string
    {
        return 'mail_template_generic';
    }

    public function getTitle() : string
    {
        return $this->getLanguage()->txt('please_choose');
    }

    public function getDescription() : string
    {
        return $this->getLanguage()->txt('please_choose');
    }

    public function getSpecificPlaceholders() : array
    {
        return [];
    }

    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string {
        return '';
    }
}
