<?php
/**
 * Twitter Bootrstap Container.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 */

namespace Ease\TWB;

/**
 * Carousel for Twitter Bootstrap.
 */
class Carousel extends \Ease\Html\DivTag
{
    /**
     * Carousel name.
     *
     * @var string
     */
    public $name = null;

    /**
     * Carousel's inner div.
     *
     * @var \Ease\Html\DivTag
     */
    public $inner = null;

    /**
     * Carousel's inner div.
     *
     * @var \Ease\Html\OlTag
     */
    public $indicators = null;

    /**
     * Which slide is active ?
     *
     * @var int
     */
    public $active = null;

    /**
     * Twitter bootstrap Carousel.
     *
     * @url   http://getbootstrap.com/javascript/#carousel
     *
     * @param string $name
     * @param array  $properties ['data-ride'=>'carousel'] means autorun
     */
    public function __construct($name = null, $properties = [])
    {
        parent::__construct(null, $properties);
        $this->name       = $this->setTagID($name);
        $this->setTagClass('carousel slide');
        $this->indicators = $this->addItem(new \Ease\Html\OlTag(null,
                ['class' => 'carousel-indicators']));
        $this->inner      = $this->addItem(new \Ease\Html\DivTag(null,
                ['class' => 'carousel-inner', 'role' => 'listbox']));
    }

    /**
     * Carousel Slide.
     *
     * @param mixed|ImgTag $slide      body Image or something else
     * @param string       $capHeading
     * @param string       $caption
     * @param bool         $default    show slide by default
     */
    public function addSlide($slide, $capHeading = '', $caption = '',
                             $default = false)
    {
        $item = new \Ease\Html\DivTag($slide, ['class' => 'item']);
        if ($default) {
            $item->addTagClass('active');
        }

        if ($capHeading || $caption) {
            $cpt = $item->addItem(new \Ease\Html\DivTag(null,
                    ['class' => 'carousel-caption']));
            if ($capHeading) {
                $cpt->addItem(new \Ease\Html\H4Tag($capHeading));
            }
            if ($caption) {
                $cpt->addItem(new \Ease\Html\PTag($caption));
            }
        }
        $to        = $this->indicators->getItemsCount();
        $indicator = new \Ease\Html\LiTag(null,
            ['data-target' => '#'.$this->name, 'data-slide-to' => $to]);
        if ($default) {
            $indicator->addTagClass('active');
            $this->active = $to;
        }
        $this->indicators->addItem($indicator);
        $this->inner->addItem($item);
    }

    /**
     * Add Navigation buttons.
     */
    public function finalize()
    {
        Part::twBootstrapize();
        if (is_null($this->active) && $this->getItemsCount() ) { //We need one slide active
            $this->indicators->getFirstPart()->setTagClass('active');
            $this->inner->getFirstPart()->addTagClass('active');
        }
        $this->inner->addItem(
            new \Ease\Html\ATag(
                '#'.$this->getTagID(),
                [
                new \Ease\Html\Span(null,
                    ['class' => 'glyphicon glyphicon-chevron-left', 'aria-hidden' => 'true']),
                new \Ease\Html\Span(_('Previous'), ['class' => 'sr-only']),
                ],
                ['class' => 'left carousel-control', 'data-slide' => 'prev', 'role' => 'button']
            )
        );
        $this->inner->addItem(
            new \Ease\Html\ATag(
                '#'.$this->getTagID(),
                [
                new \Ease\Html\Span(null,
                    ['class' => 'glyphicon glyphicon-chevron-right', 'aria-hidden' => 'true']),
                new \Ease\Html\Span(_('Next'), ['class' => 'sr-only']),
                ],
                ['class' => 'right carousel-control', 'data-slide' => 'next', 'role' => 'button']
            )
        );
        if ($this->getTagProperty('data-ride') != 'carousel') {
            $this->addJavaScript('$(\'#'.$this->name.'\').carousel();', null,
                true);
        }
    }
}
