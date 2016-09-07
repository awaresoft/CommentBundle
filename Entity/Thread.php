<?php

namespace Awaresoft\CommentBundle\Entity;

use Application\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\CommentBundle\Entity\BaseThread as BaseThread;

/**
 * @ORM\MappedSuperclass
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Thread extends BaseThread
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    protected $class;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", cascade={"persist"}, inversedBy="threads")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var User
     */
    protected $owner;

    /**
     * @ORM\OneToMany(targetEntity="Application\CommentBundle\Entity\Comment", cascade={"persist", "remove"}, mappedBy="thread")
     *
     * @var Comment[]|ArrayCollection
     */
    protected $comments;

    /**
     * Thread constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }
}