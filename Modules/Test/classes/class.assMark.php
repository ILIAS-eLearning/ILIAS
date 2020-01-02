<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * A class defining marks for assessment test objects
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 *
 * @version	$Id$
 * @ingroup ModulesTest
 */
class ASS_Mark
{
    /**
    * The short name of the mark
    *
    * The short name of the mark, e.g. F or 3 or 1,3
    *
    * @var string
    */
    public $short_name;

    /**
    * The official name of the mark
    *
    * The official name of the mark, e.g. failed, passed, befriedigend
    *
    * @var string
    */
    public $official_name;

    /**
    * The minimum percentage level reaching the mark
    *
    * The minimum percentage level reaching the mark. A double value between 0 and 100
    *
    * @var double
    */
    public $minimum_level = 0;

    /**
    * The passed status of the mark
    *
    * The passed status of the mark. 0 indicates that the mark is failed, 1 indicates that the mark is passed
    *
    * @var integer
    */
    public $passed;

    /**
    * ASS_Mark constructor
    *
    * The constructor takes possible arguments an creates an instance of the ASS_Mark object.
    *
    * @param string $short_name The short name of the mark
    * @param string $official_name The official name of the mark
    * @param double $minimum_level The minimum percentage level reaching the mark
    * @access public
    */
    public function __construct(
      $short_name = "",
      $official_name = "",
      $minimum_level = 0,
      $passed = 0
  ) {
        $this->setShortName($short_name);
        $this->setOfficialName($official_name);
        $this->setMinimumLevel($minimum_level);
        $this->setPassed($passed);
    }

    /**
    * Returns the short name of the mark
    *
    * Returns the short name of the mark
    *
    * @return string The short name of the mark
    * @access public
    * @see $short_name
    */
    public function getShortName()
    {
        return $this->short_name;
    }
  
    /**
    * Returns passed status of the mark
    *
    * Returns the passed status of the mark
    *
    * @return string The passed status of the mark
    * @access public
    * @see $passed
    */
    public function getPassed()
    {
        return $this->passed;
    }
  
    /**
    * Returns the official name of the mark
    *
    * Returns the official name of the mark
    *
    * @return string The official name of the mark
    * @access public
    * @see $official_name
    */
    public function getOfficialName()
    {
        return $this->official_name;
    }
  
    /**
    * Returns the minimum level reaching the mark
    *
    * Returns the minimum level reaching the mark
    *
    * @return double The minimum level reaching the mark
    * @access public
    * @see $minimum_level
    */
    public function getMinimumLevel()
    {
        return $this->minimum_level;
    }
  
    /**
    * Sets the short name of the mark
    *
    * Sets the short name of the mark
    *
    * @param string $short_name The short name of the mark
    * @access public
    * @see $short_name
    */
    public function setShortName($short_name = "")
    {
        $this->short_name = $short_name;
    }

    /**
    * Sets the passed status the mark
    *
    * Sets the passed status of the mark
    *
    * @param integer $passed The passed status of the mark
    * @access public
    * @see $passed
    */
    public function setPassed($passed = 0)
    {
        $this->passed = $passed;
    }

    /**
    * Sets the official name of the mark
    *
    * Sets the official name of the mark
    *
    * @param string $official_name The official name of the mark
    * @access public
    * @see $official_name
    */
    public function setOfficialName($official_name = "")
    {
        $this->official_name = $official_name;
    }

    /**
    * Sets the minimum level reaching the mark
    *
    * Sets the minimum level reaching the mark
    *
    * @param string $minimum_level The minimum level reaching the mark
    * @access public
    * @see $minimum_level
    */
    public function setMinimumLevel($minimum_level)
    {
        $minimum_level = (float) $minimum_level;

        if (($minimum_level >= 0) && ($minimum_level <= 100)) {
            $this->minimum_level = $minimum_level;
        } else {
            throw new Exception('Markstep: minimum level must be between 0 and 100');
        }
    }
}
