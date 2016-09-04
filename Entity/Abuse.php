<?php

namespace Awaresoft\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass()
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Abuse
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
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     *
     * @var UserInterface
     */
    protected $declarant;

    /**
     * @ORM\ManyToOne(targetEntity="Application\CommentBundle\Entity\Comment", inversedBy="abuses")
     *
     * @Assert\NotNull()
     *
     * @var Comment
     */
    protected $comment;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var boolean
     */
    protected $solved;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * Abuse constructor.
     */
    public function __construct()
    {
        $this->solved = false;
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return UserInterface
     */
    public function getDeclarant()
    {
        return $this->declarant;
    }

    /**
     * @param UserInterface $declarant
     *
     * @return Abuse
     */
    public function setDeclarant(UserInterface $declarant): Abuse
    {
        $this->declarant = $declarant;

        return $this;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     *
     * @return Abuse
     */
    public function setComment(Comment $comment): Abuse
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSolved(): bool
    {
        return $this->solved;
    }

    /**
     * @param boolean $solved
     *
     * @return Abuse
     */
    public function setSolved(bool $solved): Abuse
    {
        $this->solved = $solved;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Abuse
     */
    public function setCreatedAt(\DateTime $createdAt = null): Abuse
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return Abuse
     */
    public function setUpdatedAt(\DateTime $updatedAt = null): Abuse
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return (string) $this->id;
    }
}