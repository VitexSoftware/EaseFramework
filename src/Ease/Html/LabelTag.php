<?php

namespace Ease\Html;

/**
 * Tag Label pro LabeledInput
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class LabelTag extends PairTag
{

    /**
     * Odkaz na obsah
     * @var mixed
     */
    public $Contents = NULL;

    /**
     * zobrazí tag pro návěští
     *
     * @param string $for        vztažný element
     * @param mixed  $contents   obsah opatřovaný popiskem
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($for, $contents = null, $properties = null)
    {
        $this->setTagProperties(['for' => $for]);
        parent::__construct('label', $properties);
        $this->Contents = $this->addItem($contents);
    }

    /**
     * Nastaví jméno objektu
     *
     * @param string $objectName nastavované jméno
     *
     * @return string New object name
     */
    public function setObjectName($objectName = null)
    {
        if ($objectName) {
            return parent::setObjectName($objectName);
        }
        return parent::setObjectName(get_class($this) . '@' . $this->getTagProperty('for'));
    }

}
