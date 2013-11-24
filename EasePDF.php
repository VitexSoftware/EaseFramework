<?php

/**
 * TCPDF compatibility layer
 * 
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@vitexsoftware.cz (G)
 */
require_once 'tcpdf/tcpdf.php';
require_once 'EasePage.php';

/**
 * Umožnuje generovat PDF dokumenty z EaseFrameWorku
 * 
 * @author Vitex <vitex@hippy.cz>
 */
class EasePDF extends TCPDF
{

    /**
     * Objekt pro vykreslování
     * @var EasePage object
     */
    public $OPage = null;

    /**
     * Pole vkládaného obsahu
     * @var array
     */
    public $PageParts = null;

    /**
     * Pole předávaných vlastností
     * @var array 
     */
    public $RaiseItems = array('SetUpUser' => 'User', 'WebPage');

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
    function __construct($Format = PDF_PAGE_FORMAT)
    {
        $this->OPage = new EasePage();
        $this->PageParts = & $this->OPage->PageParts;
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, $Format, true, 'UTF-8', false);
        $this->setup();
    }

    function setup()
    {
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Nicola Asuni');
        $this->SetTitle('TCPDF Example 001');
        $this->SetSubject('TCPDF Tutorial');
        $this->SetKeywords('TCPDF, PDF, example, test, guide');
    }

    function setFont($family, $style = '', $size = 0, $fontfile = '')
    {
        $family = str_ireplace('arial', 'dejavusans', $family);
        parent::SetFont($family, $style, $size, $fontfile);
    }

    function error($Message, $Data = null)
    {
        $this->OPage->error($Message, $Data);
        parent::Error($Message);
    }

    function &addItem($Item)
    {
        $AddedItem = $this->OPage->addItem($Item);
        return $AddedItem;
    }

    function draw()
    {
        $this->Output($PDFFile);
    }

    function finalize()
    {
        if ($this->Finalized) {
            return null;
        }
        $this->AddPage();
        $this->writeHTML($this->OPage->getRendered());
        $this->Finalized = true;
        return true;
    }

    function sendToBrowser($OutFile = null)
    {
        if (!$OutFile) {
            $OutFile = $this->OutFile;
        }
        $this->finalize();
        return $this->Output(basename($OutFile), 'I');
    }

    function writeToFile($OutFile)
    {
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

?>
