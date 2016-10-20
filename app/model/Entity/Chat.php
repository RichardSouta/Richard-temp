<?php

namespace App\Model\Entity;

use App\Model\Repository\ChatRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\ChatRepository")
 */
class Chat
{
    public function __construct()
    {
        $this->messages = new ArrayCollection;
        $this->users = new ArrayCollection;
        $this->createdDateTime = new \DateTime();
    }

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="chat", cascade={"persist", "remove"})
     * @var Message[]
     */
    private $messages;

    /**
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ArrayCollection $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    public function addMessage(Message $message)
    {
        $message->setChat($this);
        $this->messages[] = $message;
    }

    public function addUser(User $user)
    {
        $user->addChat($this);
        $this->users[] = $user;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="chats")
     * @ORM\JoinTable(name="chat_user")
     *
     * @var User[]
     */
    private $users;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdDateTime;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
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
     */
    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }



}