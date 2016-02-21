<?php

namespace Ease\Html;

/**
 * Html Fieldset
 *
 * @author Vitex <vitex@hippy.cz>
 */
class FieldSet extends PairTag
{

    /**
     * Legenda rámečku
     *
     * @var mixed
     */
    public $Legend = null;

    /**
     * Objekt s tagem Legendy
     *
     * @var PairTag
     */
    public $LegendTag = null;

    /**
     * Obsah rámu
     *
     * @var mixed
     */
    public $Content = null;

    /**
     * Zobrazí rámeček
     *
     * @param string|mixed $legend  popisek - text nebo Ease objekty
     * @param mixed        $content prvky vkládané do rámečku
     */
    public function __construct($legend, $content = null)
    {
        $this->setTagName($legend);
        $this->Legend = $legend;
        $this->LegendTag = $this->addItem(new PairTag('legend', null, $this->Legend));
        if ($content) {
            $this->addItem($content);
        }
        parent::__construct('fieldset');
    }

    /**
     * Nastavení legendy
     *
     * @param string $legend popisek
     */
    public function setLegend($legend)
    {
        $this->Legend = $legend;
    }

    /**
     * Vložení legendy
     */
    public function finalize()
    {
        if ($this->Legend) {
            if (is_object(reset($this->pageParts))) {
                reset($this->pageParts)->pageParts = [$this->Legend];
            } else {
                array_unshift($this->pageParts, $this->LegendTag);
                reset($this->pageParts)->pageParts = [$this->Legend];
            }
        }
    }

}
