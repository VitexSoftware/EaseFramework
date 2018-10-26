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
     * @var AppMenu
     */
    public $navBar = null;

    /**
     * Page main area.
     *
     * @var \Ease\TWB\Container
     */
    public $container = null;

    /**
     * Stránka aplikace.
     *
     * @param string    $pageTitle
     */
    public function __construct($pageTitle = null)
    {
        parent::__construct($pageTitle);
        $this->navBar    = $this->addItem(
            new AppMenu('menu', 'ExApp', ['class' => 'navbar-fixed-top'])
        );
        $this->addItem(new \Ease\TWB\StatusMessages());
        $this->container = $this->addItem(
            new \Ease\Html\DivTag(null, ['class' => 'container'])
        );
    }

}