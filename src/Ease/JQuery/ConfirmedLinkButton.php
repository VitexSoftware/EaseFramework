<?php

namespace Ease\JQuery;

/**
 * Tlačítko s potvrzením
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @todo dodělat #IDčka ...
 */
class ConfirmedLinkButton extends LinkButton {

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
    public function __construct($href, $contents) {
        $this->id = $this->randomString();
        parent::__construct('#', $contents, null, ['id' => $this->id . '-button']);
        $confirmDialog = $this->addItem(new Dialog($this->id . '-dialog', _('potvrzení'), _('Opravdu') . ' ' . $contents . ' ?', 'ui-icon-alert'));
        $yes = _('Ano');
        $no = _('Ne');
        $confirmDialog->partProperties = ['autoOpen' => false, 'modal' => true, 'show' => 'slide', 'buttons' => [$yes => 'function () { window.location.href = "' . $href . '"; }', $no => 'function () { $( this ).dialog( "close" ); }']];
        \Ease\Shared::webPage()->addJavascript('', 1000, true);
    }

    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady() {
        return '$("#' . $this->Name . '").button( {' . Part::partPropertiesToString($this->JQOptions) . '} ).click( function () { $( "#' . $this->id . '-dialog" ).dialog( "open" ); } );';
    }

}
