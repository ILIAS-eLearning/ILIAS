<?php
/**
 * Class CompositeInterface
 *
 * Date: 25.03.13
 * Time: 15:36
 * @author Thomas Joußen <tjoussen@databay.de>
 */

interface ilAssLacCompositeInterface
{

    /**
     * Adds an CompositeInterface object to the node array which represents the condition tree structure
     *
     * @param ilAssLacCompositeInterface $node
     */
    public function addNode(ilAssLacCompositeInterface $node);

    /**
     * Describes a Composite tree Structure as human readable string
     *
     * @return string
     */
    public function describe();

    /**
     * Get a human readable description of the Composite element
     *
     * @return string
     */
    public function getDescription();
}
