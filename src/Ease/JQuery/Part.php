<?php

namespace Ease\JQuery;

/**
 * jQuery common class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class Part extends \Ease\Page
{
    /**
     * Partname/Tag ID.
     *
     * @var string
     */
    public $partName = 'JQ';

    /**
     * Use minimized version of scripts ?
     *
     * @var bool
     */
    public static $useMinimizedJS = false;

    /**
     * Array of Part properties.
     *
     * @var array
     */
    public $partProperties = [];

    public function __construct()
    {
        parent::__construct();
        self::jQueryze();
    }

    /**
     * Set part name - mainly div id.
     *
     * @param string $partName jméno vložené části
     */
    public function setPartName($partName)
    {
        $this->partName = $partName;
    }

    /**
     * Returns OnDocumentReady() JS code.
     *
     * @return string
     */
    public function onDocumentReady()
    {
        return '';
    }

    /**
     * Add Js/Css into page.
     */
    public function finalize()
    {
        $javaScript = $this->onDocumentReady();
        if ($javaScript) {
            \Ease\Shared::webPage()->addJavaScript($javaScript, null, true);
        }
    }

    /**
     * Opatří objekt vším potřebným pro funkci jQuery.
     */
    public static function jQueryze()
    {
        \Ease\Shared::webPage()->includeJavaScript('jquery/jquery.js', 0, true);
    }

    /**
     * Nastaví paramatry tagu.
     *
     * @param mixed $partProperties vlastnosti jQuery widgetu
     */
    public function setPartProperties($partProperties)
    {
        if (is_array($partProperties)) {
            if (is_array($this->partProperties)) {
                $this->partProperties = array_merge($this->partProperties,
                    $partProperties);
            } else {
                $this->partProperties = $partProperties;
            }
        } else {
            $propBuff             = $partProperties;
            $this->partProperties = ' '.$propBuff;
        }
    }

    /**
     * Vyrendruje aktuální parametry části jako parametry pro jQuery.
     *
     * @param array|string $partProperties pole vlastností
     *
     * @return string
     */
    public function getPartPropertiesToString($partProperties = null)
    {
        if (!$partProperties) {
            $partProperties = $this->partProperties;
        }

        return self::partPropertiesToString($partProperties);
    }

    /**
     * vyrendruje pole parametrů jako řetězec v syntaxi javascriptu.
     *
     * @param array|string $partProperties vlastnosti jQuery widgetu
     *
     * @deprecated since version 1.1.2
     * @return string
     */
    public static function partPropertiesToString($partProperties)
    {
        return json_encode($partProperties);
    }

}
