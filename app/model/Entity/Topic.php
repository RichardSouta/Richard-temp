<?php

namespace App\Model\Entity;

use App\Model\Repository\TopicRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="TopicRepository")
 */
class Topic
{
    public function __construct()
    {
        $this->categories = new ArrayCollection;
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
    protected $title;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="topics")
     * @ORM\JoinTable(name="category_topic")
     *
     * @var Category[]
     */
    private $categories;

}