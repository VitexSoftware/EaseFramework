<?php

/**
 * Třídy pro vykreslení obecne stránky shopu.
 * 
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G) 
 */
require_once 'EaseHtml.php';

/**
 * Trida obecne html stranky
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseWebPage extends EasePage
{

    /**
     * Položky předávané do vkládaného objektu
     * @var type 
     */
    public $RaiseItems = array('SetupWebPage' => 'WebPage');

    /**
     * Pole Javasriptu k vykresleni
     * @var array 
     */
    public $JavaScripts = null;

    /**
     * Pole CSS k vykreslení
     * @var array 
     */
    public $CascadeStyles = null;

    /**
     * Nadpis stránky
     * @var string
     */
    public $PageTitle = null;

    /**
     * Head stránky
     * @var EaseHtmlHeadTag
     */
    public $Head = null;

    /**
     * Objekt samotného těla stránky
     * @var EaseHtmlBodyTag
     */
    public $Body = null;

    /**
     * Nepřipojovat se DB
     * @var string|bool
     */
    public $MyTable = false;

    /**
     * Výchozí umístění javascriptů
     * @var string 
     */
    public $JSPrefix = '/javascript/';

    /**
     * Default CSS locaton
     * @var string 
     */
    public $CssPrefix = '/javascript/';

    /**
     * Výchozí Skin stránky. Viz: /usr/share/javascript/jquery-ui-themes
     * @var string     
     */
    public $jQueryUISkin = null;

    /**
     * Základní objekt pro stránku shopu
     * 
     * @param EaseUser|EaseAnonym $UserObject objekt uživatele
     */
    function __construct($PageTitle = NULL ,  & $UserObject = null)
    {
        EaseShared::webPage($this);
        if (!is_null($PageTitle)){
            $this->PageTitle = $PageTitle;
        }
        parent::__construct($UserObject);
        $this->EaseShared->setConfigValue('jQueryUISkin', $this->jQueryUISkin);
        $this->PageParts['doctype'] = '<!DOCTYPE html>';
        parent::addItem(new EaseHtmlHtmlTag());
        $this->PageParts['html']->setupWebPage($this);
        $this->PageParts['html']->addItem(new EaseHtmlHeadTag());
        $this->PageParts['html']->addItem(new EaseHtmlBodyTag());
        $this->Head = & $this->PageParts['html']->PageParts['head'];
        $this->Head->raise($this);

        $this->Body = & $this->PageParts['html']->PageParts['body'];
        $this->Body->raise($this);

        $this->JavaScripts = & $this->Head->JavaScripts;
        $this->CascadeStyles = & $this->Head->CascadeStyles;
    }

    /**
     * Vrací css skin použitý frameworkem pro jQueryUI 
     * 
     * @return string 
     */
    function getjQueryUISkin()
    {
        return $this->jQueryUISkin;
    }

    /**
     * Přidá položku do těla stránky
     * 
     * @param mixed  $item         vkládaná položka
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     * 
     * @return EasePage poiner to object well included 
     */
    function & addItem($item,$pageItemName = null)
    {
        return $this->Body->addItem($item,$pageItemName);
    }

    /**
     * Includuje Javascript do stránky
     * 
     * @param string  $JavaScriptFile soubor s javascriptem
     * @param string  $Position       končná pozice: '+','-','0','--',...
     * @param boolean $FWPrefix       Add Framework prefix ?
     * 
     * @return string
     */
    function includeJavaScript($JavaScriptFile, $Position = null, $FWPrefix = false)
    {
        if ($FWPrefix) {
            return $this->addToScriptsStack('#' . $this->JSPrefix .
                            $JavaScriptFile, $Position
            );
        } else {
            return $this->addToScriptsStack('#' . $JavaScriptFile, $Position);
        }
    }

    /**
     * Vloží javascript do stránky
     * 
     * @param string  $JavaScript      JS code
     * @param string  $Position        končná pozice: '+','-','0','--',...
     * @param boolean $inDocumentReady vložit do DocumentReady bloku ?
     * 
     * @return string 
     */
    function addJavaScript($JavaScript, $Position = null, $inDocumentReady = false)
    {
        if ($inDocumentReady) {
            return $this->addToScriptsStack('$' . $JavaScript, $Position);
        }
        return $this->addToScriptsStack('@' . $JavaScript, $Position);
    }

    /**
     * Vloží javascript do zasobniku skriptu stránky
     * 
     * @param string $Code     JS code
     * @param string $Position končná pozice: '+','-','0','--',...
     * 
     * @return int
     */
    function addToScriptsStack($Code, $Position = null)
    {
        $JavaScripts = &$this->EaseShared->JavaScripts;
        if (is_null($Position)) {
            if (is_array($JavaScripts)) {
                $ScriptFound = array_search($Code, $JavaScripts);
                if (!$ScriptFound && ($JavaScripts[0]!=$Code)) {
                    $JavaScripts[] = $Code;
                    return key($JavaScripts);
                } else {
                    return $ScriptFound;
                }
            } else {
                $JavaScripts[] = $Code;
                return 0;
            }
        } else { //Pozice urcena
            if (isset($JavaScripts[$Position])) { //Uz je obsazeno
                if ($JavaScripts[$Position] == $Code) {
                    return $Position;
                }

                $ScriptFound = array_search($Code, $JavaScripts);
                if ($ScriptFound) {
                    unset($JavaScripts[$ScriptFound]);
                }

                $Backup = array_slice($JavaScripts, $Position);
                $JavaScripts[$Position] = $Code;
                $NextFreeID = $Position + 1;
                foreach ($Backup as $Code) {
                    $JavaScripts[$NextFreeID++] = $Code;
                }
                return $Position;
            } else { //Jeste je pozice volna
                $JavaScripts[] = $Code;
                return key($JavaScripts);
            }
        }
        return $Position;
    }

    /**
     * Add another CSS definition to stack
     * 
     * @param string $Css definice CSS pravidla
     * 
     * @return boolean 
     */
    function addCSS($Css)
    {
        $this->EaseShared->CascadeStyles[md5($Css)] = $Css;
        return true;
    }

    /**
     * Vloží do stránky odkaz na CSS definici
     * 
     * @param string  $CssFile  url CSS souboru
     * @param boolean $FWPrefix Přidat cestu frameworku ? (obvykle /Ease/)
     * @param string  $media    screen|printer|braile a podobně
     * 
     * @return boolean
     */
    function includeCss($CssFile, $FWPrefix = false, $media = 'screen')
    {
        if ($FWPrefix) {
            $this->EaseShared->CascadeStyles[$this->CssPrefix . $CssFile] = $this->CssPrefix . $CssFile;
        } else {
            $this->EaseShared->CascadeStyles[$CssFile] = $CssFile;
        }
        return true;
    }

    /**
     * Vrací zprávy uživatele
     * 
     * @param string $What info|warning|error|success
     * 
     * @return string 
     */
    function getStatusMessagesAsHtml($What = null)
    {
        /**
         * Session Singleton Problem hack 
         */
        //$this->EaseShared->takeStatusMessages(EaseShared::user()->getStatusMessages(true));        

        if (!count($this->EaseShared->StatusMessages)) {
            return '';
        }
        $HtmlFargment = '';

        $AllMessages = array();
        foreach ($this->EaseShared->StatusMessages as $Quee => $Messages) {
            foreach ($Messages as $MesgID => $Message) {
                $AllMessages[$MesgID][$Quee] = $Message;
            }
        }
        ksort($AllMessages);
        foreach ($AllMessages as $Message) {
            $MessageType = key($Message);

            if (is_array($What)) {
                if (!in_array($MessageType, $What)) {
                    continue;
                }
            }

            $Message = reset($Message);

            if (is_object($this->Logger)) {
                if (!isset($this->Logger->LogStyles[$MessageType])) {
                    $MessageType = 'notice';
                }
                $HtmlFargment .= '<div class="MessageForUser" style="' . $this->Logger->LogStyles[$MessageType] . '" >' . $Message . '</div>' . "\n";
            } else {
                $HtmlFargment .= '<div class="MessageForUser">' . $Message . '</div>' . "\n";
            }
        }
        return $HtmlFargment;
    }

    /**
     * Nastavi skin
     * 
     * @param string $SkinName název skinu
     */
    function setSkin($SkinName)
    {
        $this->SkinName = $SkinName;
    }

    /**
     * Provede vykreslení obsahu objektu
     */
    function draw()
    {
        $this->finalizeRegistred();
        $this->drawAllContents();
    }

    /**
     * Provede finalizaci všech registrovaných objektů
     */
    public function finalizeRegistred()
    {
        do {
            foreach ($this->EaseShared->AllItems as $PartID => $Part) {
                if (is_object($Part) && method_exists($Part, 'finalize')) {
                    $Part->finalize();
                }
                unset($this->EaseShared->AllItems[$PartID]);
            }
        } while (count($this->EaseShared->AllItems));
    }

    /**
     * Nastaví titul webové stánky
     * 
     * @param string $PageTitle titulek
     */
    public function setPageTitle($PageTitle)
    {
        $this->PageTitle = $PageTitle;
    }

}

?>
