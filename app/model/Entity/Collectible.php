<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Collectible
{
    public function __construct()
    {
        $this->images = new ArrayCollection;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $origin;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="collectible", cascade={"persist", "remove"})
     * @var Image[]
     */
    private $images;
}