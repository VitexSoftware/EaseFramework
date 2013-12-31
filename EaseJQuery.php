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
    public $partName = 'JQ';

    /**
     * Use minimized version of scripts ?
     * @var boolean
     */
    public static $UseMinimizedJS = false;

    /**
     * Array of Part properties
     * @var array
     */
    public $partProperties = array();

    public function __construct()
    {
        parent::__construct();
        EaseJQueryPart::jQueryze();
    }

    /**
     * Set part name - mainly div id
     *
     * @param string $partName jméno vložené části
     */
    public function setPartName($partName)
    {
        $this->partName = $partName;
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
        $javaScript = $this->onDocumentReady();
        if ($javaScript) {
            EaseShared::webPage()->addJavaScript($javaScript, null, true);
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
     * @param mixed $partProperties vlastnosti jQuery widgetu
     */
    public function setPartProperties($partProperties)
    {
        if (is_array($partProperties)) {
            if (is_array($this->partProperties)) {
                $this->partProperties = array_merge($this->partProperties, $partProperties);
            } else {
                $this->partProperties = $partProperties;
            }
        } else {
            $propBuff = $partProperties;
            $this->partProperties = ' ' . $propBuff;
        }
    }

    /**
     * Vyrendruje aktuální parametry části jako parametry pro jQuery
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

    public static function is_assoc($arr)
    {
        return (array_values($arr) !== $arr);
    }

    /**
     * vyrendruje pole parametrů jako řetězec v syntaxi javascriptu
     *
     * @param array|string $partProperties vlastnosti jQuery widgetu
     *
     * @return string
     */
    public static function partPropertiesToString($partProperties)
    {
        if (is_array($partProperties)) {
            $partPropertiesString = '';
            $partsArray = array();
            foreach ($partProperties as $partPropertyName => $partPropertyValue) {
                if (!is_null($partPropertyName)) {
                    if (is_numeric($partPropertyName)) {
                        if (!strstr($partPropertiesString, ' ' . $partPropertyValue . ' ')) {
                            $partsArray[] = ' ' . $partPropertyValue . ' ';
                        }
                    } else {
                        if (is_array($partPropertyValue)) {

                            if (self::is_assoc($partPropertyValue)) {
                                if ($partPropertyName) {
                                    $partsArray[] = $partPropertyName . ': { ' . self::partPropertiesToString($partPropertyValue) . ' } ';
                                } else {
                                    $partsArray[] = self::partPropertiesToString($partPropertyValue);
                                }
                            } else {
                                foreach ($partPropertyValue as $key => $value) {
                                    if (is_string($value)) {
                                        $partPropertyValue[$key] = '"' . $value . '"';
                                    }
                                }
                                $partsArray[] = $partPropertyName . ': [' . implode(',', $partPropertyValue) . '] ';
                            }

//                            $PartsArray[] = $PartPropertyName . ': [' . implode(',', $PartPropertyValue) . '] ';
                        } elseif (is_int($partPropertyValue)) {
                            $partsArray[] = $partPropertyName . ': ' . $partPropertyValue . ' ';
                        } else {
                            if (!is_null($partPropertyValue) && ( strlen($partPropertyValue) || $partPropertyValue === false )) {
                                if (!@substr_compare($partPropertyValue, 'function', 0, 8) || ($partPropertyValue[0] == '{') || ($partPropertyValue === true)) {
                                    if ($partPropertyValue === true) {
                                        $partPropertyValue = 'true';
                                    }
                                    if ($partPropertyValue === false) {
                                        $partPropertyValue = 'false';
                                    }
                                    $partsArray[] = $partPropertyName . ': ' . $partPropertyValue . ' ';
                                } else {
                                    $partsArray[] = $partPropertyName . ': "' . $partPropertyValue . '" ';
                                }
                            }
                        }
                    }
                } else {
                    $partsArray[] = $partPropertyValue;
                }
            }
            $partPropertiesString = implode(",\n", $partsArray);

            return $partPropertiesString;
        } else {
            return $partProperties;
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
        $webPage = EaseShared::webPage();
        $webPage->includeJavaScript('jquery-ui/jquery-ui.js', 1, true);
        $jQueryUISkin = EaseShared::instanced()->getConfigValue('jQueryUISkin');
        if ($jQueryUISkin) {
            $webPage->includeCss('jquery-ui-themes/' . self::getSkinName() . '/jquery-ui.css', true);
        } else {
            $webPage->includeCss('jquery-ui/css/smoothness/jquery-ui.css', true);
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
