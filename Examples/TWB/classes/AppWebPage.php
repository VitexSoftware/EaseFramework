<?php

namespace Ease\Example\TWB;

/**
 * Description of EaseBiWebPage.
 *
 * @author vitex
 */
class AppWebPage extends \Ease\TWB\WebPage
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
        $this->navBar    = $this->addItem(
            new AppMenu('menu', 'ExApp', ['class' => 'navbar-fixed-top'])
        );
        $this->addItem(new AppStatusMessages());
        $this->container = $this->addItem(
            new \Ease\Html\Div(null, ['class' => 'container'])
        );
    }
}