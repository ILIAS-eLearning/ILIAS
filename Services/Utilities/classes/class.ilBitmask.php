<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilBitmask is a utility class to deal with bitmask-based settings.
 *
 * The concept is to instantiate an instance of the class, passing an array of setting-names and the bitmask.
 * Then you can access the bitmasks individual settings in your class with a simple accessor. This is a
 * stable alternative to defining the cardinality of the individual settings in a dozen setters but in one
 * convenient place.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 */
class ilBitmask
{
	/** @var $setting_names string[]    Array of names ordered by ordinality of the bits in the mask*/
	protected $setting_names;

	/** @var $bitmask       integer     Integer holding the current bitmask */
	protected $bitmask;

	/**
	 * Public constructor instantiating a class of type ilBitmask
	 *
	 * @param $a_setting_names  string[]    Array of names ordered by ordinality
	 * @param $a_bitmask        integer     Integer holding the current bitmask
	 *
	 * @return ilBitmask
	 */
	public function __construct($a_setting_names, $a_bitmask)
	{
		$this->setting_names    = $a_setting_names;
		$this->bitmask          = $a_bitmask;
		return;
	}

	/**
	 * Gets the given setting from the bitmask.
	 *
	 * @param $a_setting_name   string  Name of the setting.
	 *
	 * @return bool
	 * @throws ilException Thrown when setting is not available.
	 */
	public function get($a_setting_name)
	{
		$i = 1;
		foreach($this->setting_names as $name)
		{
			if ($name == $a_setting_name)
			{
				$retval = (($this->bitmask & $i) > 0);
				return $retval;
			}
			$i = $i * 2;
		}
		require_once './Services/Exceptions/classes/class.ilException.php';
		throw new ilException ('No such setting on bitmask.');
	}

	/**
	 * Sets the given setting from the bitmask.
	 *
	 * @param $a_setting_name
	 * @param $value
	 *
	 * @return void
	 * @throws ilException Thrown when setting is not available.
	 */
	public function set($a_setting_name, $value)
	{
		if (!in_array($a_setting_name, $this->setting_names))
		{
			require_once './Services/Exceptions/classes/class.ilException.php';
			throw new ilException ('No such setting on bitmask.');
		}

		$current_value = $this->get($a_setting_name);
		if ($current_value == $value)
		{

			return;
		}

		$i = 1;
		foreach($this->setting_names as $name)
		{
			if ($name == $a_setting_name)
			{
				if ($value == true)
				{
					$this->bitmask = $this->bitmask | $i;
				}
				else
				{
					$this->bitmask = $this->bitmask ^ $i;
				}
			}
			$i = $i * 2;
		}
		return;
	}

	/**
	 * Returns the bitmask.
	 *
	 * @return integer $bitmask The current bitmask.
	 */
	public function  getBitmask()
	{
		return $this->bitmask;
	}
}