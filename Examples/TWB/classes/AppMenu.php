<?php

namespace Ease\Example\TWB;

use Ease\Html\ATag;

class AppMenu extends \Ease\TWB\Navbar
{

    /**
     * Application Menu.
     *
     * @param string $name
     * @param mixed  $brand      Boostrap Menu Brand
     * @param array  $properties page menu div tag properties
     */
    public function __construct($name = null, $brand = null, $properties = [])
    {
        parent::__construct($name, $brand, $properties);
        $this->addMenuItem(new ATag('http://v.s.cz/ease.php', _('Homepage')));
        $this->addMenuItem(new ATag('http://l.q.cz/', _('LinkQuick')));

        $this->addDropDownMenu(
            _('System'),
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
