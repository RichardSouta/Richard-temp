<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CommentRepository")
 */
class Message
{
    public function __construct()
    {
        $this->createdDateTime = new \DateTime();
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
    protected $text;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdDateTime;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity="Chat", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * @var Chat
     */
    private $chat;

    /**
     * @return Chat
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param Chat $chat
     */
    public function setChat($chat)
    {
        $this->chat = $chat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Message
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedDateTime()
    {
        return $this->createdDateTime;
    }

    /**
     * @param mixed $createdDateTime
     * @return Message
     */
    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
        return $this;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     * @return Message
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

}