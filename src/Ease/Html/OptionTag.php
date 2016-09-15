<?php

namespace Ease\Html;

/**
 * Položka seznamu.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class OptionTag extends PairTag
{
    /**
     * Hodnota.
     *
     * @var string
     */
    public $value = null;

    /**
     * Tag položky rozbalovací nabídky.
     *
     * @param string|mixed $content text volby
     * @param string|int   $value   vracená hodnota
     */
    public function __construct($content, $value = null)
    {
        parent::__construct('option', ['value' => $value], $content);
        $this->setObjectName($this->getObjectName().'@'.$value);
        $this->value = &$this->tagProperties['value'];
    }

    /**
     * Nastaví předvolenou položku.
     */
    public function setDefault()
    {
        return $this->setTagProperties(['selected']);
    }

    /**
     * Nastaví hodnotu.
     *
     * @param int|string $value vracená hodnota
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Value Getter.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
