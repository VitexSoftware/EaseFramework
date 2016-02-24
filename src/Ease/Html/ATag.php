<?php

namespace Ease\Html;

/**
 * HTML hyperling class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class ATag extends PairTag
{
    /**
     * zobrazí HTML odkaz.
     *
     * @param string $href       url odkazu
     * @param mixed  $contents   vkládaný obsah
     * @param array  $properties parametry tagu
     */
    public function __construct($href, $contents = null, $properties = null)
    {
        if (!is_array($properties)) {
            $properties = [];
        }
        if (!is_null($href)) {
            $properties['href'] = $href;
        }
        parent::__construct('a', $properties, $contents);
    }

    /**
     * Ošetření perzistentních hodnot.
     */
    public function afterAdd()
    {
        if (isset($this->webPage->requestValuesToKeep) && is_array($this->webPage->requestValuesToKeep) && count($this->webPage->requestValuesToKeep)) {
            foreach ($this->webPage->requestValuesToKeep as $KeepName => $KeepValue) {
                if ($KeepValue == true) {
                    continue;
                }
                $Keep = urlencode($KeepName).'='.urlencode($KeepValue);
                if (!strstr($this->tagProperties['href'], urlencode($KeepName).'=')) {
                    if (strstr($this->tagProperties['href'], '?')) {
                        $this->tagProperties['href'] .= '&'.$Keep;
                    } else {
                        $this->tagProperties['href'] .= '?'.$Keep;
                    }
                }
            }
        }
    }
}
