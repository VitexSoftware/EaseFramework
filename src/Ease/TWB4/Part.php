<?php
/**
 * Twitter Bootstrap4 common class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
namespace Ease\TWB4;

class Part extends \Ease\JQuery\Part
{
    /**
     * Vložení náležitostí pro twitter bootstrap.
     */
    public function __construct()
    {
        parent::__construct();
        self::twBootstrapize();
    }

    /**
     * Opatří objekt vším potřebným pro funkci bootstrapu.
     */
    public static function twBootstrapize()
    {
        parent::jQueryze();
        $webPage = \Ease\Shared::webPage();
        $webPage->includeJavaScript('twitter-bootstrap4/js/bootstrap.js', 1,
            true);
        if (isset($webPage->mainStyle)) {
            $webPage->includeCss($webPage->mainStyle, true);
        }

        return true;
    }

}
