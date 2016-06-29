<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/classes/class.ilMathBaseAdapter.php';

/**
 * Class ilMathPhpAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMathPhpAdapter extends ilMathBaseAdapter
{
	/**
	 * {@inheritdoc}
	 */
	public function add($left_operand, $right_operand, $scale = 50)
	{
		$res = $this->normalize($left_operand) + $this->normalize($right_operand);

		return $this->applyScale($res, $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function sub($left_operand, $right_operand, $scale = 50)
	{
		$res = $this->normalize($left_operand) - $this->normalize($right_operand);

		return $this->applyScale($res, $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function mul($left_operand, $right_operand, $scale = 50)
	{
		$res = $this->normalize($left_operand) * $this->normalize($right_operand);

		return $this->applyScale($res, $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function div($left_operand, $right_operand, $scale = 50)
	{
		if($right_operand == 0)
		{
			throw new ilMathDevisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
		}

		$res = $this->normalize($left_operand) / $this->normalize($right_operand);

		return $this->applyScale($res, $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function mod($left_operand, $right_operand)
	{
		if($right_operand == 0)
		{
			throw new ilMathDevisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
		}

		$res = $this->normalize($left_operand) % $this->normalize($right_operand);

		return $res;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pow($left_operand, $right_operand, $scale = 50)
	{
		$res = pow($this->normalize($left_operand), $this->normalize($right_operand));

		return $this->applyScale($res, $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function sqrt($operand, $scale = 50)
	{
		$res = sqrt($this->normalize($operand));

		return $this->applyScale($res, $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function comp($left_operand, $right_operand, $scale = 50)
	{
		$left_operand  = $this->normalize($left_operand);
		$right_operand = $this->normalize($right_operand);
		$scale         = $this->normalize($scale);

		if(is_numeric($scale))
		{
			$left_operand  = $this->applyScale($left_operand, $scale);;
			$right_operand = $this->applyScale($right_operand, $scale);;
		}

		if($left_operand == $right_operand)
		{
			return 0;
		}
		else if ($left_operand > $right_operand) 
		{
			return 1;
		}
		else
		{
			return -1;
		}
	}
}