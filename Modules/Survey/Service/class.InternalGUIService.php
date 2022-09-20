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

namespace ILIAS\Survey;

use ILIAS\Survey\Settings;
use ILIAS\Survey\Mode\ModeFactory;
use ILIAS\Survey\Mode\UIModifier;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Repository\GlobalDICGUIServices;

/**
 * Survey internal ui service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected \ilLanguage $lng;
    protected ModeFactory $mode_factory;
    protected InternalDomainService $domain_service;
    protected ServerRequestInterface $request;
    protected \ilObjUser $user;

    public function __construct(
        \ilObjectServiceInterface $object_service,
        ModeFactory $mode_factory,
        InternalDomainService $domain_service
    ) {
        global $DIC;

        $this->initGUIServices($DIC);
        $this->object_service = $object_service;
        $this->mode_factory = $mode_factory;
        $this->domain_service = $domain_service;

        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->request = $DIC->http()->request();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
    }

    public function surveySettings(\ilObjSurvey $survey): Settings\UIFactory
    {
        return new Settings\UIFactory(
            $this,
            $this->object_service,
            $survey,
            $this->domain_service
        );
    }

    public function evaluation(\ilObjSurvey $survey): Evaluation\GUIService
    {
        return new Evaluation\GUIService(
            $this,
            $this->object_service,
            $survey,
            $this->domain_service
        );
    }

    public function editing(): Editing\GUIService
    {
        return new Editing\GUIService(
            $this,
            $this->domain_service
        );
    }

    public function execution(): Execution\GUIService
    {
        return new Execution\GUIService(
            $this,
            $this->domain_service
        );
    }

    public function infoScreen(
        \ilObjSurveyGUI $survey_gui,
        \ilToolbarGUI $toolbar
    ): \ilInfoScreenGUI {
        $info_screen = new InfoScreen\InfoScreenGUI(
            $survey_gui,
            $toolbar,
            $this->user,
            $this->lng,
            $this->ctrl,
            $this->request,
            $this->domain_service
        );

        return $info_screen->getInfoScreenGUI();
    }

    public function modeUIModifier(int $mode): UIModifier
    {
        $mode_provider = $this->mode_factory->getModeById($mode);
        return $mode_provider->getUIModifier();
    }

    public function lng(): \ilLanguage
    {
        return $this->lng;
    }

    public function print(): PrintView\GUIService
    {
        return new PrintView\GUIService(
            $this,
            $this->object_service,
            $this->domain_service
        );
    }
}
