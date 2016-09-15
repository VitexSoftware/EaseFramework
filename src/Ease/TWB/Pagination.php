<?php

namespace Ease\TWB;

class Pagination extends \Ease\Html\UlTag
{
    /**
     * Fragment adresy pro stránkování.
     *
     * @var string
     */
    public $url = '?page=';

    /**
     * Stránkování Twitter Bootrstap.
     *
     * @param int    $pages   celkový počet stránek
     * @param int    $current aktuální stránka
     * @param string $url     Fragment adresy
     */
    public function __construct($pages, $current = 0, $url = '?page=')
    {
        $this->url = $url;
        parent::__construct(null, ['class' => 'pagination']);
        if ($current == 0) {
            $this->addPage('#', Part::glyphIcon('fast-backward'), 'disabled');
        } else {
            $this->addPage(0, Part::glyphIcon('fast-backward'));
        }
        if ($current == 0) {
            $this->addPage('#', Part::glyphIcon('chevron-left'), 'disabled');
        } else {
            $this->addPage($current - 1, Part::glyphIcon('chevron-left'));
        }
        for ($page = 0; $page <= $pages - 1; ++$page) {
            //Stavajici
            if ($current == $page) {
                $this->addPage($page, $page + 1, 'active');
            } else {
                $this->addPage($page, $page + 1);
            }
        }
        if ($current >= $pages - 1) {
            $this->addPage('#', Part::glyphIcon('chevron-right'), 'disabled');
        } else {
            $this->addPage($current + 1, Part::glyphIcon('chevron-right'));
        }
        if ($current >= $pages - 1) {
            $this->addPage('#', Part::glyphIcon('fast-forward'), 'disabled');
        } else {
            $this->addPage($pages - 1, Part::glyphIcon('fast-forward'));
        }
    }

    /**
     * Přidá krok strankování.
     *
     * @param int    $page
     * @param string $label
     */
    public function addPage($page, $label = null, $style = null)
    {
        $link = $this->url.$page;
        if ($style) {
            $this->addItemSmart(new \Ease\Html\ATag($link, $label),
                ['class' => $style]);
        } else {
            $this->addItemSmart(new \Ease\Html\ATag($link, $label));
        }
    }
}
