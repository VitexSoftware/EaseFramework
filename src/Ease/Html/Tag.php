<?php

namespace Ease\Html;

/**
 * Common HTML tag class
 *
 * @subpackage
 * @author     Vitex <vitex@hippy.cz>
 */
class Tag extends \Ease\Page
{

    /**
     * Jméno tagu - je použit i jako jméno objektu
     *
     * @var string
     */
    public $tagName = null;

    /**
     * Typ tagu - např A či STRONG
     *
     * @var string
     */
    public $tagType = null;

    /**
     * Pole vlastností tagu
     *
     * @var array
     */
    public $tagProperties = null;

    /**
     * pole ze kterého se rendruje obsah STYLE tagu
     *
     * @var array
     */
    public $cssProperties = null;

    /**
     * Nelogovat události HTML objektů
     *
     * @var string
     */
    public $logType = 'none';

    /**
     * Koncové lomítko pro xhtml
     *
     * @var string
     */
    public $trail = ' /';

    /**
     * Má si objekt automaticky plnit vlastnost name ?
     */
    public $setName = false;

    /**
     * Objekt pro vykreslení obecného nepárového html tagu
     *
     * @param string       $tagType       typ tagu
     * @param array|string $tagProperties parametry tagu
     * @param mixed        $content       vkládaný obsah
     */
    public function __construct($tagType = null, $tagProperties = null, $content = null)
    {
        if (is_null($tagType)) {
            $tagType = $this->tagType;
        } else {
            $this->setTagType($tagType);
        }
        parent::__construct();
        if ($tagProperties) {
            $this->setTagProperties($tagProperties);
        }
        if ($content) {
            $this->addItem($content);
        }
    }

    /**
     * Nastaví jméno objektu
     *
     * @param string $objectName jméno objektu
     *
     * @return string New object name
     */
    public function setObjectName($objectName = null)
    {
        if ($objectName) {
            return parent::setObjectName($objectName);
        }
        if ($this->tagName) {
            return parent::setObjectName(get_class($this) . '@' . $this->tagName);
        } else {
            if ($this->tagType) {
                return parent::setObjectName(get_class($this) . '@' . $this->tagType);
            } else {
                return parent::setObjectName();
            }
        }
    }

    /**
     * Nastaví jméno tagu
     *
     * @param string $tagName jméno tagu do vlastnosti NAME
     */
    public function setTagName($tagName)
    {
        $this->tagName = $tagName;
        if ($this->setName) {
            $this->tagProperties['name'] = $tagName;
        }
        $this->setObjectName();
    }

    /**
     * Returns name of tag
     *
     * @return string
     */
    public function getTagName()
    {
        if ($this->setName) {
            if (isset($this->tagProperties['name'])) {
                return $this->tagProperties['name'];
            } else {
                return null;
            }
        } else {
            return $this->tagName;
        }
    }

    /**
     * Nastaví typ tagu
     *
     * @param string $tagType typ tagu - např. img
     */
    public function setTagType($tagType)
    {
        $this->tagType = $tagType;
    }

    /**
     * Vrací typ tagu
     *
     * @return string typ tagu - např. img
     */
    public function getTagType()
    {
        return $this->tagType;
    }

    /**
     * Nastaví classu tagu
     *
     * @param string $className jméno css třídy
     */
    public function setTagClass($className)
    {
        $this->setTagProperties(['class' => $className]);
    }

    /**
     * Přidá classu tagu
     *
     * @param string $className jméno css třídy
     */
    public function addTagClass($className)
    {
        $this->setTagClass($this->getTagClass() . ' ' . $className);
    }

    /**
     * Vrací css classu tagu
     */
    public function getTagClass()
    {
        return $this->getTagProperty('class');
    }

    /**
     * Nastaví tagu zadane id, nebo vygenerované náhodné
     *
     * @param string $tagID #ID html tagu pro JavaScript a Css
     *
     * @return string nastavené ID
     */
    public function setTagID($tagID = null)
    {
        if (is_null($tagID)) {
            $this->setTagProperties(['id' => \Ease\Brick::randomString()]);
        } else {
            $this->setTagProperties(['id' => $tagID]);
        }
        return $this->getTagID();
    }

