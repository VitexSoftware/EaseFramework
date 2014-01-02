<?php

/**
 * Modul průvodce pro framework
 *
 * PHP Version 5
 *
 * @category   WebUi
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
require_once 'EaseTWBootstrap.php';

/**
 * Průvodce
 *
 * @category WebUi
 * @package  EaseFrameWork
 * @author   Vitex <vitex@hippy.cz>
 */
class EasePageWizard extends EaseContainer
{

    /**
     * Pole kroků průvodce
     * @var array
     */
    public $steps = array();

    /**
     * Aktuálního krok
     * @var type
     */
    public $currentStepName = null;

    /**
     * ID aktuálního kroku
     * @var type
     */
    public $currentStepID = null;

    /**
     *
     * @var type
     */
    public $stepRequested = null;

    /**
     * Počítadlo kroků
     * @var type
     */
    public $stepCount = 0;

    /**
     * Značka pro předchozí
     * @var string
     */
    public static $prevSign = '❰❮❬';

    /**
     * Značka pro následující
     * @var string
     */
    public static $nextSign = '❭❯❱';

    /**
     *
     * @var type
     */
    public static $StepListDivider = ' <span class="divider">➠</span> ';

    /**
     * Objekt pro zobrazení sekvence stránek ve "wizard" stylu
     *
     * @param array $Steps       pole kroků: array(1=>'Název',2=>'Pozice',3=>'Ikona')
     * @param int   $CurrentStep aktuální přednastavený krok
     */
    public function __construct($Steps = null, $CurrentStep = null)
    {
        parent::__construct();
        if (!is_null($Steps)) {
            $this->addSteps($Steps);
        }
        if (is_null($CurrentStep)) {
            $this->setCurrentByID(1);
        } else {
            $this->setCurrentByID($CurrentStep);
        }
    }

    /**
     * Nastaví aktuální krok
     *
     * @param int $stepID ID aktuálního kroku
     *
     * @return null
     */
    public function setCurrentByID($stepID)
    {
        if (isset($this->steps[$stepID])) {
            $this->currentStepID = $stepID;
            $this->currentStepName = $this->steps[$stepID];

            return true;
        }

        return false;
    }

    /**
     * Redirect na dalsi krok
     *
     * @return null
     */
    public function jumpToNextStep()
    {
        $Params = array('StepRequested=' . $this->GetNextStepID());
        $RequestValuesToKeep = EaseShared::webPage()->RequestValuesToKeep;
        if (count($RequestValuesToKeep)) {
            foreach ($RequestValuesToKeep as $RequestName => $Request) {
                if (true !== $Request) {
                    $Params[$RequestName] = $RequestName . '=' . $Request;
                }
            }
        }
        EaseWebPage::redirect('?' . implode('&', $Params));
    }

    /**
     * Tlačítko s linkem na další krok
     *
     * @param string $Caption volitelný popisek
     *
     * @return EaseJQueryLinkButton
     */
    public function buttonToNextStep($Caption = null)
    {

        if (is_null($Caption)) {
            $Caption = $this->getStepName($this->getNextStepID()) . ' ' . self::$nextSign;
        }

        $Params = array('StepRequested=' . $this->GetNextStepID());
        $RequestValuesToKeep = EaseShared::webPage()->RequestValuesToKeep;
        if (count($RequestValuesToKeep)) {
            foreach ($RequestValuesToKeep as $RequestName => $Request) {
                if (true !== $Request) {
                    $Params[$RequestName] = $RequestName . '=' . $Request;
                }
            }
        }

        return new EaseTWBLinkButton('?' . implode('&', $Params), $Caption);
    }

    /**
     * Nastaví požadovaný krok
     *
     * @param int $stepID ID kroku
     *
     * @return int
     */
    public function setRequestedStep($stepID = null)
    {
        if (!$stepID) {
            $stepID = $this->easeShared->webPage()->getRequestValue('StepRequested');
        }
        if (array_key_exists($stepID, $this->steps)) {
            $this->currentStepID = $stepID;
            $this->currentStepName = $this->steps[$stepID];

            return $stepID;
        } else {
            return null;
        }
    }

    /**
     * Přidá krok wizarda
     *
     * @param string $Step název kroku
     *
     * @return boolean success
     */
    public function addStep($Step)
    {
        $this->stepCount++;
        $this->steps[$this->stepCount] = $Step;

        return true;
    }

    /**
     * Vloží více kroků
     *
     * @param array $Steps pole kroků: array(1=>'Název',2=>'Pozice',3=>'Ikona')
     *
     * @return int počet vložených kroků
     */
    public function addSteps($Steps)
    {
        $success = 0;
        foreach ($Steps as $Step) {
            if ($this->AddStep($Step)) {
                $success++;
            }
        }

        return $success;
    }

