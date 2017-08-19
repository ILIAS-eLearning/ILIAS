<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilMathTest extends PHPUnit_Framework_TestCase
{
	const DEFAULT_SCALE = '50';

	/**
	 * @var EvalMath
	 */
	protected $eval_math;

	/**
	 * 
	 */
	protected function setUp()
	{
		require_once 'Services/Math/classes/class.ilMath.php';
		require_once 'Services/Math/classes/class.EvalMath.php';

		$this->eval_math = new EvalMath();
	}

	/**
	 * @dataProvider addData
	 */
	public function testAdd($a, $b, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_add($a, $b, $scale));
	}

	/**
	 *  @dataProvider subData
	 */
	public function testSub($a, $b, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_sub($a, $b, $scale));
	}

	/**
	 *  @dataProvider mulData
	 */
	public function testMul($a, $b, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_mul($a, $b, $scale));
	}

	/**
	 *  @dataProvider divData
	 */
	public function testDiv($a, $b, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_div($a, $b, $scale));
	}

	/**
	 *  @dataProvider sqrtData
	 */
	public function testSqrt($a, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_sqrt($a, $scale));
	}

	/**
	 *  @dataProvider powData
	 */
	public function testPow($a, $b, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_pow($a, $b, $scale));
	}

	/**
	 *  @dataProvider modData
	 */
	public function testMod($a, $b, $result)
	{
		$this->assertEquals($result, ilMath::_mod($a, $b));
	}

	/**
	 *  @dataProvider equalsData
	 */
	public function testEquals($a, $b, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_equals($a, $b, $scale));
	}

	/**
	 *  @dataProvider roundData
	 */
	public function testRound($a, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_round($a, $scale));
	}

	/**
	 * @dataProvider gcdData
	 */
	public function testGcd($a, $b, $result)
	{
		$this->assertEquals($result, ilMath::getGreatestCommonDivisor($a, $b));
	}

	/**
	 *  @dataProvider calcData
	 */
	public function testCalculation($formula, $result, $scale)
	{
		$this->assertEquals($result, ilMath::_round($this->eval_math->evaluate($formula), $scale));
	}

	/******************************************************************************************************************/

	/**
	 * @return array
	 */
	public function addData()
	{
		return [
			['1', '2', '3', self::DEFAULT_SCALE]
		];
	}

	/**
	 * @return array
	 */
	public function subData()
	{
		return [
			['1', '2', '-1', self::DEFAULT_SCALE]
		];
	}

	/**
	 * @return array
	 */
	public function mulData()
	{
		return [
			['1', '2', '2', self::DEFAULT_SCALE]
		];
	}

	/**
	 * @return array
	 */
	public function divData()
	{
		return [
			['1', '2', '0.5', self::DEFAULT_SCALE]
		];
	}

	/**
	 * @return array
	 */
	public function modData()
	{
		return [
			['1', '2', '1']
		];
	}
	
	/**
	 * @return array
	 */
	public function sqrtData()
	{
		$values = [
			['9', '3', self::DEFAULT_SCALE],
			['4294967296', '65536', self::DEFAULT_SCALE]
		];
		if(extension_loaded('bcmath')) 
		{
			array_push($values, ['4294967296', '65536', self::DEFAULT_SCALE]);
		}
		return $values;
	}
	
	/**
	 * @return array
	 */
	public function powData()
	{
		return [
			['3', '2', '9', self::DEFAULT_SCALE],
			['2', '64', '18446744073709551616', self::DEFAULT_SCALE]
		];
	}
	
	/**
	 * @return array
	 */
	public function equalsData()
	{
		return [
			['3', '3', true, self::DEFAULT_SCALE]
		];
	}

	/**
	 * @return array
	 */
	public function roundData()
	{
		return [
			['2.4742', '2.47', '2'],
			['2.4762', '2.48', '2']
		];
	}
	
	/**
	 * @return array
	 */
	public function gcdData()
	{
		return [
			['1254', '5298', '6'],
			['41414124', '41414124', '41414124']
		];
	}

	/**
	 * 
	 */
	public function calcData()
	{
		return [
			['3+5', '8', self::DEFAULT_SCALE],
			['-3+5', '2', self::DEFAULT_SCALE],
			['3*6+5', '23', self::DEFAULT_SCALE],
			['10/2', '5', self::DEFAULT_SCALE],
			['13/60', '0.2166666666667', '13'],
			['(-(-8)-sqrt((-8)^2-4*(7)))/(2)', '1', self::DEFAULT_SCALE],
			['(-(-8)+sqrt((-8)^2-4*(7)))/(2)', '7', self::DEFAULT_SCALE],
			['(-(-41)-sqrt((-41)^2-4*(1)*(5)))/(2*(1))', '0.122', '3'],
			['(-(-41)+sqrt((-41)^2-4*(1)*(5)))/(2*(1))', '40.878', '3'],
			['4^2-2*4+0.5*-16', '0', self::DEFAULT_SCALE],
			['-2^2-2*-2+0.5*-16', '-8', self::DEFAULT_SCALE]
		];
	}
}