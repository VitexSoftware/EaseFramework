<?php

namespace Ease\Html;

/**
 * Fragment skriptu ve stránce
 *
 * @author Vitex <vitex@hippy.cz>
 */
class ScriptTag extends Ease\Html\PairTag
{

    /**
     * Include JS code into page
     *
     * @param string $cData        vkládaná data
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return EaseScriptTag
     */
    function &addItem($cData, $pageItemName = null)
    {
        return parent::addItem('
//<![CDATA[
' . $cData . '
// ]]>
', $pageItemName);
    }

    /**
     * fragment skriptu ve stránce
     *
     * @param string $content text scriptu
     */
    public function __construct($content = '', $properties = NULL)
    {
        parent::__construct('script', $properties);
        if ($content) {
            $this->setTagName(md5($content));
            $this->addItem($content);
        }
    }

}
