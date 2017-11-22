<?php

namespace Ease\Html;

/**
 * HTML webPage head class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class HeadTag extends PairTag
{
    /**
     * Javascripts to render in page.
     *
     * @var array
     */
    public $javaScripts = null;

    /**
     * Css definitions.
     *
     * @var strig
     */
    public $cascadeStyles = null;

    /**
     * Content Charset
     * Znaková sada obsahu.
     *
     * @var string
     */
    public $charSet = 'utf-8';

    /**
     * Html HEAD tag with basic contents and skin support.
     *
     * @param mixed $content vkládaný obsah
     */
    public function __construct($content = null)
    {
        parent::__construct('head', null, $content);
        $this->addItem('<meta http-equiv="Content-Type" content="text/html; charset='.$this->charSet.'" />');
    }

    /**
     * Change name directly to head.
     *
     * @param string $objectName jméno objektu
     */
    public function setObjectName($objectName = null)
    {
        parent::setObjectName('head');
    }

    /**
     * Vykreslení bloku scriptu.
     *
     * @param string $javaScript vkládaný skript
     *
     * @return string
     */
    public static function jsEnclosure($javaScript)
    {
        return '
<script>
// <![CDATA[
'.$javaScript.'
// ]]>
</script>
';
    }

    /**
     * Vloží do hlavíčky název stránky.
     */
    public function finalize()
    {
        $this->addItem('<title>'.\Ease\Shared::webPage()->pageTitle.'</title>');
    }

    /**
     * Vykreslí hlavičku HTML stránky.
     */
    public function draw()
    {
        if (isset($this->easeShared->cascadeStyles) && count($this->easeShared->cascadeStyles)) {
            $cascadeStyles = [];
            foreach ($this->easeShared->cascadeStyles as $StyleRes => $Style) {
                if ($StyleRes == $Style) {
                    $this->addItem('<link href="'.$Style.'" rel="stylesheet" type="text/css" media="'.'screen'.'" />');
                } else {
                    $cascadeStyles[] = $Style;
                }
            }
            $this->addItem(
                '<style>'.implode(
                    '
', $cascadeStyles
                ).'</style>'
            );
        }
        if (isset($this->easeShared->javaScripts) && count($this->easeShared->javaScripts)) {
            ksort($this->easeShared->javaScripts, SORT_NUMERIC);
            $ODRStack = [];
            foreach ($this->easeShared->javaScripts as $Script) {
                $ScriptType = $Script[0];
                $ScriptBody = substr($Script, 1);
                switch ($ScriptType) {
                    case '#':
                        $this->addItem(
                            '
'.'<script src="'.$ScriptBody.'"></script>'
                        );
                        break;
                    case '@':
                        $this->addItem(self::jsEnclosure($ScriptBody));
                        break;
                    case '$':
                        $ODRStack[] = $ScriptBody;
                        break;
                }
            }
            if (count($ODRStack)) {
                $this->addItem(
                    self::jsEnclosure(
                        '$(document).ready(function () { '.implode(
                            '
', $ODRStack
                        ).' });'
                    )
                );
            }
        }
        parent::draw();
    }
}
