<?php


class ilPDFGeneratorUtils
{
	// --

	public static function getTestPdfDir()
	{
		$iliasPDFTestPath      = 'data/' . CLIENT_ID . '/pdf_service/';
		if(!file_exists($iliasPDFTestPath))
		{
			mkdir($iliasPDFTestPath);
		}
		return $iliasPDFTestPath;
	}

	public static function removePrintMediaDefinitionsFromStyleFile($path)
	{
		foreach (glob($path . '*.css') as $filename)
		{
			$content = file_get_contents($filename);
			$content = preg_replace('/@media[\s]* print/', '@media nothing', $content);
			file_put_contents($filename, $content);
		}
	}

	public static function removeWrongPathFromStyleFiles($path)
	{
		foreach (glob($path . '*.css') as $filename)
		{
			$content = file_get_contents($filename);
			$content = preg_replace('/src:\surl\([\',\"](..\/)*(\S)/', "src: url(./$2", $content);
			file_put_contents($filename, $content);
		}
	}

	// --

	public static function getPurposeMap()
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$purposes = array();

		$result = $ilDB->query('SELECT service, purpose FROM pdfgen_purposes');

		while($row = $ilDB->fetchAssoc($result))
		{
			$purposes[$row['service']][] = $row['purpose'];
		}

		return $purposes;
	}

	public static function getSelectionMap()
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$mappings = array();
		$result = $ilDB->query('SELECT service, purpose, preferred, selected FROM pdfgen_map');

		while($row = $ilDB->fetchAssoc($result))
		{
			$mappings[$row['service']][$row['purpose']]['selected'] = $row['selected'];
			$mappings[$row['service']][$row['purpose']]['preferred'] = $row['preferred'];
		}

		return $mappings;
	}

	public static function getRenderers()
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$renderers = array();
		$result = $ilDB->query('SELECT renderer, service, purpose FROM pdfgen_renderer_avail');

		while($row = $ilDB->fetchAssoc($result))
		{
			$renderers[$row['service']][$row['purpose']][] = $row['renderer'];
		}

		return $renderers;
	}

	public static function updateRendererSelection($service, $purpose, $renderer)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$ilDB->update('pdfgen_map',
					  array( 'selected' => array('text', $renderer) ),
					  array(
							 'service' => array('text', $service),
							 'purpose' => array('text', $purpose)
					  )
		);
	}

	public static function getRendererConfig($service, $purpose, $renderer)
	{
		global $DIC;
		/** @var ilDB $ilDB */
		$ilDB = $DIC['ilDB'];

		$query = 'SELECT config FROM pdfgen_conf WHERE renderer = ' . $ilDB->quote($renderer, 'text') .
			' AND service = ' . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');

		$result = $ilDB->query($query);
		if($ilDB->numRows($result) == 0)
		{
			return self::getRendererDefaultConfig($service, $purpose, $renderer);
		}
		else
		{
			$row = $ilDB->fetchAssoc($result);
			return json_decode($row['config'], true);
		}
	}

	public static function getRendererDefaultConfig($service, $purpose, $renderer)
	{
		/** @var ilRendererConfig $class_instance */
		$class_instance = self::getRendererInstance($renderer);

		return $class_instance->getDefaultConfig($service, $purpose);
	}

	public static function getRendererInstance($renderer)
	{
		global $DIC;
		/** @var ilDB $ilDB */
		$ilDB = $DIC['ilDB'];

		$result = $ilDB->query('SELECT path FROM pdfgen_renderer WHERE renderer = ' . $ilDB->quote($renderer, 'text'));

		if($ilDB->numRows($result) == 0)
		{
			throw new Exception('No such renderer - given: ' . $renderer);
		}
		$row = $ilDB->fetchAssoc($result);

		include_once $row['path'];
		$classname = 'il'.$renderer.'Renderer';
		if(!class_exists($classname))
		{
			throw new Exception(
				'Class failed loading, including path ' . $row['path']
				. ' did not lead to class definition ' . $classname . ' being available.');
		}

		$class_instance = new $classname;
		return $class_instance;
	}

	public static function saveRendererPurposeConfig($service, $purpose, $renderer, $config)
	{
		global $DIC;
		/** @var ilDB $ilDB */
		$ilDB = $DIC['ilDB'];

		$query = 'DELETE FROM pdfgen_conf WHERE renderer = ' . $ilDB->quote($renderer, 'text') .
			' AND service = ' . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');

		$ilDB->manipulate($query);

		$ilDB->insert(
			'pdfgen_conf',
			array(
				'conf_id' => array('integer', $ilDB->nextId('pdfgen_conf')),
				'renderer'	=> array('text', $renderer),
				'service'	=> array('text', $service),
				'purpose'	=> array('text', $purpose),
				'config'	=> array('clob', json_encode($config))
			)
		);
	}

	public static function getRendererMapForPurpose($service, $purpose)
	{
		global $DIC;
		/** @var ilDB $ilDB */
		$ilDB = $DIC['ilDB'];

		$result = $ilDB->query('SELECT preferred, selected FROM pdfgen_map WHERE
		service = ' . $ilDB->quote($service, 'text') . ' AND purpose=' . $ilDB->quote($purpose, 'text'));

		if($ilDB->numRows($result) == 0)
		{
			throw new Exception('No such map - given: ' . $service . ' / ' . $purpose);
		}
		$row = $ilDB->fetchAssoc($result);

		return $row;
	}
}