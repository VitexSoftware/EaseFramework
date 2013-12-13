<?php

/**
 * Integrace jQuery
 *
 * @category   Jquery
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 * @license    http://opensource.org/licenses/gpl-license.php GNU
 * @link       http://docs.jquery.com/Main_Page
 */
require_once 'EasePage.php';

/**
 * jQuery common class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseJQueryPart extends EasePage
{

    /**
     * Partname/Tag ID
     * @var string
     */
    public $PartName = 'JQ';

    /**
     * Use minimized version of scripts ?
     * @var boolean
     */
    public static $UseMinimizedJS = false;

    /**
     * Array of Part properties
     * @var array
     */
    public $PartProperties = array();

    public function __construct()
    {
        parent::__construct();
        EaseJQueryPart::jQueryze();
    }

    /**
     * Set part name - mainly div id
     *
     * @param string $PartName jméno vložené části
     */
    public function setPartName($PartName)
    {
        $this->PartName = $PartName;
    }

    /**
     * Returns OnDocumentReady() JS code
     *
     * @return string
     */
    public function onDocumentReady()
    {
        return '';
    }

    /**
     * Add Js/Css into page
     */
    public function finalize()
    {
        $JavaScript = $this->onDocumentReady();
        if ($JavaScript) {
            EaseShared::webPage()->addJavaScript($JavaScript, null, true);
        }
    }

    /**
     * Opatří objekt vším potřebným pro funkci jQuery
     */
    public static function jQueryze()
    {
        EaseShared::webPage()->includeJavaScript('jquery/jquery.js', 0, true);
    }

    /**
     * Nastaví paramatry tagu
     *
     * @param mixed $PartProperties vlastnosti jQuery widgetu
     */
    public function setPartProperties($PartProperties)
    {
        if (is_array($PartProperties)) {
            if (is_array($this->PartProperties)) {
                $this->PartProperties = array_merge($this->PartProperties, $PartProperties);
            } else {
                $this->PartProperties = $PartProperties;
            }
        } else {
            $propBuff = $PartProperties;
            $this->PartProperties = ' ' . $propBuff;
        }
    }

    /**
     * Vyrendruje aktuální parametry části jako parametry pro jQuery
     *
     * @param array|string $PartProperties pole vlastností
     *
     * @return string
     */
    public function getPartPropertiesToString($PartProperties = null)
    {
        if (!$PartProperties) {
            $PartProperties = $this->PartProperties;
        }

        return self::partPropertiesToString($PartProperties);
    }

    public static function is_assoc($arr)
    {
        return (array_values($arr) !== $arr);
    }

    /**
     * vyrendruje pole parametrů jako řetězec v syntaxi javascriptu
     *
     * @param array|string $PartProperties vlastnosti jQuery widgetu
     *
     * @return string
     */
    public static function partPropertiesToString($PartProperties)
    {
        if (is_array($PartProperties)) {
            $PartPropertiesString = '';
            $PartsArray = array();
            foreach ($PartProperties as $PartPropertyName => $PartPropertyValue) {
                if (!is_null($PartPropertyName)) {
                    if (is_numeric($PartPropertyName)) {
                        if (!strstr($PartPropertiesString, ' ' . $PartPropertyValue . ' ')) {
                            $PartsArray[] = ' ' . $PartPropertyValue . ' ';
                        }
                    } else {
                        if (is_array($PartPropertyValue)) {

                            if (self::is_assoc($PartPropertyValue)) {
                                if ($PartPropertyName) {
                                    $PartsArray[] = $PartPropertyName . ': { ' . self::partPropertiesToString($PartPropertyValue) . ' } ';
                                } else {
                                    $PartsArray[] = self::partPropertiesToString($PartPropertyValue);
                                }
                            } else {
                                foreach ($PartPropertyValue as $Key => $Value) {
                                    if (is_string($Value)) {
                                        $PartPropertyValue[$Key] = '"' . $Value . '"';
                                    }
                                }
                                $PartsArray[] = $PartPropertyName . ': [' . implode(',', $PartPropertyValue) . '] ';
                            }

//                            $PartsArray[] = $PartPropertyName . ': [' . implode(',', $PartPropertyValue) . '] ';
                        } elseif (is_int($PartPropertyValue)) {
                            $PartsArray[] = $PartPropertyName . ': ' . $PartPropertyValue . ' ';
                        } else {
                            if (!is_null($PartPropertyValue) && ( strlen($PartPropertyValue) || $PartPropertyValue === false )) {
                                if (!@substr_compare($PartPropertyValue, 'function', 0, 8) || ($PartPropertyValue[0] == '{') || ($PartPropertyValue === true)) {
                                    if ($PartPropertyValue === true) {
                                        $PartPropertyValue = 'true';
                                    }
                                    if ($PartPropertyValue === false) {
                                        $PartPropertyValue = 'false';
                                    }
                                    $PartsArray[] = $PartPropertyName . ': ' . $PartPropertyValue . ' ';
                                } else {
                                    $PartsArray[] = $PartPropertyName . ': "' . $PartPropertyValue . '" ';
                                }
                            }
                        }
                    }
                } else {
                    $PartsArray[] = $PartPropertyValue;
                }
            }
            $PartPropertiesString = implode(",\n", $PartsArray);

            return $PartPropertiesString;
        } else {
            return $PartProperties;
        }
    }

}

/**
 * jQuery UI common class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseJQueryUIPart extends EaseJQueryPart
{

    public function __construct()
    {
        parent::__construct();
        EaseJQueryUIPart::jQueryze();
    }

    /**
     * Opatří objekt vším potřebným pro funkci jQueryUI
     *
     * @param EasePage|mixed $EaseObject objekt k opatření jQuery závislostmi
     */
    public static function jQueryze()
    {
        parent::jQueryze();
        $WebPage = EaseShared::webPage();
        $WebPage->includeJavaScript('jquery-ui/jquery-ui.js', 1, true);
        $jQueryUISkin = EaseShared::instanced()->getConfigValue('jQueryUISkin');
        if ($jQueryUISkin) {
            $WebPage->includeCss('jquery-ui-themes/' . self::getSkinName() . '/jquery-ui.css', true);
        } else {
            $WebPage->includeCss('jquery-ui/css/smoothness/jquery-ui.css', true);
        }
    }

    /**
     * Vrací název aktuálně používaného jQueryUI skinu
     * @return type
     */
    public static function getSkinName()
    {
        $jQueryUISkin = EaseShared::instanced()->getConfigValue('jQueryUISkin');
        if ($jQueryUISkin) {
            return $jQueryUISkin;
        } else {
            if (isset(EaseShared::webPage()->jQueryUISkin)) {
                return EaseShared::webPage()->jQueryUISkin;
            }
        }

        return null;
    }

}
