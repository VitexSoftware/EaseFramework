<?php
/**
 * Panel Twitter Bootstrapu.
 */

namespace Ease\TWB;

class Panel extends \Ease\Html\DivTag
{
    /**
     * Hlavička panelu.
     *
     * @var \Ease\Html\DivTag
     */
    public $heading = null;

    /**
     * Tělo panelu.
     *
     * @var \Ease\Html\DivTag
     */
    public $body = null;

    /**
     * Patička panelu.
     *
     * @var \Ease\Html\DivTag
     */
    public $footer = null;

    /**
     * Typ Panelu.
     *
     * @var string succes|wanring|info|danger
     */
    public $type = 'default';

    /**
     * Obsah k přidání do patičky panelu.
     *
     * @var mixed
     */
    public $addToFooter = null;

    /**
     * Panel Twitter Bootstrapu.
     *
     * @param string|mixed $heading
     * @param string       $type    succes|wanring|info|danger
     * @param mixes        $body    tělo panelu
     * @param mixed        $footer  patička panelu. FALSE = nezobrazit vůbec
     */
    public function __construct($heading = null, $type = 'default',
                                $body = null, $footer = null)
    {
        $this->type        = $type;
        $this->addToFooter = $footer;
        parent::__construct(null, ['class' => 'panel panel-'.$this->type]);
        if (!is_null($heading)) {
            $this->heading = parent::addItem(new \Ease\Html\DivTag($heading,
                        ['class' => 'panel-heading']), 'head');
        }
        $this->body = parent::addItem(new \Ease\Html\DivTag($body,
                    ['class' => 'panel-body']), 'body');
    }

    /**
     * Vloží další element do objektu.
     *
     * @param mixed  $pageItem     hodnota nebo EaseObjekt s metodou draw()
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return pointer Odkaz na vložený objekt
     */
    public function &addItem($pageItem, $pageItemName = null)
    {
        $added = $this->body->addItem($pageItem, $pageItemName);

        return $added;
    }

    /**
     * Vloží obsah do patičky.
     */
    public function finalize()
    {
        if (!count($this->body->pageParts)) {
            unset($this->pageParts['body']);
        }
        if ($this->addToFooter) {
            $this->footer()->addItem($this->addToFooter);
        }
    }

    /**
     * Vrací patičku panelu.
     *
     * @param mixed $content obsah pro vložení to patičky
     *
     * @return \Ease\Html\DivTag
     */
    public function footer($content = null)
    {
        if (is_object($this->footer)) {
            if ($content) {
                $this->footer->addItem($content);
            }
        } else {
            $this->footer = parent::addItem(new \Ease\Html\DivTag($content,
                        ['class' => 'panel-footer panel-'.$this->type]),
                    'footer');
        }

        return $this->footer;
    }
}
