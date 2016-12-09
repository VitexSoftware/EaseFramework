<?php

namespace Ease\Html;

/**
 * Tag Label pro LabeledInput.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class LabelTag extends PairTag
{
    /**
     * Odkaz na obsah.
     *
     * @var mixed
     */
    public $contents = null;

    /**
     * Show tag label
     *
     * @param string $for        vztažný element
     * @param mixed  $contents   obsah opatřovaný popiskem
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($for, $contents = null, $properties = [])
    {
        $this->setTagProperties(['for' => $for]);
        parent::__construct('label', $properties);
        $this->contents = $this->addItem($contents);
    }

    /**
     * Set object name.
     *
     * @param string $objectName nastavované jméno
     *
     * @return string New object name
     */
    public function setObjectName($objectName = null)
    {
        if (is_null($objectName)) {
            $objectName = get_class($this).'@'.$this->getTagProperty('for');
        }

        return parent::setObjectName($objectName);
    }
}
