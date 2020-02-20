<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Resource\Stakeholder;

/**
 * Class MainMenuResourceStakeholder
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainMenuResourceStakeholder extends AbstractResourceStakeholder
{

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return 'mme';
    }
}
