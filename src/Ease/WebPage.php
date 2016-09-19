<?php
/**
 * Třídy pro vykreslení obecne stránky shopu.
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
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
     * Základní objekt pro stránku shopu.
     *
     * @param User|Anonym $userObject objekt uživatele
     */
    public function __construct($pageTitle = null, &$userObject = null)
    {
        Shared::webPage($this);
        if (!is_null($pageTitle)) {
            $this->pageTitle = $pageTitle;
        }
        parent::__construct($userObject);

        $this->pageParts['doctype'] = '<!DOCTYPE html>';
        parent::addItem(new Html\HtmlTag());
        $this->pageParts['html']->setupWebPage($this);
        $this->pageParts['html']->addItem(new Html\HeadTag());
        $this->pageParts['html']->addItem(new Html\BodyTag());
        $this->head                 = &$this->pageParts['html']->pageParts['head'];
        $this->head->raise($this);

        $this->body = &$this->pageParts['html']->pageParts['body'];
        $this->body->raise($this);

        $this->javaScripts   = &$this->head->javaScripts;
        $this->cascadeStyles = &$this->head->cascadeStyles;
    }

    /**
     * Nastaví ID stránky.
     *
     * @return string
     */
    public function setTagID($tagID = null)
    {
        return $this->body->setTagID($tagID);
    }

    /**
     * Přidá položku do těla stránky.
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
     * @param bool   $fwPrefix       Add Framework prefix ?
     *
     * @return string
     */
    public function includeJavaScript($javaScriptFile, $position = null,
                                      $fwPrefix = false)
    {
        if ($fwPrefix) {
            return $this->addToScriptsStack(
                    '#'.$this->jsPrefix.$javaScriptFile, $position
            );
        } else {
            return $this->addToScriptsStack('#'.$javaScriptFile, $position);
        }
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
        if ($inDocumentReady) {
            return $this->addToScriptsStack('$'.$javaScript, $position);
        }

        return $this->addToScriptsStack('@'.$javaScript, $position);
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

                $Backup                 = array_slice($javaScripts, $position);
                $javaScripts[$position] = $code;
                $NextFreeID             = $position + 1;
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
     * @return bool
     */
    public function includeCss($cssFile, $fwPrefix = false, $media = 'screen')
    {
        if ($fwPrefix) {
            $this->easeShared->cascadeStyles[$this->cssPrefix.$cssFile] = $this->cssPrefix.$cssFile;
        } else {
            $this->easeShared->cascadeStyles[$cssFile] = $cssFile;
        }

        return true;
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
     * Obtain number of enclosed items in current page body or given object
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
}
