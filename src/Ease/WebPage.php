<?php
/**
 * Common webpage class
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2018 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Trida obecne html stranky.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class WebPage extends Page
{
    /**
     * Where to look for jquery script
     * @var string path or url 
     */
    public $jqueryJavaScript = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js';

    /**
     * Položky předávané do vkládaného objektu.
     *
     * @var type
     */
    public $raiseItems = ['SetupWebPage' => 'webPage'];

    /**
     * Pole Javasriptu k vykresleni.
     *
     * @var array
     */
    public $javaScripts = null;

    /**
     * Pole CSS k vykreslení.
     *
     * @var array
     */
    public $cascadeStyles = null;

    /**
     * Nadpis stránky.
     *
     * @var string
     */
    public $pageTitle = null;

    /**
     * head stránky.
     *
     * @var Html\HeadTag
     */
    public $head = null;

    /**
     * Objekt samotného těla stránky.
     *
     * @var Html\BodyTag
     */
    public $body = null;

    /**
     * Nepřipojovat se DB.
     *
     * @var string|bool
     */
    public $myTable = false;

    /**
     * Výchozí umístění javascriptů v Debianu.
     *
     * @var string
     */
    public $jsPrefix = '/javascript/';

    /**
     * Default CSS locaton on debian.
     *
     * @var string
     */
    public $cssPrefix = '/javascript/';

    /**
     * Content to place inside of body
     *
     * @param $toBody
     */
    public function __construct($pageTitle = null, $toBody = null)
    {
        Shared::webPage($this);
        if (!is_null($pageTitle)) {
            $this->pageTitle = $pageTitle;
        }
        parent::__construct();

        $this->pageParts['doctype'] = '<!DOCTYPE html>';
        parent::addItem(new Html\HtmlTag());
        $this->pageParts['html']->addItem(new Html\HeadTag());
        $this->pageParts['html']->addItem(new Html\BodyTag($toBody));
        $this->head                 = &$this->pageParts['html']->pageParts['head'];
        $this->head->raise($this);

        $this->body = &$this->pageParts['html']->pageParts['body'];
        $this->body->raise($this);

        $this->javaScripts   = &$this->head->javaScripts;
        $this->cascadeStyles = &$this->head->cascadeStyles;
    }

    /**
     * Set ID for page body
     *
     * @return string
     */
    public function setTagID($tagID = null)
    {
        return $this->body->setTagID($tagID);
    }

    /**
     * Get ID for page body
     */
    public function getTagID()
    {
        $this->body->getTagID();
    }

    /**
     * Get body Contentets
     * 
     * @return mixed
     */
    public function getContents()
    {
        return $this->body->getContents();
    }

    /**
     * Add item into page body
     *
     * @param mixed  $item         vkládaná položka
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return Page poiner to object well included
     */
    public function &addItem($item, $pageItemName = null)
    {
        $added = $this->body->addItem($item, $pageItemName);

        return $added;
    }

    /**
     * Includuje Javascript do stránky.
     *
     * @param string $javaScriptFile soubor s javascriptem
     * @param string $position       končná pozice: '+','-','0','--',...
     *
     * @return string
     */
    public function includeJavaScript($javaScriptFile, $position = null)
    {
        return $this->addToScriptsStack('#'.$javaScriptFile, $position);
    }

    /**
     * Vloží javascript do stránky.
     *
     * @param string $javaScript      JS code
     * @param string $position        končná pozice: '+','-','0','--',...
     * @param bool   $inDocumentReady vložit do DocumentReady bloku ?
     *
     * @return string
     */
    public function addJavaScript($javaScript, $position = null,
                                  $inDocumentReady = true)
    {
        return $this->addToScriptsStack(($inDocumentReady ? '$' : '@').$javaScript,
                $position);
    }

    /**
     * Vloží javascript do zasobniku skriptu stránky.
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
                if (!$scriptFound && ($javaScripts[0] != $code)) {
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

                $backup                 = array_slice($javaScripts, $position);
                $javaScripts[$position] = $code;
                $nextFreeID             = $position + 1;
                foreach ($backup as $code) {
                    $javaScripts[$nextFreeID++] = $code;
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
     * Add another CSS definition to stack.
     *
     * @param string $css definice CSS pravidla
     *
     * @return bool
     */
    public function addCSS($css)
    {
        if (is_array($css)) {
            $css = key($css).'{'.current($css).'}';
        }
        $this->easeShared->cascadeStyles[md5($css)] = $css;

        return true;
    }

    /**
     * Vloží do stránky odkaz na CSS definici.
     *
     * @param string $cssFile  url CSS souboru
     * @param bool   $fwPrefix Přidat cestu frameworku ? (obvykle /Ease/)
     * @param string $media    screen|printer|braile a podobně
     *
     * @return int one
     */
    public function includeCss($cssFile, $fwPrefix = false, $media = 'screen')
    {
        if ($fwPrefix) {
            $this->easeShared->cascadeStyles[$this->cssPrefix.$cssFile] = $this->cssPrefix.$cssFile;
        } else {
            $this->easeShared->cascadeStyles[$cssFile] = $cssFile;
        }

        return 1;
    }

    /**
     * Vrací zprávy uživatele.
     *
     * @param string $what info|warning|error|success
     *
     * @return string
     */
    public function getStatusMessagesAsHtml($what = null)
    {
        if (!count($this->easeShared->statusMessages)) {
            return '';
        }
        $htmlFargment = '';

        $allMessages = [];
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
                $htmlFargment .= '<div class="MessageForUser" style="'.$this->logger->logStyles[$messageType].'" >'.$message.'</div>'."\n";
            } else {
                $htmlFargment .= '<div class="MessageForUser">'.$message.'</div>'."\n";
            }
        }

        return $htmlFargment;
    }

    /**
     * Provede vykreslení obsahu objektu.
     */
    public function draw()
    {
        $this->finalizeRegistred();
        $this->drawAllContents();
    }

    /**
     * Provede finalizaci všech registrovaných objektů.
     */
    public function finalizeRegistred()
    {
        $shared = \Ease\Shared::instanced();
        do {
            foreach ($shared->allItems as $PartID => $part) {
                if (is_object($part) && method_exists($part, 'finalize')) {
                    $part->finalize();
                }
                unset($shared->allItems[$PartID]);
            }
        } while (count($shared->allItems));
    }

    /**
     * Nastaví titul webové stánky.
     *
     * @param string $pageTitle titulek
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * Vrací počet vložených položek.
     * Obtain number of enclosed items in current page body or given object.
     *
     * @param Container $object hodnota nebo EaseObjekt s polem ->pageParts
     *
     * @return int nuber of parts enclosed
     */
    public function getItemsCount($object = null)
    {
        if (is_null($object)) {
            $object = &$this->body;
        }

        return parent::getItemsCount($object);
    }

    /**
     * Je element prázdný ?
     * Is body element empty ?
     *
     * @param Html\BodyTag $element Ease Html Element
     *
     * @return bool emptyness
     */
    public function isEmpty($element = null)
    {
        if (is_null($element)) {
            $element = &$this->body;
        }
        return parent::isEmpty($element);
    }

    /**
     * Vyprázní obsah webstránky
     * Empty webpage contents
     */
    public function emptyContents()
    {
        $this->body->emptyContents();
    }
}
