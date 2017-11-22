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
    public function __construct($href, $contents = null, $properties = [])
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
        $valuesToKeep = \Ease\Shared::webPage()->requestValuesToKeep;
        if (isset($valuesToKeep) && is_array($valuesToKeep) && count($valuesToKeep)) {
            foreach ($valuesToKeep as $keepName => $keepValue) {
                if ($keepValue === true) {
                    continue;
                }
                $keep = urlencode($keepName).'='.urlencode($keepValue);
                if (!strstr($this->tagProperties['href'],
                        urlencode($keepName).'=')) {
                    if (strstr($this->tagProperties['href'], '?')) {
                        $this->tagProperties['href'] .= '&'.$keep;
                    } else {
                        $this->tagProperties['href'] .= '?'.$keep;
                    }
                }
            }
        }
    }
}
