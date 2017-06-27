<?php
/**
 * Html form able to be recursive filled
 * Html formulář se schopností rekurzivne naplnit hodnotami vložené prvky.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */

namespace Ease\Html;

class Form extends PairTag
{
    /**
     * Cíl formu.
     *
     * @var string URL cíle formuláře
     */
    public $formTarget = null;

    /**
     * Metoda odesílání.
     *
     * @var string GET|POST
     */
    public $formMethod = null;

    /**
     * Nastavovat formuláři jméno ?
     *
     * @var type
     */
    public $setName = false;

    /**
     * Zobrazí html formulář.
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
                                $tagProperties = [])
    {
        parent::__construct('form',
            ['method' => $formMethod, 'name' => $formName]);
        if (!is_null($formAction)) {
            $this->setFormTarget($formAction);
        } else {
            if (isset($_SERVER['REQUEST_URI'])) {
                $this->setFormTarget($_SERVER['REQUEST_URI']);
            }
        }
        if (isset($formContents)) {
            $this->addItem($formContents);
        }
        if (count($tagProperties)) {
            $this->setTagProperties($tagProperties);
        }
    }

    /**
     * Nastaví cíl odeslání.
     *
     * @param string $formTarget cíl odeslání formuláře
     */
    public function setFormTarget($formTarget)
    {
        $this->formTarget = $formTarget;
        $this->setTagProperties(['action' => $formTarget]);
    }

    /**
     * Změní jeden nebo více parametrů v ACTION url formuláře.
     *
     * @param array $parametersToChange pole parametrů
     * @param bool  $replace            přepisovat již existující
     */
    public function changeActionParameter($parametersToChange, $replace = true)
    {
        if (is_array($parametersToChange) && count($parametersToChange)) {
            foreach ($parametersToChange as $paramName => $paramValue) {
                if ($paramValue === true) {
                    unset($parametersToChange[$paramName]);
                }
            }
            $targetParts        = explode('&',
                str_replace('&&', '&', str_replace('?', '&', $this->formTarget)));
            $formTargetComputed = '';
            if (is_array($targetParts) && count($targetParts)) {
                $targetPartsValues = [];
                foreach ($targetParts as $targetPart) {
                    if (!strstr($targetPart, '=')) {
                        $formTargetComputed .= $targetPart;
                        continue;
                    }
                    list($targetPartName, $targetPartValue) = explode('=',
                        $targetPart);
                    if ($targetPartValue === true) {
                        continue;
                    }
                    $targetPartsValues[$targetPartName] = $targetPartValue;
                }
            }
            if ($replace === true) {
                $newTargPartVals = array_merge($targetPartsValues,
                    $parametersToChange);
            } else {
                $newTargPartVals = array_merge($parametersToChange,
                    $targetPartsValues);
            }
            $glueSign = '?';
            foreach ($newTargPartVals as $newTargetPartsValName => $newTargetPartsValue) {
                $formTargetComputed .= $glueSign.urlencode($newTargetPartsValName).'='.urlencode($newTargetPartsValue);
                $glueSign           = '&';
            }
            $this->setFormTarget($formTargetComputed);
        }
    }

    /**
     * Pokusí se najít ve vložených objektech tag zadaného jména.
     *
     * @param string        $searchFor jméno hledaného elementu
     * @param EaseContainer $where     objekt v němž je hledáno
     *
     * @return EaseContainer|class
     */
    public function &objectContentSearch($searchFor, $where = null)
    {
        if (is_null($where)) {
            $where = &$this;
        }
        $itemFound = null;
        if (isset($where->pageParts) && is_array($where->pageParts) && count($where->pageParts)) {
            foreach ($where->pageParts as $pagePart) {
                if (is_object($pagePart)) {
                    if (method_exists($pagePart, 'GetTagName')) {
                        if ($pagePart->getTagName() == $searchFor) {
                            return $pagePart;
                        }
                    } else {
                        $itemFound = $this->objectContentSearch($searchFor,
                            $pagePart);
                        if ($itemFound) {
                            return $itemFound;
                        }
                    }
                }
            }
        }

        return $itemFound;
    }

    /**
     * Doplnění perzistentních hodnot.
     */
    public function finalize()
    {
        $this->setupWebPage();
        if (isset($this->webPage->requestValuesToKeep) && is_array($this->webPage->requestValuesToKeep)
            && count($this->webPage->requestValuesToKeep)) {
            foreach ($this->webPage->requestValuesToKeep as $name => $value) {
                if (!$this->objectContentSearch($name)) {
                    if (is_string($value)) {
                        $this->addItem(new InputHiddenTag($name, $value));
                    }
                }
            }
        }
    }
}
