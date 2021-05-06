<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * DOM 2 util
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Services/Utilites
 */
class ilDOM2Util
{
    /**
     * Change name of a node
     *
     * @param object $node
     * @param string new name
     * @return
     */
    public static function changeName($node, $name, $keep_attributes = true)
    {
        $newnode = $node->ownerDocument->createElement($name);

        foreach ($node->childNodes as $child) {
            $child2 = $child->cloneNode(true);
            $newnode->appendChild($child2);
        }
        if ($keep_attributes) {
            foreach ($node->attributes as $attrName => $attrNode) {
                $newnode->setAttribute($attrName, $attrNode);
            }
        }
        $node->parentNode->replaceChild($newnode, $node);

        return $newnode;
    }
    
    /**
     * Add parent
     *
     * @param object $node
     * @return
     */
    public static function addParent($node, $name)
    {
        $newnode = $node->ownerDocument->createElement($name);
        //echo htmlentities($node->ownerDocument->saveXML($node->ownerDocument->documentElement));
        //echo "<br>".htmlentities($node->ownerDocument->saveXML($node)); exit;
        $par = $node->parentNode;
        //var_dump($node);
        //var_dump($par);
        if ($next_sib = $node->nextSibling) {
            $newnode = $par->insertBefore($newnode, $next_sib);
        } else {
            $newnode = $par->appendChild($newnode);
        }
        
        $node = $par->removeChild($node);
        $newnode->appendChild($node);
        
        //		foreach ($node->childNodes as $child)
        //		{
        //			$child2 = $child->cloneNode(true);
        //			$newnode->appendChild($child2);
        //		}

        return $newnode;
    }
    
    /**
     * Replace a node by its child
     *
     * @param object $node
     * @return
     */
    public static function replaceByChilds($node)
    {
        foreach ($node->childNodes as $child) {
            $child2 = $child->cloneNode(true);
            $node->parentNode->insertBefore($child2, $node);
        }
        $node->parentNode->removeChild($node);
    }
}
