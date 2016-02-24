<?php

namespace Ease\Html;

/**
 * Zobrazí input text tag.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputSearchTag extends InputTag
{
    /**
     * URL zdroje dat pro hinter.
     *
     * @var string
     */
    public $dataSourceURL = null;

    /**
     * Zobrazí tag pro vyhledávací box.
     *
     * @param string $name       jméno
     * @param string $value      předvolená hodnota
     * @param array  $properties dodatečné vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        $properties['type'] = 'search';
        if ($value) {
            $properties['value'] = $value;
        }
        if ($name) {
            $properties['name'] = $name;
        }
        if (!isset($properties['id'])) {
            $this->setTagID($name.\Ease\Brick::randomString());
        }
        $this->setTagProperties($properties);
        parent::__construct($name, $value);
    }

    /**
     * Nastaví zdroj dat našeptávače.
     *
     * @param string $DataSourceURL url zdroje dat našeptávače ve formátu JSON
     */
    public function setDataSource($DataSourceURL)
    {
        $this->dataSourceURL = $DataSourceURL;
    }

    /**
     * Vloží do stránky scripty pro hinter.
     */
    public function finalize()
    {
        if (!is_null($this->dataSourceURL)) {
            EaseJQueryUIPart::jQueryze($this);
            $this->addCSS('.ui-autocomplete-loading { background: white url(\'Ease/css/images/ui-anim_basic_16x16.gif\') right center no-repeat; }');
            $this->addJavaScript(
                '
    $( "#'.$this->getTagID().'" ).bind( "keydown", function (event) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                            $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
            }
    })
    .autocomplete({
            source: function (request, response) {
                    $.getJSON( "'.$this->dataSourceURL.'", { term: request.term }, response );
            },
            focus: function () {
                    // prevent value inserted on focus
                    return false;
            },
            open: function () {
                    $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
            },
            close: function () {
                    $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
            }
    });



            ',
                null,
                true
            );
        }
    }
}
