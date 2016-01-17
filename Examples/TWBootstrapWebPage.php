<?php

/**
 * Ukázková webstránka pro TwitterBootstrap
 *
 * @package    EaseFrameWork
 * @subpackage Examples
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
namespace Ease;

require_once '../vendor/autoload.php';

/**
 * Description of EaseBiWebPage
 *
 * @author vitex
 */
class EaseExAppWebPage extends TWB\WebPage
{

    /**
     * Applicaton Menu
     * @var EaseBiMenu
     */
    public $navBar = null;

    /**
     * Stránka aplikace
     *
     * @param string   $pageTitle
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
            new Html\Div(null, array('class' => 'container'))
        );
    }

}

class EaseExAppStatusMessages extends Html\Div
{

    /**
     * Blok stavových zpráv
     */
    public function __construct()
    {
        $properties['class'] = 'well';
        $properties['id'] = 'StatusMessages';
        $properties['title'] = _('kliknutím skryjete zprávy');
        $properties['style'] = 'padding-top: 40px; padding-bottom: 0px;';
        parent::__construct(null, null, $properties);
        $this->addJavaScript(
            '$("#StatusMessages").click(function () {
            $("#StatusMessages").fadeTo("slow",0.25).slideUp("slow");
            });', 3, true
        );
    }

    /**
     * Vypíše stavové zprávy
     */
    public function draw()
    {
        $statusMessages = trim($this->webPage->getStatusMessagesAsHtml());
        if ($statusMessages) {
            parent::addItem($statusMessages);
        } else {
            $this->suicide();
        }
        parent::draw();
    }

}

class EaseExAppMenu extends TWB\Navbar
{

    /**
     * Menu aplikace
     *
     * @param string           $name
     * @param EaseImage|string $Content
     * @param array            $properties
     */
    public function __construct($name = null, $brand = null, $properties = null)
    {
        parent::__construct($name, $brand, $properties);
        $this->addMenuItem(new Html\ATag('http://v.s.cz/ease.php', _('Homepage')));
        $this->addMenuItem(new Html\ATag('http://l.q.cz/', _('LinkQuick')));

        $this->addDropDownMenu(_('Systém'), array(
            'settings.php' => '<i class="icon-list"></i>&nbsp;' . _('Nastavení'),
            'shutdown.php' => '<i class="icon-list"></i>&nbsp;' . _('Vypnout zařízení')
                )
        );

        $this->addDropDownMenu(_('Informace'), array(
            'log.php' => '<i class="icon-list"></i>&nbsp;' . _('System log'),
            'http://h.v.s.cz/' => '<i class="icon-list"></i>&nbsp;' . _('Hosting')
                )
        );
    }

}

/**
 * Instancujeme objekt webové stránky
 */
$oPage = new EaseExAppWebPage(_('Twitter Bootstrap'));

$oPage->addStatusMessage(_('debug'), 'debug');
$oPage->addStatusMessage(_('info'), 'info');
$oPage->addStatusMessage(_('success'), 'success');
$oPage->addStatusMessage(_('warning'), 'warning');
$oPage->addStatusMessage(_('error'), 'error');

$oPage->addItem(new TWB\LinkButton('./', _('Zpět na přehled příkladů'), 'info'));

/**
 * Vyrendrování stránky
 */
$oPage->draw();
