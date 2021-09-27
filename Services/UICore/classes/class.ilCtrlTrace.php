<?php

/**
 * Class ilCtrlTrace
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlTrace implements ilCtrlTraceInterface
{
    /**
     * @var string separator used for CID traces.
     */
    private const CID_TRACE_SEPARATOR = ':';

    /**
     * @var string|null
     */
    private ?string $cid_trace;

    /**
     * @var ilCtrlStructure
     */
    private ilCtrlStructure $structure;

    /**
     * Constructor
     *
     * @param ilCtrlStructure $structure
     * @param string          $base_class
     */
    public function __construct(ilCtrlStructureInterface $structure, string $base_class)
    {
        $this->structure = $structure;
        $this->cid_trace = $this->findTraceForTargetClass($base_class);
    }

    /**
     * @return string
     */
    public function getCidTrace() : string
    {
        return $this->cid_trace;
    }

    /**
     * @inheritDoc
     */
    public function appendByClass(string $class_name) : void
    {
        $this->cid_trace = $this->findTraceForTargetClass($class_name);
    }

    /**
     * @inheritDoc
     */
    public function replaceByClassPath(array $classes) : void
    {
        if (!empty($classes)) {
            $this->cid_trace = '';
            $last_key = array_key_last($classes);
            foreach ($classes as $index => $class) {
                $this->cid_trace .= $this->structure->getClassCidByName($class);

                // only append the trace separator if it's not
                // the last iteration.
                if ($last_key !== $index) {
                    $this->cid_trace .= self::CID_TRACE_SEPARATOR;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getCidPieces(int $order = SORT_ASC) : Generator
    {
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);

        if (SORT_ASC === $order) {
            foreach ($pieces as $cid) {
                yield $cid;
            }
        } else {
            for ($i = count($pieces) - 1; 0 <= $i; $i--) {
                yield $pieces[$i];
            }
        }

        //  that wasn't quite readable ...
        //
        //  $pieces = explode(self::CID_TRACE_SEPARATOR, $this->trace);
        //
        //  $increment  = ($order === SORT_ASC) ? +1 : -1;
        //  $i_start    = ($order === SORT_ASC) ? 0 : count($pieces) - 1;
        //  $i_max      = ($order === SORT_ASC) ? count($pieces) - 1 : 0;
        //
        //  for ($i = $i_start; ($order === SORT_ASC) ? $i <= $i_max : $i >= $i_max; $i += $increment) {
        //    yield $pieces[$i];
        //  }
    }

    /**
     * @inheritDoc
     */
    public function getAllCidPieces(int $order = SORT_ASC) : array
    {
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);
        if (SORT_DESC === $order) {
            rsort($pieces);
        }

        return $pieces;
    }

    /**
     * Returns all individual paths for each cid position for the
     * given direction.
     *
     * For example, trace 'cid1:cid2:cid3' it would return:
     *      array(
     *          'cid1',
     *          'cid1:cid2',
     *          'cid1:cid2:cid3',
     *          ...
     *      );
     *
     * @param int $order (SORT_ASC|SORT_DESC)
     * @return array
     */
    public function getCidPaths(int $order = SORT_ASC) : array
    {
        $paths = [];
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);
        foreach ($pieces as $index => $cid) {
            if (0 === $index) {
                $paths[] = $cid;
            } else {
                $paths[] = $paths[$index - 1] . self::CID_TRACE_SEPARATOR . $cid;
            }
        }

        if (SORT_DESC === $order) {
            rsort($paths);
        }

        return $paths;
    }

    /**
     * @inheritDoc
     */
    public function isValid() : bool
    {
        if (null === $this->cid_trace) {
            return false;
        }

        $cid_pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);
        $cid_count  = count($cid_pieces);

        if (1 === $cid_count) {
            $base_class = $this->structure->getClassNameByCid($cid_pieces[0]);
            if (null === $base_class) {
                return false;
            }

            return $this->structure->isBaseClass($base_class);
        }

        foreach ($cid_pieces as $index => $cid) {
            if (($index + 1) < $cid_count) {
                $current_class = $this->structure->getClassNameByCid($cid);
                $next_class    = $this->structure->getClassNameByCid($cid_pieces[$index + 1]);

                // the trace is invalid if there are classes chained
                // together that are not related to each other.
                if (!$this->areClassesRelated($current_class, $next_class)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns a CID trace for a given target class (name).
     *
     * A CID trace is returned, if a relation between the previously called
     * classes and the provided one is found.
     *
     * @param string $target_class
     * @return string|null
     */
    private function findTraceForTargetClass(string $target_class) : ?string
    {
        $target_class = strtolower($target_class);
        $target_cid   = $this->structure->getClassCidByName($target_class);

        switch (true) {
            // abort if the target cid is unknown.
            case null === $target_cid:
                return null;

            // the target is used as trace, if either
            //      (a) there's no trace yet,
            //      (b) the target is already the current cid of trace, or
            //      (c) the target class is a known baseclass.
            case !isset($this->cid_trace):
            case $target_cid === $this->cid_trace:
            case $this->structure->isBaseClass($target_class):
                return $target_cid;

            // if the target cid is already the current cid
            // nothing has to be changed, so the current trace
            // is returned.
            case $target_cid === $this->getCurrentCid():
                return $this->cid_trace;
        }



        // check every class stored in trace for a relation
        // with the target class.
        foreach ($this->getCidPieces(SORT_DESC) as $index => $current_cid) {
            $current_class = $this->structure->getClassNameByCid($current_cid);
            if ($this->areClassesRelated($current_class, $target_class)) {
                $paths = $this->getCidPaths(SORT_DESC);

                // the target cid is appended to the path of the
                // current iteration.
                return $paths[$index] . self::CID_TRACE_SEPARATOR . $target_cid;
            }
        }

        // TODO: throw exception here because debugging is nightmare.

        return null;
    }

    /**
     * Returns whether two classes are related or not.
     *
     * The classes are related if either
     *
     *      (a) the current class is contained within the calling
     *          classes of the next class, or
     *      (b) the next class is contained within the called classes
     *          of the current class.
     *
     * @param string $current_class
     * @param string $next_class
     * @return bool
     */
    private function areClassesRelated(string $current_class, string $next_class) : bool
    {
        return
            in_array($next_class, $this->structure->getCalledClassesByName($current_class), true) ||
            in_array($current_class, $this->structure->getCallingClassesByName($next_class), true)
        ;
    }

    /**
     * Returns the last appended CID from the current trace.
     *
     * @return string
     */
    private function getCurrentCid() : string
    {
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);
        $key    = (count($pieces) - 1);

        return $pieces[$key];
    }
}