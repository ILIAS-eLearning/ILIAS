<?php

/**
 * Class ilCtrlSingleClassPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlSingleClassPath extends ilCtrlAbstractPath
{
    /**
     * @var ilCtrlContextInterface
     */
    private ilCtrlContextInterface $context;

    /**
     * ilCtrlSingleClassPath Constructor
     *
     * @param ilCtrlStructureInterface $structure
     * @param ilCtrlContextInterface   $context
     * @param string                   $target_class
     */
    public function __construct(ilCtrlStructureInterface $structure, ilCtrlContextInterface $context, string $target_class)
    {
        parent::__construct($structure);

        $this->context  = $context;

        try {
            $this->cid_path = $this->getCidPathByClass($target_class);
        } catch (ilCtrlException $exception) {
            $this->exception = $exception;
            $this->cid_path  = null;
        }
    }

    /**
     * Returns a cid path that reaches from the current context's
     * baseclass to the given class.
     *
     * If the given class cannot be reached from the context's
     * baseclass this instance must be given a class array instead.
     *
     * @param string $target_class
     * @return string
     * @throws ilCtrlException if the class has no relations or cannot
     *                         reach the baseclass of this context.
     */
    private function getCidPathByClass(string $target_class) : string
    {
        $target_cid = $this->structure->getClassCidByName($target_class);

        switch (true) {
            // abort if the target cid is unknown.
            case null === $target_cid:
                throw new ilCtrlException("Class '$target_class' was not found in the control structure, try `composer du` to read artifacts.");

            // the target is used as trace, if either
            //      (a) there's no trace yet,
            //      (b) the target is already the current cid of trace, or
            //      (c) the target class is a known baseclass.
            case null === $this->context->getPath()->getCidPath():
            case $target_cid === $this->context->getPath()->getCidPath():
            case $this->structure->isBaseClass($target_class):
                return $target_cid;

            // if the target cid is already the current cid
            // nothing has to be changed, so the current trace
            // is returned.
            case $target_cid === $this->context->getPath()->getCurrentCid():
                return $this->context->getPath()->getCurrentCid();
        }

        // check if the target is related to a class within
        // the current context's path.
        foreach ($this->context->getPath()->getCidArray() as $index => $cid) {
            $current_class = $this->structure->getClassNameByCid($cid);
            if ($this->isClassChildOf($target_class, $current_class)) {
                $cid_paths = $this->context->getPath()->getCidPaths();
                return $this->appendCid($target_cid, $cid_paths[$index]);
            }
        }

        throw new ilCtrlException("ilCtrl cannot find a path for '$target_class' that reaches '{$this->context->getBaseClass()}'");
    }
}