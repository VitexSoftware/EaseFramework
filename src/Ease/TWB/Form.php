<?php
/**
 * Formulář Bootstrapu.
 */

namespace Ease\TWB;

class Form extends \Ease\Html\Form
{

    /**
     * Formulář Bootstrapu.
     *
     * @param string $formName      jméno formuláře
     * @param string $formAction    cíl formulář např login.php
     * @param string $formMethod    metoda odesílání POST|GET
     * @param mixed  $formContents  prvky uvnitř formuláře
     * @param array  $tagProperties vlastnosti tagu například:
     *                              array('enctype' => 'multipart/form-data')
     */
    public function __construct($formName, $formAction = null,
                                $formMethod = 'post', $formContents = null,
                                $tagProperties = null)
    {
        if (!isset($tagProperties['class'])) {
            $tagProperties['class'] = 'form-horizontal';
        } else {
            if (!strstr($tagProperties['class'], 'form')) {
                $tagProperties['class'] = 'form-horizontal';
            }
        }
        $tagProperties['role'] = 'form';
        parent::__construct($formName, $formAction, $formMethod, $formContents,
            $tagProperties);
    }

    /**
     * Vloží prvek do formuláře.
     *
     * @param mixed  $input       Vstupní prvek
     * @param string $caption     Popisek
     * @param string $placeholder předvysvětlující text
     * @param string $helptext    Dodatečná nápověda
     */
    public function addInput($input, $caption = null, $placeholder = null,
                             $helptext = null)
    {
        return $this->addItem(new FormGroup($caption, $input, $placeholder,
                    $helptext));
    }

    /**
     * Vloží další element do formuláře a upraví mu css.
     *
     * @param mixed  $pageItem     hodnota nebo EaseObjekt s metodou draw()
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return pointer Odkaz na vložený objekt
     */
    public function &addItem($pageItem, $pageItemName = null)
    {
        if (is_object($pageItem) && method_exists($pageItem, 'setTagClass')) {
            if (strtolower($pageItem->tagType) == 'select') {
                $pageItem->setTagClass(trim(str_replace('form_control', '',
                            $pageItem->getTagClass().' form-control')));
            }
        }
        $added = parent::addItem($pageItem, $pageItemName);

        return $added;
    }
}
