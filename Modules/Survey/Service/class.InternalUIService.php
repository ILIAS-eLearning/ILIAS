<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

use ILIAS\Survey\Settings;
use ILIAS\Survey\Mode\ModeFactory;
use ILIAS\Survey\Mode\UIModifier;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Survey internal ui service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalUIService
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilObjectServiceInterface
     */
    protected $object_service;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var ModeFactory
     */
    protected $mode_factory;

    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $main_tpl;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    /**
     * Constructor
     */
    public function __construct(
        \ilObjectServiceInterface $object_service,
        ModeFactory $mode_factory,
        InternalDomainService $domain_service
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

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

    public function surveySettings(\ilObjSurvey $survey) : Settings\UIFactory
    {
        return new Settings\UIFactory(
            $this,
            $this->object_service,
            $survey,
            $this->domain_service
        );
    }

    public function evaluation(\ilObjSurvey $survey) : Evaluation\UIFactory
    {
        return new Evaluation\UIFactory(
            $this,
            $this->object_service,
            $survey,
            $this->domain_service
        );
    }

    public function infoScreen(
        \ilObjSurveyGUI $survey_gui,
        \ilToolbarGUI $toolbar
    ) : \ilInfoScreenGUI {
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

    public function modeUIModifier(int $mode) : UIModifier
    {
        $mode_provider = $this->mode_factory->getModeById($mode);
        return $mode_provider->getUIModifier();
    }

    public function ctrl() : \ilCtrl
    {
        return $this->ctrl;
    }

    public function lng() : \ilLanguage
    {
        return $this->lng;
    }

    public function mainTemplate() : \ilGlobalTemplateInterface
    {
        return $this->main_tpl;
    }

    public function http() : \ILIAS\DI\HTTPServices
    {
        return $this->http;
    }

    public function ui() : \ILIAS\DI\UIServices
    {
        return $this->ui;
    }
}
