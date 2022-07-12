<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
        if (!is_dir($iliasPDFTestPath) && !mkdir($iliasPDFTestPath) && !is_dir($iliasPDFTestPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $iliasPDFTestPath));
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
    public static function setCheckedIfTrue(ilPropertyFormGUI $form) : void
    {
        foreach ($form->getItems() as $item) {
            if ($item instanceof ilCheckboxInputGUI) {
                if ($item->getValue() || $item->getValue() === '1') {
                    $item->setChecked(true);
                }
            }
        }
    }

    /**
     * @return array<string, string[]>
     */
    public static function getPurposeMap() : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $purposes = [];

        $result = $ilDB->query('SELECT service, purpose FROM pdfgen_purposes ORDER BY service, purpose');

        while ($row = $ilDB->fetchAssoc($result)) {
            $purposes[$row['service']][] = $row['purpose'];
        }

        return $purposes;
    }

    /**
     * @return array<string, array<string, array{selected: string, preferred: string}>>
     */
    public static function getSelectionMap() : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $mappings = [];
        $result = $ilDB->query('SELECT service, purpose, preferred, selected FROM pdfgen_map');

        while ($row = $ilDB->fetchAssoc($result)) {
            $mappings[$row['service']][$row['purpose']]['selected'] = $row['selected'];
            $mappings[$row['service']][$row['purpose']]['preferred'] = $row['preferred'];
        }

        return $mappings;
    }

    /**
     * @return array<string, array<string, string[]>>
     */
    public static function getRenderers() : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $renderers = [];
        $result = $ilDB->query('SELECT renderer, service, purpose FROM pdfgen_renderer_avail');

        while ($row = $ilDB->fetchAssoc($result)) {
            $renderers[$row['service']][$row['purpose']][] = $row['renderer'];
        }

        return $renderers;
    }

    public static function updateRendererSelection(string $service, string $purpose, string $renderer) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->update(
            'pdfgen_map',
            ['selected' => ['text', $renderer]],
            [
                'service' => ['text', $service],
                'purpose' => ['text', $purpose]
            ]
        );
    }

    public static function getRendererConfig(string $service, string $purpose, string $renderer)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT config FROM pdfgen_conf WHERE renderer = ' . $ilDB->quote($renderer, 'text') .
            ' AND service = ' . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');

        $result = $ilDB->query($query);
        if ($ilDB->numRows($result) === 0) {
            return self::getRendererDefaultConfig($service, $purpose, $renderer);
        }

        $row = $ilDB->fetchAssoc($result);
        return json_decode($row['config'], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws Exception
     */
    public static function getRendererDefaultConfig(string $service, string $purpose, string $renderer)
    {
        $class_instance = self::getRendererInstance($renderer);

        return $class_instance->getDefaultConfig($service, $purpose);
    }

    public static function removeRendererConfig(string $service, string $purpose, string $renderer) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'DELETE FROM pdfgen_conf WHERE renderer = ' . $ilDB->quote($renderer, 'text') .
            ' AND service = ' . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');

        $ilDB->manipulate($query);
    }

    /**
     * @throws Exception
     * @return ilRendererConfig&ilPDFRenderer
     */
    public static function getRendererInstance(string $renderer)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->query('SELECT path FROM pdfgen_renderer WHERE renderer = ' . $ilDB->quote($renderer, 'text'));

        if ($ilDB->numRows($result) === 0) {
            throw new Exception('No such renderer - given: ' . $renderer);
        }
        $row = $ilDB->fetchAssoc($result);

        $classname = 'il' . $renderer . 'Renderer';
        if (self::isRendererPlugin($row['path'])) {
            $classname = 'il' . $renderer . 'RendererPlugin';
        }

        return new $classname();
    }

    protected static function isRendererPlugin(string $path) : bool
    {
        $needle = 'Plugin.php';
        $length = strlen($needle);
        return (substr($path, -$length) === $needle);
    }

    public static function saveRendererPurposeConfig(string $service, string $purpose, string $renderer, array $config) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'DELETE FROM pdfgen_conf WHERE renderer = ' . $ilDB->quote($renderer, 'text') .
            ' AND service = ' . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');

        $ilDB->manipulate($query);

        $ilDB->insert(
            'pdfgen_conf',
            [
                'conf_id' => ['integer', $ilDB->nextId('pdfgen_conf')],
                'renderer' => ['text', $renderer],
                'service' => ['text', $service],
                'purpose' => ['text', $purpose],
                'config' => ['clob', json_encode($config, JSON_THROW_ON_ERROR)]
            ]
        );
    }

    /**
     * @param string $service
     * @param string $purpose
     * @return array{selected: string, preferred: string}
     */
    public static function getRendererMapForPurpose(string $service, string $purpose) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $ilDB->query(
            'SELECT preferred, selected FROM pdfgen_map WHERE service = ' . $ilDB->quote($service, 'text') . ' ' .
            'AND purpose=' . $ilDB->quote($purpose, 'text')
        );

        if ($ilDB->numRows($result) === 0) {
            return ['selected' => 'TCPDF', 'preferred' => 'TCPDF'];
        }

        return $ilDB->fetchAssoc($result);
    }
}
