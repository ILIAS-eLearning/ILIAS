<?php

class assClozeGapCombination 
{
	public function loadFromDb($question_id)
	{
		global $ilDB;
		$result = $ilDB->queryF('
									SELECT 	combinations.combination_id,
											combinations.gap_fi,
											combinations.answer,
											combinations.row_id,
											combinations.points,
											combinations.best_solution,
											combinations.question_fi,
											cloze.cloze_type
									FROM 	qpl_a_cloze_combi_res AS combinations
									INNER JOIN qpl_a_cloze AS cloze
													WHERE combinations.question_fi = cloze.question_fi
													AND combinations.gap_fi = cloze.gap_id
													AND combinations.question_fi = %s 
									ORDER BY combination_id, row_id, gap_fi ASC
									',
			array('integer'),
			array($question_id)
		);

		$return_array = array();
		while ($data = $ilDB->fetchAssoc($result))
		{
			if( isset($return_array[$data['combination_id'].'::'.$data['gap_fi']]) )
			{
				continue;
			}
			
			$return_array[$data['combination_id'].'::'.$data['row_id'].'::'.$data['gap_fi']]=array(
									'cid' 			=> $data['combination_id'],
									'gap_fi' 		=> $data['gap_fi'], 
									'answer' 		=> $data['answer'], 
									'points' 		=> $data['points'],
									'row_id'		=> $data['row_id'],
									'type'			=> $data['cloze_type'],
									'best_solution'	=> $data['best_solution']
								 );
		}
		
		return array_values($return_array);
	}

	public function getCleanCombinationArray($question_id)
	{
		$assClozeGapCombinationObj = new assClozeGapCombination();
		$combination_from_db = $assClozeGapCombinationObj->loadFromDb($question_id);
		$clean_array = array();
		foreach($combination_from_db as $key => $value)
		{
			$clean_array[$value['cid']][$value['row_id']][$value['gap_fi']]['answer'] 	= $value['answer'];
			$clean_array[$value['cid']][$value['row_id']]['points'] 					= $value['points'];
			$clean_array[$value['cid']][$value['row_id']][$value['gap_fi']]['type'] 	= $value['type'];
		}
		return $clean_array;	
	}
	
	public function saveGapCombinationToDb($question_id, $gap_combinations, $gap_values)
	{
		global $ilDB;
		$best_solutions = array();
		for($i = 0; $i < count($gap_combinations['points']); $i++)
		{
			$highest_points = 0;
			for($j = 0; $j < count($gap_combinations['points'][$i]); $j++)
			{
				if($highest_points < $gap_combinations['points'][$i][$j])
				{
					$highest_points = $gap_combinations['points'][$i][$j];
					$best_solutions[$i] = $j;
				}
			}
		}
		for($i = 0; $i < count($gap_values); $i++)
		{
			for($j = 0; $j < count($gap_values[$i]); $j++)
			{
				for($k = 0; $k < count($gap_values[$i][$j]); $k++)
				{
					if($best_solutions[$i] == $j )
					{
						$best_solution = 1;
					}
					else
					{
						$best_solution = 0;
					}
					$ilDB->manipulateF( 'INSERT INTO qpl_a_cloze_combi_res
			 				(combination_id, question_fi, gap_fi, row_id, answer, points, best_solution) VALUES (%s, %s, %s, %s, %s, %s, %s)',
						array(
							'integer',
							'integer',
							'integer',
							'integer',
							'text',
							'float',
							'integer'
						),
						array(
							$i,
							$question_id,
							$gap_combinations['select'][$i][$k],
							$j,
							$gap_values[$i][$j][$k],
							$gap_combinations['points'][$i][$j],
							$best_solution
						)
					);
				}
			}
		}
	}
	public function importGapCombinationToDb($question_id, $gap_combinations)
	{
		global $ilDB;

		foreach($gap_combinations as $key => $row)
		{
			if (is_object($row)) 
			{
				$row = get_object_vars($row);
			}
			if($question_id != -1)
			{
				$ilDB->manipulateF( 'INSERT INTO qpl_a_cloze_combi_res
			 				(combination_id, question_fi, gap_fi, row_id, answer, points, best_solution) VALUES (%s, %s, %s, %s, %s, %s, %s)',
					array(
						'integer',
						'integer',
						'integer',
						'integer',
						'text',
						'float',
						'integer'
					),
					array(
						$row['cid'],
						$question_id,
						$row['gap_fi'],
						$row['row_id'],
						$row['answer'],
						$row['points'],
						$row['best_solution']
					)
				);
			}
		}
	}
	public function clearGapCombinationsFromDb($question_id)
	{
		global $ilDB;

		$ilDB->manipulateF( 'DELETE FROM qpl_a_cloze_combi_res WHERE question_fi = %s',
			array( 'integer' ),
			array( $question_id )
		);
	}

	public function combinationExistsForQid($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF('SELECT * FROM qpl_a_cloze_combi_res WHERE question_fi = %s ORDER BY gap_fi ASC',
			array('integer'),
			array($question_id)
		);
		if ($result->numRows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getGapsWhichAreUsedInCombination($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF('SELECT gap_fi, combination_id FROM `qpl_a_cloze_combi_res` WHERE question_fi = %s GROUP BY gap_fi, combination_id',
			array('integer'),
			array($question_id)
		);
		$gaps = array();
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				$gaps[$data['gap_fi']] = $data['combination_id'];
			}
		}
		return $gaps;
	}
	
	public function getMaxPointsForCombination($question_id, $combination_id = -1)
	{
		global $ilDB;

		if($combination_id == -1)
		{
			$result = $ilDB->queryF('SELECT combination_id, points FROM qpl_a_cloze_combi_res WHERE question_fi = %s AND best_solution=1 GROUP BY combination_id, points',
				array('integer'),
				array($question_id)
			);
			if ($result->numRows() > 0)
			{
				$points = 0;
				while ($data = $ilDB->fetchAssoc($result))
				{
					$points += $data['points'];
				}
				return $points;
			}
		}
		else
		{
			$result = $ilDB->queryF('SELECT combination_id, points FROM qpl_a_cloze_combi_res WHERE question_fi = %s AND  combination_id = %s AND best_solution=1 GROUP BY combination_id, points',
				array('integer', 'integer'),
				array($question_id, $combination_id)
			);
			if ($result->numRows() > 0)
			{
				$points = 0;
				while ($data = $ilDB->fetchAssoc($result))
				{
					$points += $data['points'];
				}
				return $points;
			}
		}
		return 0;
	}

	public function getBestSolutionCombination($question_id)
	{
		global $ilDB, $lng;

		$result = $ilDB->queryF('SELECT * FROM qpl_a_cloze_combi_res WHERE question_fi = %s AND best_solution=1 ORDER BY gap_fi',
			array('integer'),
			array($question_id)
		);
		if ($result->numRows() > 0)
		{
			$return_string 	='<br>';
			$combination_id = 0;
			$points			= 0;
			while ($data = $ilDB->fetchAssoc($result))
			{
				if($combination_id != $data['combination_id'])
				{
					$combination_id = $data['combination_id'];
					$return_string .= $points;
					$return_string .= '<br>';
					$return_string .= $data['answer'].'|';
				}
				else
				{
					$return_string .= $data['answer'].'|';
				}
				
				$points = ' (' . $data['points'] . ' '. $lng->txt('points') .')';
			}
			return rtrim($return_string , '|') . $points;
		}
		else
		{
			return 0;
		}
	}
} 