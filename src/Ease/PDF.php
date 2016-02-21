<?php

/**
 * TCPDF compatibility layer
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@vitexsoftware.cz (G)
 */

namespace Ease;

require_once 'tcpdf/tcpdf.php';

/**
 * Umožnuje generovat PDF dokumenty z EaseFrameWorku
 *
 * @author Vitex <vitex@hippy.cz>
 */
class PDF extends \TCPDF {

    /**
     * Objekt pro vykreslování
     * @var EasePage object
     */
    public $OPage = null;

    /**
     * Pole vkládaného obsahu
     * @var array
     */
    public $pageParts = null;

    /**
     * Pole předávaných vlastností
     * @var array
     */
    public $raiseItems = ['SetUpUser' => 'User', 'webPage'];

    /**
     * Soubor do kterého je rendrováno výsledné PDF voláním WriteToFile
     * @var string
     */
    public $OutFile = null;

    /**
     * Semafor finalizace
     */
    public $Finalized = false;

    /**
     * PDF objekt
     *
     * @param string $Format formát pdf strany
     */
    public function __construct($Format = PDF_PAGE_FORMAT) {
        $this->OPage = new EasePage();
        $this->pageParts = & $this->OPage->pageParts;
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, $Format, true, 'UTF-8', false);
        $this->setup();
    }

    public function setup() {
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Nicola Asuni');
        $this->SetTitle('TCPDF Example 001');
        $this->SetSubject('TCPDF Tutorial');
        $this->SetKeywords('TCPDF, PDF, example, test, guide');
    }

    public function setFont($family, $style = '', $size = 0, $fontfile = '') {
        $family = str_ireplace('arial', 'dejavusans', $family);
        parent::SetFont($family, $style, $size, $fontfile);
    }

    public function error($Message, $data = null) {
        $this->OPage->error($Message, $data);
        parent::Error($Message);
    }

    function &addItem($Item) {
        $AddedItem = $this->OPage->addItem($Item);

        return $AddedItem;
    }

    public function draw() {
        $this->Output($PDFFile);
    }

    public function finalize() {
        if ($this->Finalized) {
            return null;
        }
        $this->AddPage();
        $this->writeHTML($this->OPage->getRendered());
        $this->Finalized = true;

        return true;
    }

    public function sendToBrowser($OutFile = null) {
        if (!$OutFile) {
            $OutFile = $this->OutFile;
        }
        $this->finalize();

        return $this->Output(basename($OutFile), 'I');
    }

    public function writeToFile($OutFile) {
        if (!$OutFile) {
            $OutFile = $this->OutFile;
        }
        $this->finalize();
        $this->Output($OutFile, 'F');
        if (is_file($OutFile)) {
            return $OutFile;
        } else {
            return null;
        }
    }

}
