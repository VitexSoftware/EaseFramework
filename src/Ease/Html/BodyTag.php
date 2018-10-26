<?php

namespace Ease\Html;

/**
 * Třída pro tělo HTML stránky.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class BodyTag extends PairTag
{

    /**
     * Tělo stránky je v aplikaci vždy dostupně jako
     * $this->easeShared->webPage->body.
     *
     * @param mixed  $content     items to be included
     * @param array  $properties  additional properties for tag
     */
    public function __construct($content = null,$properties = null)
    {
        parent::__construct('body', $properties, $content);
    }

    /**
     * Nastaví jméno objektu na "body".
     *
     * @param string $objectName jméno objektu
     */
    public function setObjectName($objectName = null)
    {
        parent::setObjectName('body');
    }
}
