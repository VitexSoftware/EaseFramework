<?php

/**
 * Provede uložení obecných dat
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@vitexsoftware.cz (G)
 */
require_once 'EaseBase.php';

/**
 * Provede uložení obecných dat
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseSaver extends EaseBrick
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
        $Structure = array($this->getMyKeyColumn() => array('type' => 'int', 'key' => 'primary', 'unsigned' => true));
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
     * @param array $Data
     *
     * @return int
     */
    public function insertToMySQL($Data = null)
    {
        if (is_null($Data)) {
            $Data = $this->getData();
        }

        $SaveResult = parent::InsertToMySQL($Data);
        if ($this->myDbLink->ErrorNumber == 1054) { //Column doesn't exist
            if ($this->createMissingColumns($Data) > 0) {
                $SaveResult = parent::InsertToMySQL($Data);
            }
        }

        return $SaveResult;
    }

    /**
     * Vytvoří v databázi sloupeček pro uložení hodnoty widgetu
     *
     * @param array $Data sloupečky k vytvoření
     *
     * @return int
     */
    public function createMissingColumns($Data = null)
    {
        if (is_null($Data)) {
            $Data = $this->getData();
        }

        $actualStructure = $this->myDbLink->describe($this->myTable);

        $structure = array();
        foreach ($Data as $column => $value) {
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
     * @param int     $ItemID     klíč záznamu k načtení
     * @param string  $DataPrefix název datové skupiny
     * @param boolean $Multiplete nevarovat v případě více výsledků
     *
     * @return array Results
     */
    public function loadFromMySQL($ItemID = null, $DataPrefix = null, $Multiplete = false)
    {
        $this->setMyKey($this->User->getUserID());
        $Result = parent::loadFromMySQL($ItemID, $DataPrefix, $Multiplete);
        if ($Result) {
            return $Result;
        }
        if ($this->myDbLink->ErrorNumber == 1146) { //Table doesn't exist
            $this->createmyTable();
            $this->insertToMySQL();

            return parent::loadFromMySQL($ItemID, $DataPrefix, $Multiplete);
        }

        return $Result;
    }

    /**
     * Pokusí se updatnout záznam. Neexistuje, tak vloží nový záznam
     *
     * @param array $Data
     *
     * @return int
     */
    public function updateToMySQL($Data = null)
    {
        if (!isset($Data)) {
            $Data = $this->getData();
        }
        $UpdateResult = parent::UpdateToMySQL($Data);
        if ($UpdateResult && $this->myDbLink->getNumRows()) {
            return $UpdateResult;
        } else {
            return $this->insertToMySQL($Data);
        }
    }

    /**
     * jQuery Kod barevného označení výsledku případného uložení
     *
     * @param EaseContainer|mixed $EnclosedElement element, který se má ukládat
     * @param string              $Infotext        volitelný zobrazovaný text
     *
     * @return string
     */
    public static function visualResponse($EnclosedElement, $Infotext = null)
    {
        if (is_null($Infotext)) {
            $Infotext = _('Položku se nepodařilo uložit. Prosím zkuste jinou hodnotu.');
        }
        EaseShared::webPage()->addItem('<div id="dialog-message' . $EnclosedElement->GetTagID() . '" title="' . _('Neuloženo') . '">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
        ' . $Infotext . '
    </p>
</div>'
        );
        EaseShared::webPage()->addJavaScript('$( \'#dialog-message' . $EnclosedElement->GetTagID() . '\' ).dialog({ autoOpen: false, modal: true, buttons: { Ok: function () { $( this ).dialog( \'close\' );	} } });', null, true);

        return '.success(function (data, textStatus) { $(\'#' . $EnclosedElement->GetTagID() . '\').css(\'border\',\'green 2px solid\').css(\'margin\',\'2px\'); }).error(  function () { $(\'#' . $EnclosedElement->GetTagID() . '\').val(\'' . $EnclosedElement->getValue() . '\'); $( \'#dialog-message' . $EnclosedElement->GetTagID() . '\' ).dialog(\'open\') } );  $(\'#' . $EnclosedElement->GetTagID() . '\').css(\'border\',\'red 2px solid\').css(\'margin\',\'2px\'); ';
    }

}
