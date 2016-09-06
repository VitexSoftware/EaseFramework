<?php

namespace Ease\JQuery;

/**
 * Tlačítko s potvrzením.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class ConfirmedLinkButton extends LinkButton
{
    /**
     * @var type
     */
    private $id = null;

    /**
     * Link se vzhledem tlačítka a potvrzením odeslání.
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
        parent::__construct('#', $contents, null, ['id' => $this->id.'-button']);
        $confirmDialog = $this->addItem(new Dialog($this->id.'-dialog',
            _('Confirmation'), _('Really').' '.$contents.' ?', 'ui-icon-alert'));
        $yes = _('Yes');
        $no = _('No');
        $confirmDialog->partProperties = ['autoOpen' => false, 'modal' => true, 'show' => 'slide',
            'buttons' => [$yes => 'function () { window.location.href = "'.$href.'"; }',
                $no => 'function () { $( this ).dialog( "close" ); }', ], ];
        \Ease\Shared::webPage()->addJavascript('', 1000, true);
    }

    /**
     * Nastaveni javascriptu.
     */
    public function onDocumentReady()
    {
        return '$("#'.$this->getTagID().'").button( {'.Part::partPropertiesToString($this->JQOptions).'} ).click( function () { $( "#'.$this->id.'-dialog" ).dialog( "open" ); } );';
    }
}
