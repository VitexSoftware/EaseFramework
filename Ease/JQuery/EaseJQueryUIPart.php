<?php

/**
 * jQuery UI common class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseJQueryUIPart extends EaseJQueryPart
{
    public function __construct()
    {
        parent::__construct();
        EaseJQueryUIPart::jQueryze();
    }
    /**
     * Opatří objekt vším potřebným pro funkci jQueryUI
     *
     * @param EasePage|mixed $EaseObject objekt k opatření jQuery závislostmi
     */
    public static function jQueryze()
    {
        parent::jQueryze();
        $webPage = EaseShared::webPage();
        $webPage->includeJavaScript('jquery-ui/jquery-ui.js', 1, true);
        $jQueryUISkin = EaseShared::instanced()->getConfigValue('jQueryUISkin');
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
    public static function getSkinName()
    {
        $jQueryUISkin = EaseShared::instanced()->getConfigValue('jQueryUISkin');
        if ($jQueryUISkin) {
            return $jQueryUISkin;
        } else {
            if (isset(EaseShared::webPage()->jQueryUISkin)) {
                return EaseShared::webPage()->jQueryUISkin;
            }
        }
        return null;
    }
}