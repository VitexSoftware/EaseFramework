<?php

namespace Ease\JQuery;

/**
 * Hypertextový odkaz v designu jQueryUI tlačítka.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 *
 * @link   http://jqueryui.com/demos/button/
 */
class LinkButton extends UIPart
{
    /**
     * Jméno tlačítka.
     *
     * @var string
     */
    private $name = null;

    /**
     * Paramatry pro jQuery .button().
     *
     * @var array
     */
    public $jqOptions = null;

    /**
     * Odkaz tlačítka.
     *
     * @var Ease\Html\ATag
     */
    public $button = null;

    /**
     * Link se vzhledem tlačítka.
     *
     * @see http://jqueryui.com/demos/button/
     *
     * @param string       $href       cíl odkazu
     * @param string       $contents   obsah tlačítka
     * @param array|string $jQOptions  parametry pro $.button()
     * @param array        $properties vlastnosti HTML tagu
     */
    public function __construct($href, $contents, $jQOptions = null,
                                $properties = [])
    {
        parent::__construct();
        if (!isset($properties['id'])) {
            $this->name = \Ease\Brick::randomString();
        } else {
            $this->name = $properties['id'];
        }
        $this->jqOptions = $jQOptions;
        $this->button = $this->addItem(new \Ease\Html\ATag($href, $contents));
        if ($properties) {
            $this->button->setTagProperties($properties);
        }
        $this->button->setTagProperties(['id' => $this->name]);
    }

    /**
     * Nastaveni javascriptu.
     */
    public function onDocumentReady()
    {
        return '$("#'.$this->name.'").button( {'.Part::partPropertiesToString($this->jqOptions).'} )';
    }

    /**
     * Nastaví ID linku tlačítka.
     *
     * @param type $TagID ID tagu
     *
     * @return type
     */
    public function setTagID($TagID = null)
    {
        return $this->button->setTagID($TagID);
    }

    /**
     * Vrací ID linku tlačítka.
     *
     * @return type
     */
    public function getTagID()
    {
        return $this->button->getTagID();
    }
}
