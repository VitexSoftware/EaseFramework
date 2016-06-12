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
     * @param string $tagID   id tagu
     * @param mixed  $content vkládané prvky
     */
    public function __construct($tagID = null, $content = null)
    {
        parent::__construct('body', null, $content);
        if (!is_null($tagID)) {
            $this->setTagID($tagID);
        }
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