    /**
     * Vrací ID html tagu
     *
     * @return string
     */
    public function getTagID()
    {
        if (isset($this->tagProperties['id'])) {
            return $this->tagProperties['id'];
        } else {
            return null;
        }
    }

    /**
     * Returns property tag value
     *
     * @param string $propertyName název vlastnosti tagu. např. "src" u obrázku
     *
     * @return string current tag property value
     */
    public function getTagProperty($propertyName)
    {
        $property = null;
        if (isset($this->tagProperties[$propertyName])) {
            $property = $this->tagProperties[$propertyName];
        }
        return $property;
    }

    /**
     * Nastaví paramatry tagu
     *
     * @param mixed $tagProperties asociativní pole parametrů tagu
     */
    public function setTagProperties($tagProperties)
    {
        if (is_array($tagProperties)) {
            if (isset($tagProperties['id'])) {
                $tagProperties['id'] = preg_replace('/[^A-Za-z0-9_\\-]/', '', $tagProperties['id']);
            }
            if (is_array($this->tagProperties)) {
                $this->tagProperties = array_merge($this->tagProperties, $tagProperties);
            } else {
                $this->tagProperties = $tagProperties;
            }
            if (isset($tagProperties['name'])) {
                $this->setTagName($tagProperties['name']);
            }
        } else {
            $propBuff = $tagProperties;
            //if (substr($propBuff, 0, 1) != ' ') $propBuff = ' ' . $tagProperties;
            $this->tagProperties = ' ' . $propBuff;
        }
    }

    /**
     * Vrátí parametry tagu jako řetězec
     *
     * @param mixed $tagProperties asociativní pole parametrú nebo řetězec
     *
     * @return string
     */
    public function tagPropertiesToString($tagProperties = null)
    {
        if (!$tagProperties) {
            $tagProperties = $this->tagProperties;
        }
        if (is_array($tagProperties)) {
            $TagPropertiesString = ' ';
            foreach ($tagProperties as $TagPropertyName => $TagPropertyValue) {
                if ($TagPropertyName) {
                    if (is_numeric($TagPropertyName)) {
                        if (!strstr($TagPropertiesString, ' ' . $TagPropertyValue . ' ')) {
                            $TagPropertiesString .= ' ' . $TagPropertyValue . ' ';
                        }
                    } else {
                        $TagPropertiesString .= $TagPropertyName . '="' . $TagPropertyValue . '" ';
                    }
                } else {
                    $TagPropertiesString .= $TagPropertyValue . ' ';
                }
            }
            return $TagPropertiesString;
        } else {
            return $this->tagProperties;
        }
    }

    /**
     * Nastaví paramatry Css
     *
     * @param array|string $cssProperties asociativní pole, nebo CSS definice
     */
    public function setTagCss($cssProperties)
    {
        if (is_array($cssProperties)) {
            if (is_array($this->cssProperties)) {
                $this->cssProperties = array_merge($this->cssProperties, $cssProperties);
            } else {
                $this->cssProperties = $cssProperties;
            }
        } else {
            $propBuff = $cssProperties;
            //if (substr($propBuff, 0, 1) != ' ') $propBuff = ' ' . $cssProperties;
            $this->cssProperties = ' ' . $propBuff;
        }
        $this->setTagProperties(['style' => $this->cssPropertiesToString()]);
    }

    /**
     * Vrátí parametry Cssu jako řetězec
     *
     * @param array|string $cssProperties pole vlastností nebo CSS definice
     *
     * @return string
     */
    public function cssPropertiesToString($cssProperties = null)
    {
        if (!$cssProperties) {
            $cssProperties = $this->cssProperties;
        }
        if (is_array($cssProperties)) {
            $cssPropertiesString = ' ';
            foreach ($cssProperties as $CssPropertyName => $CssPropertyValue) {
                $cssPropertiesString .= $CssPropertyName . ':' . $CssPropertyValue . ';';
            }
            return $cssPropertiesString;
        } else {
            return $this->cssProperties;
        }
    }

    /**
     * Vykreslí tag
     */
    public function draw()
    {
        echo '
<' . $this->tagType;
        echo $this->tagPropertiesToString();
        echo $this->trail;
        echo '>';
    }
}
