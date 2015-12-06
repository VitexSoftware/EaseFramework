<?php

/**
 * Tlačítko s potvrzením
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @todo dodělat #IDčka ...
 */
class EaseJQConfirmedLinkButton extends EaseJQueryLinkButton
{
    /**
     *
     * @var type
     */
    private $id = null;
    /**
     * Link se vzhledem tlačítka a potvrzením odeslání
     *
     * @see http://jqueryui.com/demos/button/
     *
     * @param string       $href       cíl odkazu
     * @param string       $contents   obsah tlačítka
     * @param array|string $JQOptions  parametry pro $.button()
     * @param array        $Properties vlastnosti HTML tagu
     */
    public function __construct($href, $contents)
    {
        $this->id = $this->randomString();
        parent::__construct('#', $contents, null, array('id' => $this->id . '-button'));
        $confirmDialog = $this->addItem(new EaseJQueryDialog($this->id . '-dialog', _('potvrzení'), _('Opravdu') . ' ' . $contents . ' ?', 'ui-icon-alert'));
        $yes = _('Ano');
        $no = _('Ne');
        $confirmDialog->partProperties = array('autoOpen' => false, 'modal' => true, 'show' => 'slide', 'buttons' => array($yes => 'function () { window.location.href = "' . $href . '"; }', $no => 'function () { $( this ).dialog( "close" ); }'));
        EaseShared::webPage()->addJavascript('', 1000, true);
    }
    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady()
    {
        return '$("#' . $this->Name . '").button( {' . EaseJQueryPart::partPropertiesToString($this->JQOptions) . '} ).click( function () { $( "#' . $this->id . '-dialog" ).dialog( "open" ); } );';
    }
}