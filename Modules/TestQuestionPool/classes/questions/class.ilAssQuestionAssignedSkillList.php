<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilAssQuestionAssignedSkillList implements Iterator
{
    /**
     * @var array
     */
    protected $skills = array();
    
    /**
     * @param integer $skillBaseId
     * @param integer $skillTrefId
     */
    public function addSkill($skillBaseId, $skillTrefId)
    {
        $this->skills[] = "{$skillBaseId}:{$skillTrefId}";
    }
    
    /**
     * @return bool
     */
    public function skillsExist()
    {
        return (bool) count($this->skills);
    }
    
    /**
     * @return array
     */
    public function current()
    {
        return current($this->skills);
    }
    
    /**
     * @return array
     */
    public function next()
    {
        return next($this->skills);
    }
    
    /**
     * @return integer|bool
     */
    public function key()
    {
        $res = key($this->skills);
        return $res;
    }
    
    /**
     * @return bool
     */
    public function valid()
    {
        $res = key($this->skills);
        return $res !== null;
    }
    
    /**
     * @return array|bool
     */
    public function rewind()
    {
        return reset($this->skills);
    }
    
    /**
     * @return array
     */
    public function sleep()
    {
        return array('skills');
    }
    
    public function wakeup()
    {
        // TODO: Implement __wakeup() method.
    }
}
