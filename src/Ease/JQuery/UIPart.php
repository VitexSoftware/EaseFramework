<?php

namespace Ease\JQuery;

/**
 * jQuery UI common class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class UIPart extends Part {

    public function __construct() {
        parent::__construct();
        UIPart::jQueryze();
    }

    /**
     * Opatří objekt vším potřebným pro funkci jQueryUI
     *
     * @param \Ease\Page|mixed $EaseObject objekt k opatření jQuery závislostmi
     */
    public static function jQueryze() {
        parent::jQueryze();
        $webPage = \Ease\Shared::webPage();
        $webPage->includeJavaScript('jquery-ui/jquery-ui.js', 1, true);
        $jQueryUISkin = \Ease\Shared::instanced()->getConfigValue('jQueryUISkin');
        if ($jQueryUISkin) {
            $webPage->includeCss('jquery-ui-themes/' . self::getSkinName() . '/jquery-ui.css', true);
        } else {
            $webPage->includeCss('jquery-ui/css/smoothness/jquery-ui.css', true);
        }
    }

    /**
     * Vrací název aktuálně používaného jQueryUI skinu
     * @return type
     */
    public static function getSkinName() {
        $jQueryUISkin = \Ease\Shared::instanced()->getConfigValue('jQueryUISkin');
        if ($jQueryUISkin) {
            return $jQueryUISkin;
        } else {
            if (isset(\Ease\Shared::webPage()->jQueryUISkin)) {
                return \Ease\Shared::webPage()->jQueryUISkin;
            }
        }
        return null;
    }

}
