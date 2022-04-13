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

class ilTCPDFGenerator
{
    public static function generatePDF(ilPDFGenerationJob $job) : void
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator($job->getCreator());
        $pdf->SetAuthor($job->getAuthor());
        $pdf->SetTitle($job->getTitle());
        $pdf->SetSubject($job->getSubject());
        $pdf->SetKeywords($job->getKeywords());

        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins($job->getMarginLeft(), $job->getMarginTop(), $job->getMarginRight());
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak($job->getAutoPageBreak(), $job->getMarginBottom());
        $pdf->setImageScale($job->getImageScale());
        $pdf->SetFont('dejavusans', '', 10);

        $pdf->setSpacesRE('/[^\S\xa0]/'); // Fixing unicode/PCRE-mess #17547

        foreach ($job->getPages() as $page) {
            $page = ' ' . $page;
            $pdf->AddPage();
            $pdf->writeHTML($page, true, false, true, false, '');
        }
        $pdf->Output($job->getFilename(), $job->getOutputMode()); // (I - Inline, D - Download, F - File)

        if (in_array($job->getOutputMode(), ['I', 'D'])) {
            exit();
        }
    }
}
