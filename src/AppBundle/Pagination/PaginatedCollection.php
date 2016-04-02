<?php

namespace AppBundle\Pagination;

use AppBundle\Annotation\Link;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Link(
 *     "self",
 *     url = "object.getUrl('self')"
 * )
 * 
 * @Link(
 *     "first",
 *     url = "object.getUrl('first')"
 * )
 *
 * @Link(
 *     "last",
 *     url = "object.getUrl('last')"
 * )
 *
 * @Link(
 *     "next",
 *     url = "object.getUrl('next')"
 * )
 *
 * @Link(
 *     "previous",
 *     url = "object.getUrl('previous')"
 * )
 *
 * @Serializer\ExclusionPolicy("all")
 */

class PaginatedCollection
{

    /**
     * @Serializer\Expose()
     *
     * @var
     */
    private $items;

    /**
     * @Serializer\Expose()
     *
     * @var
     */
    private $total;

    /**
     * @Serializer\Expose()
     *
     * @var
     */
    private $count;

    /**
     * @var array
     */
    private $links = array();

    /**
     * PaginatedCollection constructor.
     * @param $items
     * @param $total
     */
    public function __construct($items, $total)
    {
        $this->items = $items;
        $this->total = $total;
        $this->count = count($items);
    }

    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }

    public function getUrl($ref)
    {
        return isset($this->_links[$ref]) ? $this->_links[$ref] : '';
    }
}