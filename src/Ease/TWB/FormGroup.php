<?php

namespace Ease\TWB;

class FormGroup extends \Ease\Html\DivTag
{

/**
 * Položka TWBootstrp formuláře.
 *
 * @param string         $label       popisek pole formuláře
 * @param \Ease\Html\Tag $content     widget formuláře
 * @param string         $placeholder předvysvětlující text
 * @param string         $helptext    Nápvěda pod prvkem
 * @param string         $addTagClass CSS třída kterou má být oskiován vložený prvek
 */
    public function __construct($label = null, $content = null,
                                $placeholder = null, $helptext = null,
                                $addTagClass = 'form-control')
    {
        if (is_object($content) && method_exists($content, 'getTagID')) {
            $id = $content->getTagID();
        } else {
            $id = null;
        }
        if (is_null($id)) {
            $formKey = \Ease\Brick::lettersOnly($label);
        } else {
            $formKey = $id;
        }
        $properties['class'] = 'form-group';
        parent::__construct(null, $properties);
        $this->addItem(new \Ease\Html\LabelTag($formKey, $label));
        $content->addTagClass($addTagClass);
        if ($placeholder) {
            $content->SetTagProperties(['placeholder' => $placeholder]);
        }
        $content->setTagId($formKey);
        $this->addItem($content);
        if ($helptext) {
            $this->addItem(new \Ease\Html\PTag($helptext,
                    ['class' => 'help-block']));
        }
    }
}
