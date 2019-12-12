<?php
/**
 * Class ilQuestionHeaderBlockBuilder
 *
 * Date: 14.01.14
 * Time: 15:27
 * @author Thomas Joußen <tjoussen@databay.de>
 */

interface ilQuestionHeaderBlockBuilder
{

    /**
     * Get the HTML representation of the header block
     *
     * @return string
     */
    public function getHTML();
}
