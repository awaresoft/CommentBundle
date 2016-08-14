<?php

namespace Awaresoft\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Model\SignedVoteInterface;
use FOS\CommentBundle\Model\VotableCommentInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\CommentBundle\Entity\Vote as BaseVote;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Vote extends BaseVote implements SignedVoteInterface
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
    protected $voter;

    /**
     * @ORM\ManyToOne(targetEntity="Application\CommentBundle\Entity\Comment", cascade={"persist"}, inversedBy="votes")
     *
     * @Assert\NotNull()
     *
     * @var Comment
     */
    protected $comment;

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
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param VotableCommentInterface $comment
     */
    public function setComment(VotableCommentInterface $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Gets the owner of the vote
     *
     * @return UserInterface
     */
    public function getVoter()
    {
        return $this->voter;
    }

    /**
     * Sets the owner of the vote
     *
     * @param UserInterface $voter
     */
    public function setVoter(UserInterface $voter)
    {
        $this->voter = $voter;
    }
}