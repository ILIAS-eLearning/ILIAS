<?php declare(strict_types=1);

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
 
namespace ILIAS\Setup;

/**
 * A objective collection is a objective that is achieved once all subobjectives are achieved.
 */
class ObjectiveCollection implements Objective
{
    protected string $label;
    protected bool $is_notable;

    /**
     * @var	Objective[]
     */
    protected array $objectives;

    public function __construct(string $label, bool $is_notable, Objective ...$objectives)
    {
        $this->label = $label;
        $this->is_notable = $is_notable;
        $this->objectives = $objectives;
    }

    /**
     * @return Objective[]
     */
    public function getObjectives() : array
    {
        return $this->objectives;
    }

    /**
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            get_class($this) .
            implode(
                array_map(
                    fn ($g) : string => $g->getHash(),
                    $this->objectives
                )
            )
        );
    }

    /**
     * @inheritdocs
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdocs
     */
    public function isNotable() : bool
    {
        return $this->is_notable;
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Environment $environment) : array
    {
        return $this->objectives;
    }

    /**
     * @inheritdocs
     */
    public function achieve(Environment $environment) : Environment
    {
        return $environment;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable(Environment $environment) : bool
    {
        return false;
    }
}
