<?php

namespace AppBundle\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Link
{
    /**
     * @Required()
     *
     * @var string
     */
    public $name;

    /**
     * @Required
     *
     * @var string
     */
    public $route;

    public $params = array();
}