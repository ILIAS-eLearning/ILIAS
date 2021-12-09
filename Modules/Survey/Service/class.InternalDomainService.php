<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

use ILIAS\Survey\Mode\FeatureConfig;
use ILIAS\Survey\Mode\ModeFactory;
use ILIAS\Survey\Code\CodeManager;

/**
 * Survey internal domain service
 * @author killing@leifos.de
 */
class InternalDomainService
{
    /**
     * @var ModeFactory
     */
    protected $mode_factory;

    /**
     * @var \ilTree
     */
    protected $repo_tree;

    /**
     * @var \ilAccessHandler
     */
    protected $access;

    /**
     * @var InternalRepoService
     */
    protected $repo_service;

    /**
     * @var InternalDataService
     */
    protected $data_service;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct(
        ModeFactory $mode_factory,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->repo_tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();

        $this->repo_service = $repo_service;
        $this->data_service = $data_service;

        $this->mode_factory = $mode_factory;
    }

    /**
     * Repository tree
     * @return \ilTree
     */
    public function repositoryTree()
    {
        return $this->repo_tree;
    }

    public function modeFeatureConfig(int $mode) : FeatureConfig
    {
        $mode_provider = $this->mode_factory->getModeById($mode);
        return $mode_provider->getFeatureConfig();
    }

    public function participants() : Participants\DomainService
    {
        return new Participants\DomainService(
            $this,
            $this->repo_service
        );
    }

    public function execution() : Execution\DomainService
    {
        return new Execution\DomainService(
            $this->repo_service,
            $this
        );
    }

    public function access(int $ref_id, int $user_id) : Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->access,
            $ref_id,
            $user_id
        );
    }

    public function code(\ilObjSurvey $survey, int $user_id) : CodeManager
    {
        return new CodeManager(
            $this->repo_service->code(),
            $this->data_service,
            $survey,
            $this,
            $user_id
        );
    }

    public function lng() : \ilLanguage
    {
        return $this->lng;
    }

    public function evaluation(\ilObjSurvey $survey, int $user_id) : Evaluation\EvaluationManager
    {
        return new Evaluation\EvaluationManager(
            $this,
            $this->repo_service,
            $survey,
            $user_id
        );
    }
}
