<?php

class assClozeGapCombination 
{
	public function loadFromDb($question_id)
	{
		global $ilDB;
		
			$result = $ilDB->queryF('SELECT combination_id,gap_fi, answer, cloze_type, combinations.points, best_solution
									 	FROM qpl_a_cloze_combinations AS combinations
										INNER JOIN qpl_a_cloze AS cloze
										WHERE combinations.question_fi = cloze.question_fi
										AND combinations.gap_fi = cloze.gap_id
										AND combinations.question_fi = %s GROUP BY combination_id, gap_fi',
//										AND combinations.answer = cloze.answertext
				array('integer'),
				array($question_id)
			);
			$return_array = array();
			if ($result->numRows() > 0)
			{
				while ($data = $ilDB->fetchAssoc($result))
				{
					$return_array[]=array(
											'cid' 			=> $data['combination_id'],
										 	'gap_fi' 		=> $data['gap_fi'], 
										  	'answer' 		=> $data['answer'], 
										  	'points' 		=> $data['points'],
										  	'type' 			=> $data['cloze_type'], 
										  	'best_solution'	=> $data['best_solution']
										 );
				}
			}
		return $return_array;
	}

	public function getCleanCombinationArray($question_id)
	{
		$assClozeGapCombinationObj = new assClozeGapCombination();
		$combination_from_db = $assClozeGapCombinationObj->loadFromDb($question_id);
		$clean_array = array();
		foreach($combination_from_db as $key => $value)
		{
			$clean_array[$value['cid']][$value['gap_fi']]['answer'] = $value['answer'];
			$clean_array[$value['cid']]['points'] 					= $value['points'];
			$clean_array[$value['cid']][$value['gap_fi']]['type'] 	= $value['type'];
		}
		return $clean_array;	
	}
	
	public function saveGapCombinationToDb($question_id, $gap_combinations, $best_solution)
	{
		global $ilDB;
		
		foreach($gap_combinations as $key => $row)
		{
			if(is_array($row))
			{
				foreach($row as $key2 => $gap)
				{
					$best_possible_solution = 0;
					if($key == $best_solution)
					{
						$best_possible_solution = 1;
					}
					if(is_array($gap))
					{
						$ilDB->manipulateF( 'INSERT INTO qpl_a_cloze_combinations
			 				(combination_id, question_fi, gap_fi, answer, points, best_solution) VALUES (%s, %s, %s, %s, %s, %s)',
							array(
								'integer',
								'integer',
								'integer',
								'text',
								'float',
								'integer'
							),
							array(
								$key,
								$question_id,
								$gap['select'],
								$gap['value'],
								$row['points'],
								$best_possible_solution
							)
						);
					}
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
				$ilDB->manipulateF( 'INSERT INTO qpl_a_cloze_combinations
				(combination_id, question_fi, gap_fi, answer, points, best_solution) VALUES (%s, %s, %s, %s, %s, %s)',
					array(
						'integer',
						'integer',
						'integer',
						'text',
						'integer',
						'integer'
					),
					array(
						$row['cid'],
						$question_id,
						$row['gap_fi'],
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

		$ilDB->manipulateF( 'DELETE FROM qpl_a_cloze_combinations WHERE question_fi = %s',
			array( 'integer' ),
			array( $question_id )
		);
	}

	public function combinationExistsForQid($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF('SELECT * FROM qpl_a_cloze_combinations WHERE question_fi = %s ORDER BY gap_fi ASC',
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

	public function getMaxPointsForCombination($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF('SELECT points FROM qpl_a_cloze_combinations WHERE question_fi = %s AND best_solution=1 GROUP BY points',
			array('integer'),
			array($question_id)
		);
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				return $data['points'];
			}
		}
		else
		{
			return 0;
		}
	}

	public function getBestSolutionCombination($question_id)
	{
		global $ilDB, $lng;

		$result = $ilDB->queryF('SELECT * FROM qpl_a_cloze_combinations WHERE question_fi = %s AND best_solution=1 ORDER BY gap_fi',
			array('integer'),
			array($question_id)
		);
		if ($result->numRows() > 0)
		{
			$return_string ='';
			while ($data = $ilDB->fetchAssoc($result))
			{
				$return_string .= $data['answer'].'|';
				$points = ' (' . $data['points'] . ' '. $lng->txt('points') .')';
			}
			return rtrim($return_string, '|') . $points;
		}
		else
		{
			return 0;
		}
	}
} 