<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract learning history provider
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
abstract class ilAbstractLearningHistoryProvider
{
    /**
     * User id. This is the user the history will be retrieved for.
     *
     * @var int
     */
    protected $user_id;

    /**
     * @var ilLearningHistoryFactory
     */
    protected $factory;

    /**
     * @var iLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate|null
     */
    private $template;

    /**
     * Constructor
     * @param int $user_id
     * @param ilLearningHistoryFactory $factory
     * @param ilLanguage $lng
     * @param ilTemplate|null $template
     */
    public function __construct(
        $user_id,
        ilLearningHistoryFactory $factory,
        ilLanguage $lng,
        ilTemplate $template = null
    ) {
        $this->user_id = $user_id;
        $this->factory = $factory;
        $this->lng = $lng;

        if ($template === null) {
            $template = new ilTemplate(
                'tpl.emphasized_title.php',
                true,
                true,
                'Services/LearningHistory'
            );
        }
        $this->template = $template;
    }

    /**
     * Get user id
     *
     * @param
     * @return
     */
    protected function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Get factory
     *
     * @return ilLearningHistoryFactory
     */
    protected function getFactory()
    {
        return $this->factory;
    }

    /**
     * Get language object
     *
     * @return ilLanguage
     */
    protected function getLanguage()
    {
        return $this->lng;
    }

    /**
     * Get emphasized title
     *
     * @param string
     * @return string
     */
    protected function getEmphasizedTitle($title)
    {
        $clone = clone $this->template;
        $clone->setVariable("TITLE", $title);
        return $clone->get();
    }
}
