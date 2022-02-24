<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * Tries to enumerate all preconditions for the given objective, where the ones that
 * can be achieved (i.e. have no further preconditions on their own) will be
 * returned first. Will also attempt to only return every objective once. This thus
 * expects, that returned objectives will be achieved somehow.
 */
class ObjectiveIterator implements \Iterator
{
    /**
     * @var	Environment
     */
    protected $environment;

    /**
     * @var Objective
     */
    protected $objective;

    /**
     * @var Objective[]
     */
    protected $stack;

    /**
     * @var Objective|null
     */
    protected $current;

    /**
     * @var array<string, bool>
     */
    protected $returned;

    /**
     * @var	array<string, bool>
     */
    protected $failed;

    /**
     * @var array<string, string[]>
     */
    protected $reverse_dependencies;


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

    public function markAsFailed(Objective $objective)
    {
        if (!isset($this->returned[$objective->getHash()])) {
            throw new \LogicException(
                "You may only mark objectives as failed that have been returned by this iterator."
            );
        }

        $this->failed[$objective->getHash()] = true;
    }

    public function rewind()
    {
        $this->stack = [$this->objective];
        $this->current = null;
        $this->returned = [];
        $this->failed = [];
        $this->reverse_dependencies = [];
        $this->next();
    }

    public function current()
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

    public function next()
    {
        if (count($this->stack) === 0) {
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
            count($preconditions) !== 0 &&
            count($preconditions) === count($failed_preconditions)
        ) {
            $this->returned[$hash] = true;
            $this->markAsFailed($cur);
            if (count($this->stack) === 0) {
                throw new UnachievableException(
                    "Objective had failed preconditions."
                );
            }
            $this->next();
            return;
        }

        // No preconditions open, we can proceed with the objective.
        if (count($preconditions) === 0) {
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

    public function valid()
    {
        return $this->current !== null;
    }

    protected function detectDependencyCycles(string $cur, string $next)
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

    protected function setReverseDependency(string $other, string $cur)
    {
        if (!isset($this->reverse_dependencies[$other])) {
            $this->reverse_dependencies[$other] = [];
        }
        $this->reverse_dependencies[$other][] = $cur;
    }
}
