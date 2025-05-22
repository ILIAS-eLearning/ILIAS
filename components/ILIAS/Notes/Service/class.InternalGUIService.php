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

namespace ILIAS\Notes;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\Export\PrintProcessGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
    }

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function initJavascript(
        string $ajax_url = "",
        ?\ilGlobalTemplateInterface $main_tpl = null
    ): void {
        $tpl = $main_tpl ?? $this->mainTemplate();
        $lng = $this->domain_service->lng();
        $ctrl = $this->ctrl();
        // temporary patch to make this work...
        global $DIC;
        $ref_id = $DIC->repository()->internal()->gui()->standardRequest()->getRefId();
        $type = \ilObject::_lookupType($ref_id, true);
        if ($ajax_url === "") {
            $path = ["ilcommonactiondispatchergui", "ilnotegui"];
            $ajax_url = $this->ctrl()->getLinkTargetByClass(
                $path,
                "",
                "",
                true,
                false
            );
        }
        $lng->loadLanguageModule("notes");

        $lng->toJS(array("private_notes", "notes_public_comments", "cancel", "notes_messages"), $tpl);
        $tpl->addJavaScript("assets/js/ilNotes.js");
        //$tpl->addJavaScript("../components/ILIAS/Notes/resources/ilNotes.js");

        $modal_template = $this->getModalTemplate();

        $tpl->addOnLoadCode("ilNotes.setAjaxUrl('" . $ajax_url . "');");
        $tpl->addOnLoadCode('ilNotes.setModalTemplate("' . addslashes(json_encode($modal_template["template"])) . '");');
        $tpl->addOnLoadCode("ilNotes.setShowSignal('" . $modal_template["show"] . "');");
        $tpl->addOnLoadCode("ilNotes.setCloseSignal('" . $modal_template["close"] . "');");
    }

    public function getModalTemplate(): array
    {
        $ui = $this->ui();
        $modal = $ui->factory()->modal()->roundtrip('#notes_title#', $ui->factory()->legacy()->content(''));
        $modalt["show"] = $modal->getShowSignal()->getId();
        $modalt["close"] = $modal->getCloseSignal()->getId();
        $modalt["template"] = $ui->renderer()->renderAsync($modal);

        return $modalt;
    }

    public function print(): PrintProcessGUI
    {
        $provider = new PrintViewProvider();
        return new PrintProcessGUI(
            $provider,
            $this->http(),
            $this->ui(),
            $this->domain_service->lng()
        );
    }

    public function getCommentsGUI(
        $rep_obj_id,
        int $obj_id,
        string $obj_type,
        int $news_id = 0,
        bool $include_subs = false,
        bool $ajax = true,
        string $search_text = ""
    ): \ilCommentGUI {
        return new \ilCommentGUI(
            $rep_obj_id,
            $obj_id,
            $obj_type,
            $include_subs,
            $news_id,
            $ajax,
            $search_text
        );
    }

    public function getMessagesGUI(
        int $recipient,
        int $rep_obj_id,
        int $obj_id,
        string $obj_type
    ): \ilMessageGUI {
        return new \ilMessageGUI(
            $recipient,
            $rep_obj_id,
            $obj_id,
            $obj_type
        );
    }
}
