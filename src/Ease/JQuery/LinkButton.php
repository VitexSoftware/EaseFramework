<?php

namespace Ease\JQuery;

/**
 * Hypertextový odkaz v designu jQueryUI tlačítka
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @link http://jqueryui.com/demos/button/
 */
class LinkButton extends UIPart
{

    /**
     * Jméno tlačítka
     * @var string
     */
    private $name = null;

    /**
     * Paramatry pro jQuery .button()
     * @var array
     */
    public $JQOptions = null;

    /**
     * Odkaz tlačítka
     * @var Ease\Html\ATag
     */
    public $Button = NULL;

    /**
     * Link se vzhledem tlačítka
     *
     * @see http://jqueryui.com/demos/button/
     *
     * @param string       $Href       cíl odkazu
     * @param string       $Contents   obsah tlačítka
     * @param array|string $JQOptions  parametry pro $.button()
     * @param array        $Properties vlastnosti HTML tagu
     */
    public function __construct($Href, $Contents, $JQOptions = null, $Properties = null)
    {
        parent::__construct();
        if (!isset($Properties['id'])) {
            $this->Name = EaseBrick::randomString();
        } else {
            $this->Name = $Properties['id'];
        }
        $this->JQOptions = $JQOptions;
        $this->Button = $this->addItem(new Ease\Html\ATag($Href, $Contents));
        if ($Properties) {
            $this->Button->setTagProperties($Properties);
        }
        $this->Button->setTagProperties(['id' => $this->Name]);
    }

    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady()
    {
        return '$("#' . $this->Name . '").button( {' . Part::partPropertiesToString($this->JQOptions) . '} )';
    }

    /**
     * Nastaví ID linku tlačítka
     *
     * @param  type $TagID ID tagu
     * @return type
     */
    public function setTagID($TagID = NULL)
    {
        return $this->Button->setTagID($TagID);
    }

    /**
     * Vrací ID linku tlačítka
     *
     * @return type
     */
    public function getTagID()
    {
        return $this->Button->getTagID();
    }

}
