<?php

namespace Ease\Example;

/**
 * Description of EaseBiWebPage.
 *
 * @author vitex
 */
class EaseExAppWebPage extends \Ease\TWB\WebPage
{

    /**
     * Applicaton Menu.
     *
     * @var EaseBiMenu
     */
    public $navBar = null;

    /**
     * Stránka aplikace.
     *
     * @param string    $pageTitle
     * @param Ease\User $userObject
     */
    public function __construct($pageTitle = null, &$userObject = null)
    {
        parent::__construct($pageTitle, $userObject);
        $this->navBar = $this->addItem(
            new EaseExAppMenu('menu', 'ExApp', array('class' => 'navbar-fixed-top'))
        );
        $this->addItem(new EaseExAppStatusMessages());
        $this->container = $this->addItem(
            new \Ease\Html\Div(null, array('class' => 'container'))
        );
    }
}
