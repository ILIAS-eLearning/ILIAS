<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilPDFGeneratorUtils
 */
class ilPDFGeneratorUtils
{
    /**
     * Prepare the content processing for a PDF generation request
     * This function should be called as in a request before any content is generated
     * It sets the generation mode for Latex processing according the needs of the PDF renderer
     */
    public static function prepareGenerationRequest(string $service, string $purpose) : void
    {
        try {
            $map = self::getRendererMapForPurpose($service, $purpose);
            $renderer = self::getRendererInstance($map['selected']);
            $renderer->prepareGenerationRequest($service, $purpose);
        } catch (Exception $e) {
            return;
        }
    }

    public static function getTestPdfDir() : string
    {
        $iliasPDFTestPath = 'data/' . CLIENT_ID . '/pdf_service/';
        if (!file_exists($iliasPDFTestPath)) {
            mkdir($iliasPDFTestPath);
        }
        return $iliasPDFTestPath;
    }

    public static function removePrintMediaDefinitionsFromStyleFile(string $path) : void
    {
        foreach (glob($path . '*.css') as $filename) {
            $content = file_get_contents($filename);
            $content = preg_replace('/@media[\s]* print/', '@media nothing', $content);
            file_put_contents($filename, $content);
        }
    }

    public static function removeWrongPathFromStyleFiles(string $path) : void
    {
        foreach (glob($path . '*.css') as $filename) {
            $content = file_get_contents($filename);
            $content = preg_replace('/src:\surl\([\',\"](..\/)*(\S)/', "src: url(./$2", $content);
            file_put_contents($filename, $content);
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public static function setCheckedIfTrue(\ilPropertyFormGUI $form) : void
    {
        foreach ($form->getItems() as $item) {
            if ($item instanceof ilCheckboxInputGUI) {
                if ($item->getChecked() != null && ($item->getValue() == true || $item->getValue() == '1')) {
                    $item->setChecked(true);
                }
            }
        }
    }

    /**
     * @return array<int|string, mixed[]>
     */
    public static function getPurposeMap() : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $purposes = array();

        $result = $ilDB->query('SELECT service, purpose FROM pdfgen_purposes ORDER BY service, purpose');

        while ($row = $ilDB->fetchAssoc($result)) {
            $purposes[$row['service']][] = $row['purpose'];
        }

        return $purposes;
    }

    /**
     * @return array<int|string, array<int|string, array<string, mixed>>>
     */
    public static function getSelectionMap() : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $mappings = array();
        $result = $ilDB->query('SELECT service, purpose, preferred, selected FROM pdfgen_map');

        while ($row = $ilDB->fetchAssoc($result)) {
            $mappings[$row['service']][$row['purpose']]['selected'] = $row['selected'];
            $mappings[$row['service']][$row['purpose']]['preferred'] = $row['preferred'];
        }

        return $mappings;
    }

    /**
     * @return array<int|string, array<int|string, mixed[]>>
     */
    public static function getRenderers() : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $renderers = array();
        $result = $ilDB->query('SELECT renderer, service, purpose FROM pdfgen_renderer_avail');

        while ($row = $ilDB->fetchAssoc($result)) {
            $renderers[$row['service']][$row['purpose']][] = $row['renderer'];
        }

        return $renderers;
    }

    public static function updateRendererSelection(string $service, string $purpose, string $renderer) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->update(
            'pdfgen_map',
            array( 'selected' => array('text', $renderer) ),
            array(
                             'service' => array('text', $service),
                             'purpose' => array('text', $purpose)
                      )
        );
    }

    /**
     * @return array
     */
    public static function getRendererConfig(string $service, string $purpose, string $renderer)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT config FROM pdfgen_conf WHERE renderer = ' . $ilDB->quote($renderer, 'text') .
            ' AND service = ' . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');

        $result = $ilDB->query($query);
        if ($ilDB->numRows($result) == 0) {
            return self::getRendererDefaultConfig($service, $purpose, $renderer);
        } else {
            $row = $ilDB->fetchAssoc($result);
            return json_decode($row['config'], true);
        }
    }

    /**
     * @return mixed[]
     */
    public static function getRendererDefaultConfig(string $service, string $purpose, string $renderer) : array
    {
        /** @var ilRendererConfig $class_instance */
        $class_instance = self::getRendererInstance($renderer);

        return $class_instance->getDefaultConfig($service, $purpose);
    }

    public static function removeRendererConfig(string $service, string $purpose, string $renderer) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM pdfgen_conf WHERE renderer = ' . $ilDB->quote($renderer, 'text') .
            ' AND service = ' . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');

        $ilDB->manipulate($query);
    }

    /**
     * @param $renderer
     * @throws Exception
     */
    public static function getRendererInstance($renderer) : object
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query('SELECT path FROM pdfgen_renderer WHERE renderer = ' . $ilDB->quote($renderer, 'text'));

        if ($ilDB->numRows($result) == 0) {
            throw new Exception('No such renderer - given: ' . $renderer);
        }
        $row = $ilDB->fetchAssoc($result);
        if (self::isRendererPlugin($row['path'])) {
            $classname = 'il' . $renderer . 'RendererPlugin';
        } else {
            $classname = 'il' . $renderer . 'Renderer';
        }

        if (false && !class_exists($classname)) {
            throw new Exception(
                'Class failed loading, including path ' . $row['path']
                . ' did not lead to class definition ' . $classname . ' being available.'
            );
        }

        $class_instance = new $classname;
        return $class_instance;
    }

    /**
     * @param $path
     */
    protected static function isRendererPlugin($path) : bool
    {
        $needle = 'Plugin.php';
        $length = strlen($needle);
        return (substr($path, -$length) === $needle);
    }

    /**
     * @param $config
     */
    public static function saveRendererPurposeConfig(string $service, string $purpose, string $renderer, $config) : void
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
                'renderer' => array('text', $renderer),
                'service' => array('text', $service),
                'purpose' => array('text', $purpose),
                'config' => array('clob', json_encode($config))
            )
        );
    }

    /**
     * @return array|mixed
     */
    public static function getRendererMapForPurpose(string $service, string $purpose)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query('SELECT preferred, selected FROM pdfgen_map WHERE
		service = ' . $ilDB->quote($service, 'text') . ' AND purpose=' . $ilDB->quote($purpose, 'text'));

        if ($ilDB->numRows($result) == 0) {
            return array('selected' => 'TCPDF', 'preferred' => 'TCPDF');
        }
        $row = $ilDB->fetchAssoc($result);

        return $row;
    }
}
