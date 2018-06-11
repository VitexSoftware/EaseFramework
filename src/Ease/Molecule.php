<?php
/**
 * Something between Atom and Sand
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2017 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Description of Molecule
 *
 * @author vitex
 */
class Molecule extends Atom
{

    /**
     * Here Live Shared Object
     * 
     * @var Shared 
     */
    public $easeShared = null;


    /**
     * Molecule Can Log and Use Shared
     */
    public function __construct()
    {
        $this->easeShared = Shared::singleton();
        $this->logger     = $this->easeShared->logger();

        $this->setObjectName();
    }

    /**
     * Nastaví jméno objektu.
     *
     * @param string $objectName
     *
     * @return string Jméno objektu
     */
    public function setObjectName($objectName = null)
    {
        if (empty($objectName)) {
            $this->objectName = get_class($this);
        } else {
            $this->objectName = $objectName;
        }

        return $this->objectName;
    }

    /**
     * Vrací jméno objektu.
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Set up one of properties
     *
     * @param array  $options  array of given properties
     * @param string $name     name of property to process
     * @param string $constant load default property value from constant
     */
    public function setupProperty($options, $name, $constant = null)
    {
        if (isset($options[$name])) {
            $this->$name = $options[$name];
        } else {
            if (is_null($this->$name) && !empty($constant) && defined($constant)) {
                $this->$name = constant($constant);
            }
        }
    }

    /**
     * Zapíše zprávu do logu.
     *
     * @param string $message zpráva
     * @param string $type    typ zprávy (info|warning|success|error|*)
     *
     * @return bool byl report zapsán ?
     */
    public function addToLog($message, $type = 'message')
    {
        $logged = false;
        if (isset($this->logger) && is_object($this->logger)) {
            $this->logger->addToLog($this->getObjectName(), $message, $type);
        } else {
            $logged = Shared::logger()->addToLog($this->getObjectName(),
                $message, $type);
        }

        return $logged;
    }

    /**
     * Přidá zprávu do sdíleného zásobníku pro zobrazení uživateli.
     *
     * @param string $message  Text zprávy
     * @param string $type     Fronta zpráv (warning|info|error|success)
     *
     * @return
     */
    public function addStatusMessage($message, $type = 'info')
    {
        return $this->easeShared->takeMessage(new Logger\Message($message,
                    $type, get_class($this)));
    }

    /**
     * Obtain Status Messages
     *
     * @param bool $clean Remove messages from strack ?
     *
     * @return array
     */
    public function getStatusMessages($clean = false)
    {
        $messages = $this->easeShared->getStatusMessages();
        if ($clean) {
            $this->easeShared->cleanMessages();
        }
        return $messages;
    }

    /**
     * Pro serializaci připraví vše.
     *
     * @return array
     */
    public function __sleep()
    {
        $objectVarsRaw = get_object_vars($this);
        $objectVars    = array_combine(array_keys($objectVarsRaw),
            array_keys($objectVarsRaw));
        $parent        = get_parent_class(__CLASS__);
        if (method_exists($parent, '__sleep')) {
            $parentObjectVars = parent::__sleep();
            if (is_array($parentObjectVars)) {
                $objectVars = array_merge($objectVars, $parentObjectVars);
            }
        }
        return $objectVars;
    }

    /**
     * Akce po probuzení ze serializace.
     */
    public function __wakeup()
    {
        $this->setObjectName();
    }
}
