<?php

namespace Ease\JQuery;

/**
 * jQuery common class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class Part extends \Ease\Page
{

    /**
     * Partname/Tag ID
     *
     * @var string
     */
    public $partName = 'JQ';

    /**
     * Use minimized version of scripts ?
     *
     * @var boolean
     */
    public static $useMinimizedJS = false;

    /**
     * Array of Part properties
     *
     * @var array
     */
    public $partProperties = [];

    public function __construct()
    {
        parent::__construct();
        Part::jQueryze();
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
            \Ease\Shared::webPage()->addJavaScript($javaScript, null, true);
        }
    }

    /**
     * Opatří objekt vším potřebným pro funkci jQuery
     */
    public static function jQueryze()
    {
        \Ease\Shared::webPage()->includeJavaScript('jquery/jquery.js', 0, true);
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
        return array_values($arr) !== $arr;
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
            $partsArray = [];
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
                                if (is_array($partPropertyValue)) {
                                    foreach ($partPropertyValue as $pId => $piece) {
                                        if (is_array($piece)) {
                                            $partPropertyValue[$pId] = ' { ' . self::partPropertiesToString($piece) . ' } ';
                                        }
                                    }
                                    $partsArray[] = $partPropertyName . ': [' . implode(',', $partPropertyValue) . '] ';
                                } else {
                                    $partsArray[] = $partPropertyName . ':' . $partPropertyValue;
                                }
                            }
                        } elseif (is_int($partPropertyValue)) {
                            $partsArray[] = $partPropertyName . ': ' . $partPropertyValue . ' ';
                        } else {
                            if (!is_null($partPropertyValue) && (strlen($partPropertyValue) || $partPropertyValue === false)) {
                                if (!@substr_compare($partPropertyValue, 'function', 0, 8) || $partPropertyValue[0] == '{' || $partPropertyValue === true) {
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
            $partPropertiesString = implode(
                ',
',
                $partsArray
            );
            return $partPropertiesString;
        } else {
            return $partProperties;
        }
    }
}
