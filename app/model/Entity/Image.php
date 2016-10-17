<?php

namespace App\Model\Entity;

use App\Model\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\ImageRepository")
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Collectible", inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     * @var Collectible
     */
    private $collectible;

    /**
     * @return Collectible
     */
    public function getCollectible()
    {
        return $this->collectible;
    }

    /**
     * @param Collectible $collectible
     */
    public function setCollectible($collectible)
    {
        $this->collectible = $collectible;
    }

    public function __toString()
    {
        return (string) $this->id;
    }

}