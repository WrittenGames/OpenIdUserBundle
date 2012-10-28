<?php

namespace WG\OpenIdUserBundle\Model;

use FOS\UserBundle\Model\Group as FosGroup;

/**
 * Storage agnostic group object
 */
abstract class Group extends FosGroup
{
    /**
     * @var string
     */
    protected $slug;

    /**
     * @var datetime
     */
    protected $updated;

    /**
     * @var datetime
     */
    protected $created;
}
