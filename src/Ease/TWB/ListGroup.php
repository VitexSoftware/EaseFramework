<?php

namespace Ease\TWB;

class ListGroup extends \Ease\Html\UlTag
{
    /**
     * Vytvori ListGroup.
     *
     * @link  http://getbootstrap.com/components/#list-group ListGroup
     *
     * @param mixed $ulContents položky seznamu
     * @param array $properties parametry tagu
     */
    public function __construct($ulContents = null, $properties = [])
    {
        parent::__construct($ulContents, $properties);
        $this->addTagClass('list-group');
    }

    /**
     * Every item id added in \Ease\Html\LiTag envelope.
     *
     * @param mixed  $pageItem   obsah vkládaný jako položka výčtu
     * @param string $properties Vlastnosti LI tagu
     *
     * @return mixed
     */
    public function &addItemSmart($pageItem, $properties = [])
    {
        $item = parent::addItemSmart($pageItem, $properties);
        $item->addTagClass('list-group-item');

        return $item;
    }
}
