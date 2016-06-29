<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/classes/class.ilMathBigIntAdapter.php';

/**
 * Class ilMathBCMathAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMathBCMathAdapter extends ilMathBigIntAdapter
{
	/**
	 * ilMathBcMathAdapter constructor.
	 */
	public function __construct()
	{
		bcscale(0);
	}

	/**
	 * {@inheritdoc}
	 */
	public function add($left_operand, $right_operand, $scale = 50)
	{
		return bcadd($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function sub($left_operand, $right_operand, $scale = 50)
	{
		return bcsub($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
	}

	/**
	 * {@inheritdoc}
	 */
	public function mul($left_operand, $right_operand, $scale = 50)
	{
		return bcmul($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
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

		return bcdiv($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
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

		return bcmod($this->normalize($left_operand), $this->normalize($right_operand));
	}

	/**
	 * {@inheritdoc}
	 */
	public function pow($left_operand, $right_operand, $scale = 50)
	{
		$left_operand  = $this->normalize($left_operand);
		$right_operand = $this->normalize($right_operand);
		$scale         = $this->normalize($scale);

		// bcpow() only supports exponents less than or equal to 2^31-1.
		// Also, bcpow() does not support decimal numbers.
		// If you have scale set to 0, then the exponent is converted to an integer; otherwise an error is generated.
		$left_operand_dec  = $this->exp2dec($left_operand);
		$right_operand_dec = $this->exp2dec($right_operand);

		// bcpow does NOT support decimal exponents
		if(strpos($right_operand_dec, '.') === false)
		{
			return bcpow($left_operand_dec, $right_operand_dec, $scale);
		}

		return $this->applyScale(pow($left_operand, $right_operand), $scale);
	}

	/**
	 * {@inheritdoc}
	 */
	public function sqrt($operand, $scale = 50)
	{
		return bcsqrt($operand,  $scale);
	}

	/**
	 * {@inheritdoc}
	 */
	public function comp($left_operand, $right_operand, $scale = 50)
	{
		return bccomp($left_operand, $right_operand, $scale);
	}
}