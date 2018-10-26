<?php

namespace Ease\TWB;

class Modal extends \Ease\Html\DivTag
{
    /**
     * Spodek dialogu s tlačítky.
     *
     * @var \Ease\Html\DivTag
     */
    public $footer;

    /**
     * Vlastnosti dialogu.
     *
     * @var array
     */
    private $properties;

    /**
     * Jméno dialogu.
     *
     * @var string
     */
    public $name;

    /**
     * Titulek dialogu.
     *
     * @var string
     */
    public $title;

    /**
     * Tělo dialogu.
     *
     * @var \Ease\Html\DivTag
     */
    public $body;

    /**
     * Hlavička dialogu.
     *
     * @var \Ease\Html\DivTag
     */
    public $header;

    /**
     * Vytvoří modální dialogs.
     *
     * @param string $name
     * @param mixed  $content
     * @param array  $properties
     */
    public function __construct($name, $title, $content = null, $properties = [])
    {
        parent::__construct(null,
            ['class' => 'modal fade', 'id' => $name, 'tabindex' => '-1', 'role' => 'dialog',
                'aria-labelledby' => $title.'ID', 'aria-hidden' => 'true',]);
        $this->properties = $properties;
        $this->name       = $name;
        $this->title      = $title;
        $this->header     = new \Ease\Html\DivTag(null, ['class' => 'modal-header']);
        $this->header->addItem(new \Ease\Html\ButtonTag('&times;',
                ['class' => 'close', 'data-dismiss' => 'modal', 'aria-hidden' => 'true']));
        $this->body       = new \Ease\Html\DivTag($content,
            ['class' => 'modal-body']);
        $this->footer     = new \Ease\Html\DivTag(null, ['class' => 'modal-footer']);
        $this->footer->addItem(new \Ease\Html\ButtonTag(_('Close'),
                ['id' => $name.'ko', 'type' => 'button', 'class' => 'btn btn-default',
                'data-dismiss' => 'modal',]));
        $this->footer->addItem(new \Ease\Html\ButtonTag(_('Save'),
                ['id' => $name.'ok', 'type' => 'button', 'class' => 'btn btn-primary']));
    }

    /**
     * Finalize modal.
     */
    public function finalize()
    {
        Part::twBootstrapize();
        $modalDialog  = $this->addItem(new \Ease\Html\DivTag(null,
                ['class' => 'modal-dialog', 'role' => 'document']));
        $modalContent = $modalDialog->addItem(new \Ease\Html\DivTag(null,
                ['class' => 'modal-content']));
        $this->header->addItem(new \Ease\Html\H4Tag($this->title,
                ['class' => 'modal-title', 'id' => $this->title.'ID']));
        $modalContent->addItem($this->header);
        $modalContent->addItem($this->body);
        $modalContent->addItem($this->footer);
        if (is_array($this->properties)) {
            \Ease\Shared::webPage()->addJavaScript(
                ' $(function ()
{
    $("#'.$this->name.'").modal( {'.Part::partPropertiesToString($this->properties).'});
});
', null, true
            );
        } else {
            \Ease\Shared::webPage()->addJavaScript(
                ' $(function ()
{
    $("#'.$this->name.'").modal( '.$this->properties.');
});
', null, true
            );
        }
    }
}
