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
    public $raiseItems = array('SetupWebPage' => 'webPage');

    /**
     * Pole Javasriptu k vykresleni
     * @var array
     */
    public $javaScripts = null;

    /**
     * Pole CSS k vykreslení
     * @var array
     */
    public $cascadeStyles = null;

    /**
     * Nadpis stránky
     * @var string
     */
    public $pageTitle = null;

    /**
     * head stránky
     * @var EaseHtmlHeadTag
     */
    public $head = null;

    /**
     * Objekt samotného těla stránky
     * @var EaseHtmlBodyTag
     */
    public $body = null;

    /**
     * Nepřipojovat se DB
     * @var string|bool
     */
    public $myTable = false;

    /**
     * Výchozí umístění javascriptů v Debianu
     * @var string
     */
    public $jsPrefix = '/javascript/';

    /**
     * Default CSS locaton on debian
     * @var string
     */
    public $cssPrefix = '/javascript/';

    /**
     * Výchozí Skin stránky. Viz: /usr/share/javascript/jquery-ui-themes
     * @var string
     */
    public $jQueryUISkin = null;

    /**
     * Základní objekt pro stránku shopu
     *
     * @param EaseUser|EaseAnonym $userObject objekt uživatele
     */
    public function __construct($pageTitle = NULL ,  & $userObject = null)
    {
        EaseShared::webPage($this);
        if (!is_null($pageTitle)) {
            $this->pageTitle = $pageTitle;
        }
        parent::__construct($userObject);
        $this->easeShared->setConfigValue('jQueryUISkin', $this->jQueryUISkin);
        $this->pageParts['doctype'] = '<!DOCTYPE html>';
        parent::addItem(new EaseHtmlHtmlTag());
        $this->pageParts['html']->setupWebPage($this);
        $this->pageParts['html']->addItem(new EaseHtmlHeadTag());
        $this->pageParts['html']->addItem(new EaseHtmlBodyTag());
        $this->head = & $this->pageParts['html']->pageParts['head'];
        $this->head->raise($this);

        $this->body = & $this->pageParts['html']->pageParts['body'];
        $this->body->raise($this);

        $this->javaScripts = & $this->head->javaScripts;
        $this->cascadeStyles = & $this->head->cascadeStyles;
    }

    /**
     * Vrací css skin použitý frameworkem pro jQueryUI
     *
     * @return string
     */
    public function getjQueryUISkin()
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
        return $this->body->addItem($item,$pageItemName);
    }

    /**
     * Includuje Javascript do stránky
     *
     * @param string  $javaScriptFile soubor s javascriptem
     * @param string  $position       končná pozice: '+','-','0','--',...
     * @param boolean $fwPrefix       Add Framework prefix ?
     *
     * @return string
     */
    public function includeJavaScript($javaScriptFile, $position = null, $fwPrefix = false)
    {
        if ($fwPrefix) {
            return $this->addToScriptsStack(
                    '#' . $this->jsPrefix . $javaScriptFile, $position
            );
        } else {
            return $this->addToScriptsStack('#' . $javaScriptFile, $position);
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
    public function addJavaScript($JavaScript, $Position = null, $inDocumentReady = false)
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
    public function addToScriptsStack($Code, $Position = null)
    {
        $javaScripts = &$this->easeShared->javaScripts;
        if (is_null($Position)) {
            if (is_array($javaScripts)) {
                $ScriptFound = array_search($Code, $javaScripts);
                if (!$ScriptFound && ($javaScripts[0]!=$Code)) {
                    $javaScripts[] = $Code;

                    return key($javaScripts);
                } else {
                    return $ScriptFound;
                }
            } else {
                $javaScripts[] = $Code;

                return 0;
            }
        } else { //Pozice urcena
            if (isset($javaScripts[$Position])) { //Uz je obsazeno
                if ($javaScripts[$Position] == $Code) {
                    return $Position;
                }

                $ScriptFound = array_search($Code, $javaScripts);
                if ($ScriptFound) {
                    unset($javaScripts[$ScriptFound]);
                }

                $Backup = array_slice($javaScripts, $Position);
                $javaScripts[$Position] = $Code;
                $NextFreeID = $Position + 1;
                foreach ($Backup as $Code) {
                    $javaScripts[$NextFreeID++] = $Code;
                }

                return $Position;
            } else { //Jeste je pozice volna
                $javaScripts[] = $Code;

                return key($javaScripts);
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
    public function addCSS($Css)
    {
        $this->easeShared->cascadeStyles[md5($Css)] = $Css;

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
    public function includeCss($CssFile, $FWPrefix = false, $media = 'screen')
    {
        if ($FWPrefix) {
            $this->easeShared->cascadeStyles[$this->cssPrefix . $CssFile] = $this->cssPrefix . $CssFile;
        } else {
            $this->easeShared->cascadeStyles[$CssFile] = $CssFile;
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
    public function getStatusMessagesAsHtml($What = null)
    {
        /**
         * Session Singleton Problem hack
         */
        //$this->easeShared->takeStatusMessages(EaseShared::user()->getStatusMessages(true));

        if (!count($this->easeShared->statusMessages)) {
            return '';
        }
        $HtmlFargment = '';

        $AllMessages = array();
        foreach ($this->easeShared->statusMessages as $Quee => $Messages) {
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

            if (is_object($this->logger)) {
                if (!isset($this->logger->logStyles[$MessageType])) {
                    $MessageType = 'notice';
                }
                $HtmlFargment .= '<div class="MessageForUser" style="' . $this->logger->logStyles[$MessageType] . '" >' . $Message . '</div>' . "\n";
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
    public function setSkin($SkinName)
    {
        $this->SkinName = $SkinName;
    }

    /**
     * Provede vykreslení obsahu objektu
     */
    public function draw()
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
            foreach ($this->easeShared->AllItems as $PartID => $Part) {
                if (is_object($Part) && method_exists($Part, 'finalize')) {
                    $Part->finalize();
                }
                unset($this->easeShared->AllItems[$PartID]);
            }
        } while (count($this->easeShared->AllItems));
    }

    /**
     * Nastaví titul webové stánky
     *
     * @param string $pageTitle titulek
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

}
