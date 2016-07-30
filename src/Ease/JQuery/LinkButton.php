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
    public $JQOptions = null;

    /**
     * Odkaz tlačítka.
     *
     * @var Ease\Html\ATag
     */
    public $Button = null;

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
                                $properties = null)
    {
        parent::__construct();
        if (!isset($properties['id'])) {
            $this->Name = \Ease\Brick::randomString();
        } else {
            $this->Name = $properties['id'];
        }
        $this->JQOptions = $jQOptions;
        $this->Button = $this->addItem(new \Ease\Html\ATag($href, $contents));
        if ($properties) {
            $this->Button->setTagProperties($properties);
        }
        $this->Button->setTagProperties(['id' => $this->Name]);
    }

    /**
     * Nastaveni javascriptu.
     */
    public function onDocumentReady()
    {
        return '$("#'.$this->Name.'").button( {'.Part::partPropertiesToString($this->JQOptions).'} )';
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
        return $this->Button->setTagID($TagID);
    }

    /**
     * Vrací ID linku tlačítka.
     *
     * @return type
     */
    public function getTagID()
    {
        return $this->Button->getTagID();
    }
}
