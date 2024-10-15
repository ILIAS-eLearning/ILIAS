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

namespace ILIAS\Survey\InfoScreen;

use ILIAS\Survey\Participants;
use ILIAS\Survey\Execution;
use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\Access;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Info screen wrapper for the survey. The screen currently acts
 * as a start screen, too.
 * @author Alexander Killing <killing@leifos.de>
 */
class InfoScreenGUI
{
    protected \ILIAS\Survey\InternalGUIService $gui;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilObjSurvey $survey;
    protected \ilObjUser $user;
    protected \ilToolbarGUI $toolbar;
    protected \ilObjSurveyGUI $survey_gui;
    protected Participants\StatusManager $status_manager;
    protected Access\AccessManager $access_manager;
    protected Execution\RunManager $run_manager;
    protected ServerRequestInterface $request;
    protected string $requested_code;
    protected \ILIAS\Survey\Mode\FeatureConfig $feature_config;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        \ilObjSurveyGUI $survey_gui,
        \ilToolbarGUI $toolbar,
        \ilObjUser $user,
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        ServerRequestInterface $request,
        InternalDomainService $domain_service
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->user = $user;
        $this->toolbar = $toolbar;
        $this->survey_gui = $survey_gui;
        $this->ui = $DIC->ui();
        /** @var \ilObjSurvey $survey */
        $survey = $survey_gui->getObject();
        $this->survey = $survey;
        $this->status_manager = $domain_service->participants()->status($this->survey, $user->getId());
        $this->access_manager = $domain_service->access($this->survey->getRefId(), $user->getId());
        $this->run_manager = $domain_service->execution()->run($this->survey, $user->getId());
        $this->feature_config = $domain_service->modeFeatureConfig($this->survey->getMode());

        $this->lng = $lng;
        $this->ctrl = $ctrl;

        $body = $request->getParsedBody();
        $this->requested_code = (string) ($body["anonymous_id"] ?? "");
        $this->gui = $DIC->survey()->internal()->gui();
    }

    public function getInfoScreenGUI(): \ilInfoScreenGUI
    {
        $survey = $this->survey;

        $info = new \ilInfoScreenGUI($this->survey_gui);
        $info->enablePrivateNotes();


        $info->addMetaDataSections($survey->getId(), 0, $survey->getType());

        return $info;
    }


}
