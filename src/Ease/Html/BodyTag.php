<?php

namespace Ease\Html;

/**
 * Třída pro tělo HTML stránky
 *
 * @subpackage 
 * @author     Vitex <vitex@hippy.cz>
 */
class BodyTag extends PairTag {

    /**
     * Tělo stránky je v aplikaci vždy dostupně jako
     * $this->easeShared->webPage->body
     *
     * @param string $TagID   id tagu
     * @param mixed  $Content vkládané prvky
     */
    public function __construct($TagID = null, $Content = null) {
        parent::__construct('body', null, $Content);
        if (!is_null($TagID)) {
            $this->setTagID($TagID);
        }
    }

    /**
     * Nastaví jméno objektu na "body"
     *
     * @param string $ObjectName jméno objektu
     */
    public function setObjectName($ObjectName = null) {
        parent::setObjectName('body');
    }

}
