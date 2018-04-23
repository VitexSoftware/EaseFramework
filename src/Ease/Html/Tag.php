<?php

namespace Ease\Html;

/**
 * Common HTML tag class.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class Tag extends \Ease\Page
{
    /**
     * Jméno tagu - je použit i jako jméno objektu.
     *
     * @var string
     */
    public $tagName = null;

    /**
     * Typ tagu - např A či STRONG.
     *
     * @var string
     */
    public $tagType = null;

    /**
     * Pole vlastností tagu.
     *
     * @var array
     */
    public $tagProperties = null;

    /**
     * pole ze kterého se rendruje obsah STYLE tagu.
     *
     * @var array
     */
    public $cssProperties = null;

    /**
     * Nelogovat události HTML objektů.
     *
     * @var string
     */
    public $logType = 'none';

    /**
     * Koncové lomítko pro xhtml.
     *
     * @var string
     */
    public $trail = ' /';

    /**
     * Má si objekt automaticky plnit vlastnost name ?
     */
    public $setName = false;

    /**
     * Objekt pro vykreslení obecného nepárového html tagu.
     *
     * @param string       $tagType       typ tagu
     * @param array|string $tagProperties parametry tagu
     */
    public function __construct($tagType = null, $tagProperties = null)
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
    }

    /**
     * Set ObjectName
     * Nastaví jméno objektu.
     *
     * @param string $objectName jméno objektu
     *
     * @return string New object name
     */
    public function setObjectName($objectName = null)
    {
        if (!is_null($objectName)) {
            return parent::setObjectName($objectName);
        }
        if (!empty($this->tagName)) {
            return parent::setObjectName(get_class($this).'@'.$this->tagName);
        } else {
            if (!empty($this->tagType)) {
                return parent::setObjectName(get_class($this).'@'.$this->tagType);
            } else {
                return parent::setObjectName();
            }
        }
    }

    /**
     * Nastaví jméno tagu. Unused ...
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
     * Returns name of tag.
     *
     * @return string
     */
    public function getTagName()
    {
        $tagName = null;
        if ($this->setName === true) {
            if (isset($this->tagProperties['name'])) {
                $tagName = $this->tagProperties['name'];
            }
        } else {
            $tagName = $this->tagName;
        }

        return $tagName;
    }

    /**
     * Nastaví typ tagu.
     *
     * @param string $tagType typ tagu - např. img
     */
    public function setTagType($tagType)
    {
        $this->tagType = $tagType;
    }

    /**
     * Vrací typ tagu.
     *
     * @return string typ tagu - např. img
     */
    public function getTagType()
    {
        return $this->tagType;
    }

    /**
     * Nastaví classu tagu.
     *
     * @param string $className jméno css třídy
     */
    public function setTagClass($className)
    {
        $this->setTagProperties(['class' => $className]);
    }

    /**
     * Přidá classu tagu.
     *
     * @param string $className jméno css třídy
     */
    public function addTagClass($className)
    {
        $this->setTagClass(trim($this->getTagClass().' '.$className));
    }

    /**
     * Vrací css classu tagu.
     */
    public function getTagClass()
    {
        return $this->getTagProperty('class');
    }

    /**
     * Nastaví tagu zadane id, nebo vygenerované náhodné.
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
     * Vrací ID html tagu.
     *
     * @return string
     */
    public function getTagID()
    {
        return $this->getTagProperty('id');
    }

    /**
     * Returns property tag value.
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
     * Nastaví paramatry tagu.
     *
     * @param mixed $tagProperties asociativní pole parametrů tagu
     */
    public function setTagProperties(array $tagProperties)
    {
        if (isset($tagProperties['id'])) {
            $tagProperties['id'] = preg_replace('/[^A-Za-z0-9_\\-]/', '',
                $tagProperties['id']);
        }
        if (is_array($this->tagProperties)) {
            $this->tagProperties = array_merge($this->tagProperties,
                $tagProperties);
        } else {
            $this->tagProperties = $tagProperties;
        }
        if (isset($tagProperties['name'])) {
            $this->setTagName($tagProperties['name']);
        }
    }

    /**
     * Vrátí parametry tagu jako řetězec.
     *
     * @param mixed $tagProperties asociativní pole parametrú nebo řetězec
     *
     * @return string
     */
    public function tagPropertiesToString($tagProperties = null)
    {
        $tagPropertiesString = '';
        if (!$tagProperties) {
            $tagProperties = $this->tagProperties;
        }
        if (is_array($tagProperties)) {
            $tagPropertiesString = ' ';
            foreach ($tagProperties as $tagPropertyName => $tagPropertyValue) {
                if ($tagPropertyName) {
                    if (is_numeric($tagPropertyName)) {
                        if (!strstr($tagPropertiesString,
                                ' '.$tagPropertyValue.' ')) {
                            $tagPropertiesString .= ' '.$tagPropertyValue.' ';
                        }
                    } else {
                        $tagPropertiesString .= $tagPropertyName.'="'.$tagPropertyValue.'" ';
                    }
                } else {
                    $tagPropertiesString .= $tagPropertyValue.' ';
                }
            }

            $tagPropertiesString = trim($tagPropertiesString);
        }
        return $tagPropertiesString;
    }

    /**
     * Nastaví paramatry Css.
     *
     * @param array $cssProperties asociativní pole, nebo CSS definice
     */
    public function setTagCss(array $cssProperties)
    {
        $this->cssProperties = $cssProperties;
        $this->setTagProperties(['style' => $this->cssPropertiesToString()]);
    }

    /**
     * Vrátí parametry Cssu jako řetězec.
     *
     * @param array|string $cssProperties pole vlastností nebo CSS definice
     *
     * @return string
     */
    public function cssPropertiesToString($cssProperties = null)
    {
        $cssPropertiesString = '';
        if (!$cssProperties) {
            $cssProperties = $this->cssProperties;
        }
        $cssPropertiesString = ' ';
        foreach ($cssProperties as $CssPropertyName => $CssPropertyValue) {
            $cssPropertiesString .= $CssPropertyName.':'.$CssPropertyValue.';';
        }
        return trim($cssPropertiesString);
    }

    /**
     * Add Css to tag properties.
     */
    public function finalize()
    {
        if (!empty($this->cssProperties)) {
            $this->tagProperties['style'] = $this->cssPropertiesToString();
        }
    }

    /**
     * Vykreslí tag.
     */
    public function draw()
    {
        echo '<' . trim($this->tagType.' '.$this->tagPropertiesToString());
        echo $this->trail.'>';
        $this->drawStatus = true;
    }
}
