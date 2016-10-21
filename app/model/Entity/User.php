<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\Strings as Strings;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\UserRepository")
 */
class User
{
    public function __construct()
    {
        $this->collectibles = new ArrayCollection;
        $this->regDateTime = new \DateTime();
        $this->topics = new ArrayCollection;
        $this->confirmedEmail = Strings::random(15, '0-9a-zA-Z');
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Collectible", mappedBy="user", cascade={"persist", "remove"})
     * @var Collectible[]
     */
    private $collectibles;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $surname;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $confirmedEmail;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $facebook;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $notification;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $regDateTime;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $phone;

    /**
     * @ORM\OneToMany(targetEntity="Topic", mappedBy="user", cascade={"persist", "remove"})
     * @var Topic[]
     */
    private $topics;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="user", cascade={"persist", "remove"})
     * @var Comment[]
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="sender", cascade={"persist", "remove"})
     * @var Message[]
     */
    private $messages;

    /**
     * @ORM\ManyToMany(targetEntity="Chat", mappedBy="users", cascade={"persist", "remove"})
     * @var Chat[]
     */
    private $chats;

    public function addChat(Chat $chat)
    {
        $this->chats[] = $chat;
    }

    /**
     * @return Chat[]
     */
    public function getChats()
    {
        return $this->chats;
    }

    /**
     * @param Chat[] $chats
     */
    public function setChats($chats)
    {
        $this->chats = $chats;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Message[] $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return mixed
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param mixed $facebook
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @return Topic[]
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * @param Topic[] $topics
     */
    public function setTopics($topics)
    {
        $this->topics = $topics;
    }

    /**
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment[] $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
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
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Collectible[]
     */
    public function getCollectibles()
    {
        return $this->collectibles;
    }

    /**
     * @param Collectible[] $collectibles
     * @return User
     */
    public function setCollectibles($collectibles)
    {
        $this->collectibles = $collectibles;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param mixed $surname
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfirmedEmail()
    {
        return $this->confirmedEmail;
    }

    /**
     * @param mixed $confirmedEmail
     * @return User
     */
    public function setConfirmedEmail($confirmedEmail)
    {
        $this->confirmedEmail = $confirmedEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param mixed $notification
     * @return User
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegDateTime()
    {
        return $this->regDateTime;
    }

    /**
     * @param mixed $regDateTime
     * @return User
     */
    public function setRegDateTime($regDateTime)
    {
        $this->regDateTime = $regDateTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return User
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function __toString()
    {
        return $this->username;
    }

    public function toIdentity()
    {
        $identity = [];
        $identity['confirmedEmail'] = $this->confirmedEmail;
        $identity['username'] = $this->username;
        $identity['id'] = $this->id;
        return $identity;
    }

    public function getPicture()
    {
        return "images/user/$this->id-360";
    }


}