    /**
     * vykreslí odkazy na další kroky
     *
     * @return null
     */
    public function addNavigation()
    {
        $this->addItem($this->getNavigation());
    }

    /**
     * Vrací div s navigací
     * @return EaseHtmlDivTag
     */
    public function getNavigation()
    {
        $navigation = new EaseHtmlULTag();
        $PrevStep = $this->GetPrevStepID();
        if ($PrevStep) {
            $navigation->addItem(new EaseHtmlATag('?StepRequested=' . $PrevStep . '&' . $this->easeShared->webPage->getLinkParametersToKeep(), self::$prevSign . ' ' . $this->steps[$PrevStep]));
        }
        $NextStep = $this->GetNextStepID();
        if ($NextStep) {
            $navigation->addItem(new EaseHtmlATag('?StepRequested=' . $NextStep . '&' . $this->easeShared->webPage->getLinkParametersToKeep(), $this->steps[$NextStep] . ' ' . self::$nextSign));
        }

        return new EaseHtmlDivTag(null, $navigation, array('class' => 'pagination'));
    }

    /**
     * Vrací seznam kroků s odkazy
     *
     * @return EaseHtmlDivTag
     */
    public function getStepList()
    {
        $stepList = new EaseHtmlUlTag(null, array('class' => 'breadcrumb'));
        $StepsDone = 0;
        foreach ($this->steps as $StepID => $StepName) {
            $StepsDone++;
            if ($StepID == $this->currentStepID) {
                $Current = $stepList->addItem($StepName);
                $Current->setTagClass('active');
            } else {
                $stepList->addItem(new EaseHtmlATag('?StepRequested=' . $StepID . '&' . $this->easeShared->webPage->getLinkParametersToKeep(), $StepName));
            }
            if ($StepsDone != $this->stepCount) {
                $stepList->addItem(self::$StepListDivider);
            }
        }

        return $stepList;
    }

    /**
     * Vloží do sebe seznam kroků s odkazy
     *
     */
    public function addStepList()
    {
        $this->addItem($this->getStepList());
    }

    /**
     * Nastaví požadovaný krok podle názvu
     *
     * @param string $Step název kroku
     *
     * @return int
     */
    public function setCurrentStepByName($Step)
    {
        $StepsReversed = array_flip($this->steps);
        if (array_key_exists($Step, $StepsReversed)) {
            $this->currentStepID = $StepsReversed[$Step];
            $this->currentStepName = $Step;
        }

        return $this->currentStepID;
    }

    /**
     * Nastaví aktuální krok podle ID
     *
     * @param int $stepID ID kroku
     *
     * @return int právě nastavený krok
     */
    public function setCurrentStepByID($stepID)
    {
        if (array_key_exists($stepID, $this->steps)) {
            $this->currentStepID = $stepID;
            $this->currentStepName = $this->steps[$stepID];
        }

        return $this->currentStepID;
    }

    /**
     * Vrací název kroku
     *
     * @var int $StepID ID kroku
     *
     * @return string
     */
    public function getStepName($StepID = NULL)
    {
        if (is_null($StepID)) {
            return $this->currentStepName;
        } else {
            return $this->steps[$StepID];
        }
    }

    /**
     * Vrací ID aktuálního kroku
     *
     * @return int
     */
    public function getStepID()
    {
        return $this->currentStepID;
    }

    /**
     * Vrací první krok
     *
     * @return array
     */
    public function getFirstStep()
    {
        return reset($this->steps);
    }

    /**
     * Vrací následující ID kroku
     *
     * @return null
     */
    public function getNextStepID()
    {
        if (isset($this->steps[$this->currentStepID + 1])) {
            return $this->currentStepID + 1;
        } else {
            return null;
        }
    }

    /**
     * Posune průvodce o jeden krok dále
     */
    public function forward()
    {
        $this->setCurrentStepByID($this->getNextStepID());
    }

    /**
     * Posune průvodce o jeden krok zpět
     */
    public function backward()
    {
        $this->setCurrentStepByID($this->getPrevStepID());
    }

    /**
     * Vrací předcházející ID kroku
     *
     * @return null
     */
    public function getPrevStepID()
    {
        if (isset($this->steps[$this->currentStepID - 1])) {
            return $this->currentStepID - 1;
        } else {
            return null;
        }
    }

    /**
     * Vrací poslední krok
     *
     * @return string
     */
    public function getLastStep()
    {
        return end($this->steps);
    }

}
