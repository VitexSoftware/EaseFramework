<?php

/**
 * Provede uložení obecných dat
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@vitexsoftware.cz (G)
 */

namespace Ease;

/**
 * Provede uložení obecných dat
 *
 * @author Vitex <vitex@hippy.cz>
 */
class Saver extends Brick
{

    /**
     * Pracujeme s tabulkou mains
     * @var string
     */
    public $myTable = true;

    /**
     * Pokud tabulka do které se má ukládat, neexistuje, vytvoří se
     */
    public function __construct()
    {
        parent::__construct();
        if (!$this->myDbLink->tableExist($this->myTable)) {
            $this->createmyTable();
        }
    }

    /**
     * Vytvoří prázdnou tabulku s klíčovým sloupcem
     */
    public function createmyTable()
    {
        $Structure = array($this->getmyKeyColumn() => array('type' => 'int', 'key' => 'primary', 'unsigned' => true));
        if ($this->myDbLink->createTable($Structure, $this->myTable)) {
            $this->addStatusMessage(sprintf(_('Tabulka % byla vytvořena'), $this->myTable));
        }
    }

    /**
     * Přiřadí objektu uživatele a nastaví DB
     *
     * @param Easeuser|EaseUser $User
     * @param object|mixed      $TargetObject
     *
     * @return boolen
     */
    public function setUpUser(&$User, &$TargetObject = null)
    {
        $this->setMyKey($User->getUserID());

        return parent::SetUpUser($User, $TargetObject);
    }

    /**
     * Pokusí se vložit  data, pokud se to nepovede, pokusí se vytvořit
     * chybějící sloupečky a vrátí vysledek dalšího uložení
     *
     * @param array $data
     *
     * @return int
     */
    public function insertToMySQL($data = null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }

        $SaveResult = parent::InsertToMySQL($data);
        if ($this->myDbLink->errorNumber == 1054) { //Column doesn't exist
            if ($this->createMissingColumns($data) > 0) {
                $SaveResult = parent::InsertToMySQL($data);
            }
        }

        return $SaveResult;
    }

    /**
     * Vytvoří v databázi sloupeček pro uložení hodnoty widgetu
     *
     * @param array $data sloupečky k vytvoření
     *
     * @return int
     */
    public function createMissingColumns($data = null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }

        $actualStructure = $this->myDbLink->describe($this->myTable);

        $structure = array();
        foreach ($data as $column => $value) {
            if (!array_key_exists($column, $actualStructure)) {
                $structure[$column] = $value;
            }
        }

        return EaseDbMySqli::createMissingColumns($this, $structure);
    }

    /**
     * Načte ze shopu data k aktuálnímu $ItemID
     * Pokud tabulka neexistuje, vytvoří ji
     *
     * @param int     $itemID     klíč záznamu k načtení
     * @param boolean $multiplete nevarovat v případě více výsledků
     *
     * @return array Results
     */
    public function loadFromMySQL($itemID = null, $multiplete = false)
    {
        $this->setMyKey($this->user->getUserID());
        $Result = parent::loadFromMySQL($itemID, $multiplete);
        if ($Result) {
            return $Result;
        }
        if ($this->myDbLink->errorNumber == 1146) { //Table doesn't exist
            $this->createmyTable();
            $this->insertToMySQL();

            return parent::loadFromMySQL($itemID, $multiplete);
        }

        return $Result;
    }

    /**
     * Pokusí se updatnout záznam. Neexistuje, tak vloží nový záznam
     *
     * @param array $data
     *
     * @return int
     */
    public function updateToMySQL($data = null)
    {
        if (!isset($data)) {
            $data = $this->getData();
        }
        $UpdateResult = parent::UpdateToMySQL($data);
        if ($UpdateResult && $this->myDbLink->getNumRows()) {
            return $UpdateResult;
        } else {
            return $this->insertToMySQL($data);
        }
    }

    /**
     * jQuery Kod barevného označení výsledku případného uložení
     *
     * @param Container|mixed $enclosedElement element, který se má ukládat
     * @param string              $Infotext        volitelný zobrazovaný text
     *
     * @return string
     */
    public static function visualResponse($enclosedElement, $Infotext = null)
    {
        if (is_null($Infotext)) {
            $Infotext = _('Položku se nepodařilo uložit. Prosím zkuste jinou hodnotu.');
        }
        EaseShared::webPage()->addItem('<div id="dialog-message' . $enclosedElement->GetTagID() . '" title="' . _('Neuloženo') . '">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
        ' . $Infotext . '
    </p>
</div>'
        );
        EaseShared::webPage()->addJavaScript('$( \'#dialog-message' . $enclosedElement->GetTagID() . '\' ).dialog({ autoOpen: false, modal: true, buttons: { Ok: function () { $( this ).dialog( \'close\' );	} } });', null, true);

        return '.success(function (data, textStatus) { $(\'#' . $enclosedElement->GetTagID() . '\').css(\'border\',\'green 2px solid\').css(\'margin\',\'2px\'); }).error(  function () { $(\'#' . $enclosedElement->GetTagID() . '\').val(\'' . $enclosedElement->getValue() . '\'); $( \'#dialog-message' . $enclosedElement->GetTagID() . '\' ).dialog(\'open\') } );  $(\'#' . $enclosedElement->GetTagID() . '\').css(\'border\',\'red 2px solid\').css(\'margin\',\'2px\'); ';
    }

}
