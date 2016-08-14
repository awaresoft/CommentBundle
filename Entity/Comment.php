<?php

namespace Awaresoft\CommentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Model\VotableCommentInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\CommentBundle\Entity\BaseComment as BaseComment;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Comment extends BaseComment implements VotableCommentInterface
{
    const DEFAULT_ANONYMOUS = 'anonymous';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", cascade={"persist"})
     *
     * @var UserInterface
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="Application\CommentBundle\Entity\Thread", cascade={"persist"}, inversedBy="comments")
     *
     * @Assert\NotNull()
     *
     * @var Thread
     */
    protected $thread;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $score = 0;

    /**
     * @inheritdoc
     *
     * @Assert\NotBlank(groups={"own"})
     * @Assert\Length(min="4", max="1000", groups={"own"})
     */
    protected $body;

    /**
     * @ORM\OneToMany(targetEntity="Application\CommentBundle\Entity\Abuse", mappedBy="comment", cascade={"persist", "remove"})
     *
     * @var ArrayCollection
     */
    protected $abuses;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $abusesCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="Application\CommentBundle\Entity\Vote", mappedBy="comment", cascade={"persist", "remove"})
     *
     * @var ArrayCollection
     */
    protected $votes;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     *
     * @Assert\Length(max="1000", groups={"own"})
     *
     * @var string
     */
    protected $answer;

    /**
     * Thread constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->votes = new ArrayCollection();
        $this->private = false;
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
     * @param UserInterface $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @inheritdoc
     */
    public function getAuthorName()
    {
        return $this->getAuthor() ? $this->getAuthor()->getUsername() : $this->authorName;
    }

    /**
     * @inheritdoc
     */
    public function setAuthorName($authorName = null)
    {
        if ($this->author) {
            $this->authorName = $this->author->getUsername();
        } elseif ($authorName) {
            $this->authorName = $authorName;
        } else {
            $this->authorName = self::DEFAULT_ANONYMOUS;
        }
    }

    /**
     * Sets the score of the comment.
     *
     * @param integer $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Returns the current score of the comment.
     *
     * @return integer
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Increments the comment score by the provided
     * value.
     *
     * @param integer value
     *
     * @return integer The new comment score
     */
    public function incrementScore($by = 1)
    {
        $this->score += $by;
    }

    /**
     * @return int
     */
    public function getAbusesCount()
    {
        return $this->abusesCount;
    }

    /**
     * @param int $abusesCount
     */
    public function setAbusesCount($abusesCount)
    {
        $this->abusesCount = $abusesCount;
    }

    /**
     * @param Abuse $abuse
     */
    public function addAbuse(Abuse $abuse)
    {
        $abuse->setComment($this);
        $this->abuses->add($abuse);
        $this->abusesCount++;
    }

    /**
     * @return ArrayCollection
     */
    public function getAbuses()
    {
        return $this->abuses;
    }

    /**
     * @param ArrayCollection $abuses
     */
    public function setAbuses($abuses)
    {
        $this->abuses = $abuses;
    }

    /**
     * @return ArrayCollection|Vote[]
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param ArrayCollection $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param mixed $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }
}