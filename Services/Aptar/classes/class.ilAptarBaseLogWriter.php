<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Aptar/interfaces/interface.ilAptarLogWriter.php';

/**
 * Class ilAptarBaseLogWriter
 */
abstract class ilAptarBaseLogWriter implements ilAptarLogWriter
{
	const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%): %message% %extra%';

	/**
	 * @param array $message
	 * @return void
	 */
	abstract protected function doWrite(array $message);

	/**
	 * @return string
	 */
	protected static function getMemoryUsageString()
	{
		return 'Memory: ' . self::formatBytes(memory_get_usage(true));
	}

	/***
	 * @param $bytes
	 * @return string
	 */
	private static function formatBytes($bytes)
	{
		$memoryUnits = array('', 'kilobyte(s)', 'megabyte(s)', 'gigabyte(s)');

		$i = 0;
		while(1023 < $bytes)
		{
			$bytes /= 1024;
			++$i;
		}

		return $i ? (round($bytes, 2) . ' ' . $memoryUnits[$i]) : ($bytes . ' byte(s)');
	}

	/**
	 * @return string
	 */
	protected static function getDateTimeFormat()
	{
		return 'y-m-d H:i:s';
	}

	/**
	 * Could be replaced by a formatter object in later releases (means: never ;-))
	 * @param array $message
	 * @return string
	 */
	protected function format(array $message)
	{
		$output = self::DEFAULT_FORMAT;
		foreach($message as $part => $value)
		{
			if('extra' == $part && count($value))
			{
				$value = $this->normalize($value);
			}
			else if('extra' == $part)
			{
				// Don't print an empty array
				$value = '';
			}
			else
			{
				$value = $this->normalize($value);
			}
			$output = str_replace("%$part%", $value, $output);
		}
		return $output;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function normalize($value)
	{
		if(is_scalar($value) || null === $value)
		{
			return $value;
		}

		if($value instanceof ilDateTime)
		{
			$value = date(self::getDateTimeFormat(), $value->get(IL_CAL_UNIX));
		}
		else if(is_array($value))
		{
			foreach($value as $key => $subvalue)
			{
				$value[$key] = $this->normalize($subvalue);
			}
			$value = json_encode($value);
		}
		elseif(is_object($value) && !method_exists($value, '__toString'))
		{
			$value = sprintf('object(%s) %s', get_class($value), json_encode($value));
		}
		else if(is_resource($value))
		{
			$value = sprintf('resource(%s)', get_resource_type($value));
		}
		elseif(!is_object($value))
		{
			$value = gettype($value);
		}

		return (string)$value;
	}

	/**
	 * @param array $message
	 * @return void
	 */
	public function write(array $message)
	{
		$this->doWrite($message);
	}
}