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
     * @var string
     */
    private string $trace;

    /**
     * @var ilCtrlStructure
     */
    private ilCtrlStructure $structure;

    /**
     * @param ilCtrlStructure $structure
     * @param string          $target_class
     */
    public function __construct(ilCtrlStructure $structure, string $target_class)
    {
        $this->structure = $structure;
        $this->trace = $this->findTraceForTargetClass($target_class);
    }

    /**
     * @inheritDoc
     */
    public function appendClass(string $class_name) : void
    {
        $this->trace = $this->findTraceForTargetClass($class_name);
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCid() : string
    {
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->trace);
        $key    = (count($pieces) - 1);

        return $pieces[$key];
    }

    /**
     * @inheritDoc
     */
    public function getCidPieces(int $order = SORT_ASC) : Generator
    {
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->trace);

        if (SORT_ASC === $order) {
            foreach ($pieces as $cid) {
                yield $cid;
            }
        } else {
            for ($i = count($pieces) - 1; 0 <= $i; $i--) {
                yield $pieces[$i];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function isValid() : bool
    {
        $cid_pieces = explode(self::CID_TRACE_SEPARATOR, $this->trace);
        $cid_count  = count($cid_pieces);
        foreach ($cid_pieces as $index => $cid) {
            if (($index + 1) <= $cid_count) {
                $current_class = $this->structure->getClassNameByCid($cid);
                $next_class    = $this->structure->getClassNameByCid($cid_pieces[$index + 1]);

                // the classes aren't related if either
                //      (a) the next class is not contained in the called classes
                //          of the current class, or
                //      (b) the current class is not contained in the called-by
                //          classes of the next class.
                if (!in_array($next_class, $this->structure->getCalledClassesByName($current_class), true) ||
                    !in_array($current_class, $this->structure->getCallingClassesByName($next_class), true)
                ) {
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
     * classes and the provided one are found.
     *
     * @param string $target_class
     * @return string
     */
    private function findTraceForTargetClass(string $target_class) : string
    {
        // retrieve information of target class.
        $target_class = strtolower($target_class);
        $target_cid   = $this->structure->getClassCidByName($target_class);

        // the target is used as trace, if either
        //      (a) there's no trace yet, or
        //      (b) the target is already the current cid of trace
        if (null === $this->trace || $target_cid === $this->trace) {
            return $target_cid;
        }

        foreach ($this->getCidPieces(SORT_DESC) as $index => $current_cid) {
            $current_class = $this->structure->getClassNameByCid($current_cid);

            // now we check if the target class is a direct child of the
            // previous class. This relation is true, if either
            //      (a) the previous class contains the target class within
            //          it's called classes, or
            //      (b) the previous class is contained in the target classes
            //          called-by classes.
            if (in_array($target_class, $this->structure->getCalledClassesByCid($current_cid), true) ||
                in_array($current_class, $this->structure->getCallingClassesByCid($target_cid), true)
            ) {
                // all cid paths in descending order are retrieved, so the
                // current iterations trace can be used to append the target
                // cid, which is then returned.
                $target_paths = $this->getCidPaths(SORT_DESC);

                return $target_paths[$index] . self::CID_TRACE_SEPARATOR . $target_cid;
            }
        }

        // if this point is reached, the target class must be
        // a baseclass itself, hence the trace is reset.

        // @TODO: check db table service_class and module_class for baseclass.
        //        if no entry exists, throw exception.

        return $target_cid;
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
    private function getCidPaths(int $order = SORT_ASC) : array
    {
        $paths = [];
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->trace);
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
}