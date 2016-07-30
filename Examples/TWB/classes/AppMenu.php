<?php

namespace Ease\Example\TWB;

use Ease\Html\ATag;

class AppMenu extends \Ease\TWB\Navbar
{

    /**
     * Application Menu.
     *
     * @param string           $name
     * @param string|\Ease\Html\ImgTag           $brand  Boostrap Menu Brand
     * @param array            $properties
     */
    public function __construct($name = null, $brand = null, $properties = null)
    {
        parent::__construct($name, $brand, $properties);
        $this->addMenuItem(new ATag('http://v.s.cz/ease.php', _('Homepage')));
        $this->addMenuItem(new ATag('http://l.q.cz/', _('LinkQuick')));

        $this->addDropDownMenu(
            _('Systém'),
            [
            'settings.php' => '<i class="icon-list"></i>&nbsp;'._('Settings'),
            'shutdown.php' => '<i class="icon-list"></i>&nbsp;'._('Shutdown'),
            ]
        );

        $this->addDropDownMenu(
            _('Informace'),
            [
            'log.php' => '<i class="icon-list"></i>&nbsp;'._('System log'),
            'http://h.v.s.cz/' => '<i class="icon-list"></i>&nbsp;'._('Hosting'),
            ]
        );
    }

}
