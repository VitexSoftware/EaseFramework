<?php

namespace Ease\Html;

/**
 * Definiční list.
 */
class DlTag extends PairTag
{

    /**
     * Definice.
     *
     * @param mixed $content
     * @param array $tagProperties vlastnosti tagu
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('dl', $tagProperties, $content);
    }

    /**
     * Vloží novou definici.
     *
     * @param string|mixed $term  Subjekt
     * @param string|mixed $value Popis subjektu
     */
    public function addDef($term, $value)
    {
        $this->addItem(new DtTag($term));
        $this->addItem(new DdTag($value));
    }
}
