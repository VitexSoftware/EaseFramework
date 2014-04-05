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
     * @param string  $javaScript      JS code
     * @param string  $position        končná pozice: '+','-','0','--',...
     * @param boolean $inDocumentReady vložit do DocumentReady bloku ?
     *
     * @return string
     */
    public function addJavaScript($javaScript, $position = null, $inDocumentReady = false)
    {
        if ($inDocumentReady) {
            return $this->addToScriptsStack('$' . $javaScript, $position);
        }

        return $this->addToScriptsStack('@' . $javaScript, $position);
    }

    /**
     * Vloží javascript do zasobniku skriptu stránky
     *
     * @param string $code     JS code
     * @param string $position končná pozice: '+','-','0','--',...
     *
     * @return int
     */
    public function addToScriptsStack($code, $position = null)
    {
        $javaScripts = &$this->easeShared->javaScripts;
        if (is_null($position)) {
            if (is_array($javaScripts)) {
                $scriptFound = array_search($code, $javaScripts);
                if (!$scriptFound && ($javaScripts[0]!=$code)) {
                    $javaScripts[] = $code;

                    return key($javaScripts);
                } else {
                    return $scriptFound;
                }
            } else {
                $javaScripts[] = $code;

                return 0;
            }
        } else { //Pozice urcena
            if (isset($javaScripts[$position])) { //Uz je obsazeno
                if ($javaScripts[$position] == $code) {
                    return $position;
                }

                $scriptFound = array_search($code, $javaScripts);
                if ($scriptFound) {
                    unset($javaScripts[$scriptFound]);
                }

                $Backup = array_slice($javaScripts, $position);
                $javaScripts[$position] = $code;
                $NextFreeID = $position + 1;
                foreach ($Backup as $code) {
                    $javaScripts[$NextFreeID++] = $code;
                }

                return $position;
            } else { //Jeste je pozice volna
                $javaScripts[] = $code;

                return key($javaScripts);
            }
        }

        return $position;
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
     * @param string  $cssFile  url CSS souboru
     * @param boolean $fwPrefix Přidat cestu frameworku ? (obvykle /Ease/)
     * @param string  $media    screen|printer|braile a podobně
     *
     * @return boolean
     */
    public function includeCss($cssFile, $fwPrefix = false, $media = 'screen')
    {
        if ($fwPrefix) {
            $this->easeShared->cascadeStyles[$this->cssPrefix . $cssFile] = $this->cssPrefix . $cssFile;
        } else {
            $this->easeShared->cascadeStyles[$cssFile] = $cssFile;
        }

        return true;
    }

    /**
     * Vrací zprávy uživatele
     *
     * @param string $what info|warning|error|success
     *
     * @return string
     */
    public function getStatusMessagesAsHtml($what = null)
    {
        /**
         * Session Singleton Problem hack
         */
        //$this->easeShared->takeStatusMessages(EaseShared::user()->getStatusMessages(true));

        if (!count($this->easeShared->statusMessages)) {
            return '';
        }
        $htmlFargment = '';

        $allMessages = array();
        foreach ($this->easeShared->statusMessages as $Quee => $messages) {
            foreach ($messages as $mesgID => $message) {
                $allMessages[$mesgID][$Quee] = $message;
            }
        }
        ksort($allMessages);
        foreach ($allMessages as $message) {
            $messageType = key($message);

            if (is_array($what)) {
                if (!in_array($messageType, $what)) {
                    continue;
                }
            }

            $message = reset($message);

            if (is_object($this->logger)) {
                if (!isset($this->logger->logStyles[$messageType])) {
                    $messageType = 'notice';
                }
                $htmlFargment .= '<div class="MessageForUser" style="' . $this->logger->logStyles[$messageType] . '" >' . $message . '</div>' . "\n";
            } else {
                $htmlFargment .= '<div class="MessageForUser">' . $message . '</div>' . "\n";
            }
        }

        return $htmlFargment;
    }

    /**
     * Nastavi skin
     * 
     * @deprecated since version 190
     * @param string $skinName název skinu
     */
    public function setSkin($skinName)
    {
        $this->SkinName = $skinName;
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
            foreach ($this->easeShared->AllItems as $PartID => $part) {
                if (is_object($part) && method_exists($part, 'finalize')) {
                    $part->finalize();
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
