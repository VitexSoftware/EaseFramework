<?php

namespace Ease\Html;

/**
 * Html Fieldset.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class FieldSet extends PairTag
{
    /**
     * Legenda rámečku.
     *
     * @var mixed
     */
    public $legend = null;

    /**
     * Objekt s tagem Legendy.
     *
     * @var PairTag
     */
    public $legendTag = null;

    /**
     * Obsah rámu.
     *
     * @var mixed
     */
    public $content = null;

    /**
     * Zobrazí rámeček.
     *
     * @param string|mixed $legend  popisek - text nebo Ease objekty
     * @param mixed        $content prvky vkládané do rámečku
     */
    public function __construct($legend, $content = null)
    {
        $this->setTagName($legend);
        $this->legend    = $legend;
        $this->legendTag = $this->addItem(new PairTag('legend', null,
                $this->legend));
        if ($content) {
            $this->content = $this->addItem($content);
        }
        parent::__construct('fieldset');
    }

    /**
     * Nastavení legendy.
     *
     * @param string $legend popisek
     */
    public function setLegend($legend)
    {
        $this->legend = $legend;
    }

    /**
     * Vložení legendy.
     */
    public function finalize()
    {
        if ($this->legend) {
            if (is_object(reset($this->pageParts))) {
                reset($this->pageParts)->pageParts = [$this->legend];
            } else {
                array_unshift($this->pageParts, $this->legendTag);
                reset($this->pageParts)->pageParts = [$this->legend];
            }
        }
    }
}
