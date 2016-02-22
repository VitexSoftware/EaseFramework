<?php

namespace Ease\Example;

class AppMenu extends \Ease\TWB\Navbar
{

    /**
     * Menu aplikace.
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

        $this->addDropDownMenu(
            _('Systém'),
            array(
            'settings.php' => '<i class="icon-list"></i>&nbsp;' . _('Settings'),
            'shutdown.php' => '<i class="icon-list"></i>&nbsp;' . _('Vypnout zařízení'),
                )
        );

        $this->addDropDownMenu(
            _('Informace'),
            array(
            'log.php' => '<i class="icon-list"></i>&nbsp;' . _('System log'),
            'http://h.v.s.cz/' => '<i class="icon-list"></i>&nbsp;' . _('Hosting'),
                )
        );
    }
}
