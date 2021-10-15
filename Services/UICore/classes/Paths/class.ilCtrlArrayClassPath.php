<?php

/**
 * Class ilCtrlArrayClassPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayClassPath extends ilCtrlAbstractPath
{
    /**
     * @var ilCtrlContextInterface
     */
    private ilCtrlContextInterface $context;

    /**
     * ilCtrlArrayClassPath Constructor
     *
     * @param ilCtrlStructureInterface $structure
     * @param ilCtrlContextInterface   $context
     * @param string[]                 $target_classes
     */
    public function __construct(ilCtrlStructureInterface $structure, ilCtrlContextInterface $context, array $target_classes)
    {
        parent::__construct($structure);

        $this->context = $context;

        try {
            $this->cid_path = $this->getCidPathByArray($target_classes);
        } catch (ilCtrlException $exception) {
            $this->exception = $exception;
            $this->cid_path  = null;
        }
    }

    /**
     * Generates a cid path from the given class array.
     *
     * If the given class array does not contain a valid
     * path an according exception will be thrown.
     *
     * @param string[] $target_classes
     * @return string
     * @throws ilCtrlException if classes within the classes array
     *                         are not related.
     */
    private function getCidPathByArray(array $target_classes) : string
    {
        if (empty($target_classes)) {
            throw new ilCtrlException(__METHOD__ . " must be provided with a list of classes.");
        }

        // loop through each provided class in descending order
        // and check if they are all related to one another and
        // convert them to a cid path.
        $cid_path = $previous_class = null;
        foreach ($target_classes as $current_class) {
            $current_cid = $this->structure->getClassCidByName($current_class);

            // abort if the current class cannot be found.
            if (null === $current_cid) {
                throw new ilCtrlException("Class '$current_class' was not found in the control structure, try `composer du` to read artifacts.");
            }

            // abort if the current and previous classes are
            // not related.
            if (null !== $previous_class && !$this->isClassParentOf($previous_class, $current_class)) {
                throw new ilCtrlException("Class '$current_class' is not a child of '$previous_class'.");
            }

            $cid_path = $this->appendCid($current_cid, $cid_path);
            $previous_class = $current_class;
        }

        // if the first provided class is a baseclass the
        // created cid path can be returned.
        $first_array_class = $target_classes[array_key_first($target_classes)];
        if ($this->structure->isBaseClass($first_array_class)) {
            return $cid_path;
        }

        // if the first provided class is not a baseclass,
        // the current context is checked for a relation
        // that leads to the context's baseclass.
        if (!$this->structure->isBaseClass($first_array_class) && null !== $this->context->getPath()->getCidPath()) {
            foreach ($this->context->getPath()->getCidArray() as $index => $cid) {
                $current_class = $this->structure->getClassNameByCid($cid);

                // if one of the existing classes from the context's
                // current path is related to the first provided class
                // the generated path from above can be appended to
                // the context's existing one.
                if (null !== $current_class && $this->isClassParentOf($current_class, $first_array_class)) {
                    $paths = $this->context->getPath()->getCidPaths();
                    return $this->appendCid($cid_path, $paths[$index]);
                }
            }
        }

        throw new ilCtrlException("Class '$first_array_class' is not a baseclass and the current context doesn't have one either.");
    }
}