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
 * Tries to enumerate all preconditions for the given objective, where the ones that
 * can be achieved (i.e. have no further preconditions on their own) will be
 * returned first. Will also attempt to only return every objective once. This thus
 * expects, that returned objectives will be achieved somehow.
 */
class ObjectiveIterator implements \Iterator
{
    protected Environment $environment;
    protected Objective $objective;

    /**
     * @var Objective[]
     */
    protected array $stack;

    protected ?Objective $current = null;

    /**
     * @var array<string, bool>
     */
    protected array $returned;

    /**
     * @var	array<string, bool>
     */
    protected array $failed;

    /**
     * @var array<string, string[]>
     */
    protected array $reverse_dependencies;


    public function __construct(Environment $environment, Objective $objective)
    {
        $this->environment = $environment;
        $this->objective = $objective;
        $this->rewind();
    }

    public function setEnvironment(Environment $environment) : void
    {
        $this->environment = $environment;
    }

    public function markAsFailed(Objective $objective) : void
    {
        if (!isset($this->returned[$objective->getHash()])) {
            throw new \LogicException(
                "You may only mark objectives as failed that have been returned by this iterator."
            );
        }

        $this->failed[$objective->getHash()] = true;
    }

    public function rewind() : void
    {
        $this->stack = [$this->objective];
        $this->current = null;
        $this->returned = [];
        $this->failed = [];
        $this->reverse_dependencies = [];
        $this->next();
    }

    public function current() : \ILIAS\Setup\Objective
    {
        if ($this->current === null) {
            throw new \LogicException(
                "Iterator is finished or wasn't initialized correctly internally."
            );
        }
        return $this->current;
    }

    public function key()
    {
        return $this->current()->getHash();
    }

    public function next() : void
    {
        if ($this->stack === []) {
            $this->current = null;
            return;
        }

        $cur = array_pop($this->stack);
        $hash = $cur->getHash();

        if (isset($this->returned[$hash]) || isset($this->failed[$hash])) {
            $this->next();
            return;
        }

        $preconditions = [];
        $failed_preconditions = [];
        foreach ($cur->getPreconditions($this->environment) as $p) {
            $h = $p->getHash();
            if (!isset($this->returned[$h]) || isset($this->failed[$h])) {
                $preconditions[] = $p;
            }

            if (isset($this->failed[$h])) {
                $failed_preconditions[] = $p;
            }
        }

        // We only have preconditions left that we know to have failed.
        if (
            $preconditions !== [] &&
            count($preconditions) === count($failed_preconditions)
        ) {
            $this->returned[$hash] = true;
            $this->markAsFailed($cur);
            if ($this->stack === []) {
                throw new UnachievableException(
                    "Objective had failed preconditions."
                );
            }
            $this->next();
            return;
        }

        // No preconditions open, we can proceed with the objective.
        if ($preconditions === []) {
            $this->returned[$hash] = true;
            $this->current = $cur;
            return;
        }

        $this->stack[] = $cur;
        $this->detectDependencyCycles($hash, $hash);
        foreach (array_reverse($preconditions) as $p) {
            $this->stack[] = $p;
            $this->setReverseDependency($p->getHash(), $hash);
        }
        $this->next();
    }

    public function valid() : bool
    {
        return $this->current !== null;
    }

    protected function detectDependencyCycles(string $cur, string $next) : void
    {
        if (!isset($this->reverse_dependencies[$next])) {
            return;
        }
        if (in_array($cur, $this->reverse_dependencies[$next])) {
            throw new UnachievableException(
                "The objectives contain a dependency cycle and won't all be achievable."
            );
        }
        foreach ($this->reverse_dependencies[$next] as $d) {
            $this->detectDependencyCycles($cur, $d);
        }
    }

    protected function setReverseDependency(string $other, string $cur) : void
    {
        if (!isset($this->reverse_dependencies[$other])) {
            $this->reverse_dependencies[$other] = [];
        }
        $this->reverse_dependencies[$other][] = $cur;
    }
}
