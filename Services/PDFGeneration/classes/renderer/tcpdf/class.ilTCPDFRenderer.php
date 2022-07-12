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

class ilTCPDFRenderer implements ilRendererConfig, ilPDFRenderer
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
    }

    public function addConfigElementsToForm(ilPropertyFormGUI $form, string $service, string $purpose) : void
    {
        $margin_left = new ilTextInputGUI($this->lng->txt('margin_left'), 'margin_left');
        $form->addItem($margin_left);

        $margin_top = new ilTextInputGUI($this->lng->txt('margin_top'), 'margin_top');
        $form->addItem($margin_top);

        $margin_right = new ilTextInputGUI($this->lng->txt('margin_right'), 'margin_right');
        $form->addItem($margin_right);

        $margin_bottom = new ilTextInputGUI($this->lng->txt('margin_bottom'), 'margin_bottom');
        $form->addItem($margin_bottom);

        $image_scale = new ilTextInputGUI($this->lng->txt('image_scale'), 'image_scale');
        $form->addItem($image_scale);
    }

    public function populateConfigElementsInForm(ilPropertyFormGUI $form, string $service, string $purpose, array $config) : void
    {
        $form->getItemByPostVar('margin_left')->setValue($config['margin_left']);
        $form->getItemByPostVar('margin_right')->setValue($config['margin_right']);
        $form->getItemByPostVar('margin_top')->setValue($config['margin_top']);
        $form->getItemByPostVar('margin_bottom')->setValue($config['margin_bottom']);
        $form->getItemByPostVar('image_scale')->setValue($config['image_scale']);
    }

    public function validateConfigInForm(ilPropertyFormGUI $form, string $service, string $purpose) : bool
    {
        return true;
    }

    public function getConfigFromForm(ilPropertyFormGUI $form, string $service, string $purpose) : array
    {
        $retval = [
            'margin_left' => $form->getItemByPostVar('margin_left')->getValue(),
            'margin_right' => $form->getItemByPostVar('margin_right')->getValue(),
            'margin_top' => $form->getItemByPostVar('margin_top')->getValue(),
            'margin_bottom' => $form->getItemByPostVar('margin_bottom')->getValue(),
            'image_scale' => $form->getItemByPostVar('image_scale')->getValue(),
        ];

        return $retval;
    }

    public function getDefaultConfig(string $service, string $purpose) : array
    {
        $retval = [
            'margin_left' => '10',
            'margin_top' => '10',
            'margin_right' => '10',
            'margin_bottom' => '10',
            'image_scale' => '1',
        ];

        return $retval;
    }

    public function prepareGenerationRequest(string $service, string $purpose) : void
    {
        ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
            ->setRendering(ilMathJax::RENDER_PNG_AS_IMG_EMBED)
            ->setDpi(600)
            ->setZoomFactor(0.17);
    }

    public function generatePDF(string $service, string $purpose, array $config, ilPDFGenerationJob $job) : void
    {
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetMargins($config['margin_left'], $config['margin_top'], $config['margin_right']);
        $pdf->SetAutoPageBreak('auto', $config['margin_buttom']);
        $pdf->setImageScale($config['image_scale']);

        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->setSpacesRE('/[^\S\xa0]/'); // Fixing unicode/PCRE-mess #17547

        foreach ($job->getPages() as $page) {
            $page = ' ' . $page;
            $pdf->AddPage();
            $pdf->writeHTML($page, true, false, true, false, '');
        }
        $result = $pdf->Output(basename($job->getFilename()), $job->getOutputMode()); // (I - Inline, D - Download, F - File)

        if (in_array($job->getOutputMode(), ['I', 'D'])) {
            exit();
        }
    }
}
