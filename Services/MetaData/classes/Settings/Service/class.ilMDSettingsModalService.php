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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Component\Input\Field\Input;

class ilMDSettingsModalService
{
    protected UIFactory $ui_factory;

    public function __construct(UIFactory $ui_factory)
    {
        $this->ui_factory = $ui_factory;
    }

    public function initJS(ilGlobalTemplateInterface $tpl): void
    {
        $tpl->addJavaScript('./Services/Repository/js/repository.js');
    }

    public function placeholderModal(string $async_link): RoundTrip
    {
        return $this->ui_factory
            ->modal()
            ->roundtrip('', null)
            ->withAsyncRenderUrl($async_link);
    }

    public function modalWithForm(
        string $modal_title,
        string $post_url,
        Input ...$inputs
    ): RoundTrip {
        /*
         * This is only here to carry some js, since there's no way to get to
         * the form otherwise (when replacing the modal its id is not replaced,
         * so I can't bind this to the modal).
         */
        $hidden = $this->ui_factory
            ->input()
            ->field()
            ->hidden()
            ->withAdditionalOnLoadCode(function ($id) {
                return "document.getElementById('$id').closest('form').addEventListener('submit', function(event) {
                    event.preventDefault();
                    il.repository.ui.submitModalForm(event);
                });";
            });
        $inputs['ignore me'] = $hidden;

        return $this->ui_factory->modal()->roundtrip(
            $modal_title,
            null,
            $inputs,
            $post_url
        );
    }

    public function redirectHTML(string $redirect_link): string
    {
        return "<script>window.location.href = '" . $redirect_link . "';</script>";
    }
}
