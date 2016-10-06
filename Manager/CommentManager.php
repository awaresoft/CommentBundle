<?php

namespace Awaresoft\CommentBundle\Manager;

use Application\UserBundle\Entity\User;
use Awaresoft\CommentBundle\Entity\Comment;
use Awaresoft\CommentBundle\Entity\Repository\CommentRepository;
use Doctrine\ORM\Query\Expr\Join;
use FOS\CommentBundle\Model\ThreadInterface;
use FOS\UserBundle\Model\UserInterface;
use Sonata\CommentBundle\Manager\CommentManager as BaseCommentManager;

/**
 * Extended ORM CommentManager.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class CommentManager extends BaseCommentManager
{
    /**
     * Default limit for comments displaying on the site
     */
    const DEFAULT_LIMIT = 5;

    /**
     * Default limit for comments displaying on the site
     */
    const DEFAULT_PANEL_LIMIT = 5;


    /**
     * @return \Doctrine\ORM\EntityRepository|CommentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Find comments by thread with limit
     *
     * @param User $user
     * @param ThreadInterface $thread
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @param null $depth
     * @param bool $withPrivate
     * @param bool $withDeleted
     *
     * @return Comment[]
     */
    public function findCommentsByThreadWithLimit(User $user = null, ThreadInterface $thread, $limit = self::DEFAULT_LIMIT, $offset = 0, array $orderBy = ['c.createdAt' => 'DESC'], $depth = null, $withPrivate = true, $withDeleted = false)
    {
        return $this->findComments($user, ['c.thread =' => $thread], $limit, $offset, $orderBy, $depth, $withPrivate, $withDeleted);
    }

    /**
     * Find comments by owner with limit
     *
     * @param User $user
     * @param User $owner
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @param null $depth
     * @param bool $withPrivate
     * @param bool $withDeleted
     *
     * @return Comment[]
     */
    public function findCommentsByOwnerWithLimit(User $user = null, User $owner, $limit = self::DEFAULT_LIMIT, $offset = 0, array $orderBy = ['c.createdAt' => 'DESC'], $depth = null, $withPrivate = true, $withDeleted = false)
    {
        return $this->findComments($user, ['t.owner =' => $owner], $limit, $offset, $orderBy, $depth, $withPrivate, $withDeleted);
    }

    /**
     * Find comments by author with limit
     *
     * @param User $user
     * @param User $author
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @param null $depth
     * @param bool $withPrivate
     * @param bool $withDeleted
     *
     * @return Comment[]
     */
    public function findCommentsByAuthorWithLimit(User $user = null, User $author, $limit = self::DEFAULT_LIMIT, $offset = 0, array $orderBy = ['c.createdAt' => 'DESC'], $depth = null, $withPrivate = true, $withDeleted = false)
    {
        return $this->findComments($user, ['c.author =' => $author], $limit, $offset, $orderBy, $depth, $withPrivate, $withDeleted);
    }

    /**
     * Find comments by criteria
     *
     * @param User $user
     * @param array $criteria
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @param null $depth
     * @param bool $withPrivate
     * @param bool $withDeleted
     *
     * @return Comment[]
     */
    public function findComments(User $user = null, $criteria = [], $limit = self::DEFAULT_LIMIT, $offset = 0, array $orderBy = ['c.createdAt' => 'DESC'], $depth = null, $withPrivate = true, $withDeleted = false)
    {
        $qb = $this->repository->createQueryBuilder('c')
            ->select('c, t.permalink, u.username')
            ->innerJoin('c.thread', 't')
            ->join('c.author', 'u')
            ->orderBy('c.ancestors', 'ASC');

        if ($user) {
            $qb2 = $this->em->createQueryBuilder()
                ->select('v.value')
                ->from('ApplicationCommentBundle:Vote', 'v')
                ->where('v.voter = :voter')
                ->andWhere('v.comment = c')
                ->getDQL();

            $qb->addSelect('(' . $qb2 . ') AS value')
                ->setParameter('voter', $user);

            $qb3 = $this->em->createQueryBuilder()
                ->select('ub.id')
                ->from('ApplicationUserBundle:User', 'ub')
                ->where('ub = :voter')
                ->andWhere('c.author MEMBER OF ub.usersBlockedMe OR c.author MEMBER OF ub.blockedUsers')
                ->getDQL();

            $qb->addSelect('(' . $qb3 . ') AS blocked')
                ->setParameter('voter', $user);
        }

        if (!$withDeleted) {
            $qb->andWhere('c.state = :state OR c.state = :state2')
                ->setParameter('state', Comment::STATUS_VALID)
                ->setParameter('state2', Comment::STATUS_MODERATE);
        }

        $i = 1;
        foreach ($criteria as $key => $value) {
            $qb->andWhere($key . ':param' . $i)->setParameter('param' . $i, $value);
            $i++;
        }

        if (null !== $depth && $depth >= 0) {
            $qb->andWhere('c.depth < :depth')->setParameter('depth', $depth + 1);
        }

        if (!$withPrivate) {
            $qb->andWhere('c.private = :private')->setParameter('private', false);
        }

        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $orderKeys = array_keys($orderBy);
        $qb->orderBy($orderKeys[0], $orderBy[$orderKeys[0]]);

        return $qb->getQuery()->getResult();
    }

    /**
     * Return number of received comments by user
     *
     * @param User $owner
     * @param bool $withPrivate
     * @param bool $withDeleted
     *
     * @return int
     */
    public function countUserReceivedComments(User $owner, $withPrivate = true, $withDeleted = false)
    {
        $qb = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->innerJoin('c.thread', 't')
            ->where('t.owner = :owner')
            ->setParameter('owner', $owner);

        if (!$withDeleted) {
            $qb
                ->andWhere('c.state = :state OR c.state = :state2')
                ->setParameter('state', Comment::STATUS_VALID)
                ->setParameter('state2', Comment::STATUS_MODERATE);
        }

        if (!$withPrivate) {
            $qb
                ->andWhere('c.private = :private')
                ->setParameter('private', false);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Return number of added comments by user
     *
     * @param User $author
     * @param bool $withPrivate
     * @param bool $withDeleted
     *
     * @return int
     */
    public function countUserAddedComments(User $author, $withPrivate = true, $withDeleted = false)
    {
        $qb = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.author = :author')
            ->setParameter('author', $author);

        if (!$withDeleted) {
            $qb->andWhere('c.state = :state OR c.state = :state2')
                ->setParameter('state', Comment::STATUS_VALID)
                ->setParameter('state2', Comment::STATUS_MODERATE);
        }

        if (!$withPrivate) {
            $qb->andWhere('c.private = :private')->setParameter('private', false);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param ThreadInterface $thread
     * @param bool $withPrivate
     * @param bool $withDeleted
     *
     * @return mixed
     */
    public function countCommentsByThread(ThreadInterface $thread, $withPrivate = true, $withDeleted = false)
    {
        $qb = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.thread = :thread')
            ->setParameter('thread', $thread);

        if (!$withDeleted) {
            $qb->andWhere('c.state = :state OR c.state = :state2')
                ->setParameter('state', Comment::STATUS_VALID)
                ->setParameter('state2', Comment::STATUS_MODERATE);
        }

        if (!$withPrivate) {
            $qb->andWhere('c.private = :private')->setParameter('private', false);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param UserInterface $owner
     *
     * @return mixed
     */
    public function countCommentsByOwner(UserInterface $owner)
    {
        $qb = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.thread', 't')
            ->where('t.owner = :owner')
            ->andWhere('c.state = :state OR c.state = :state2')
            ->setParameters([
                'owner' => $owner,
                'state' => Comment::STATUS_VALID,
                'state2' => Comment::STATUS_MODERATE,
            ]);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param UserInterface $author
     *
     * @return mixed
     */
    public function countCommentsByAuthor(UserInterface $author)
    {
        $qb = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.author = :author')
            ->andWhere('c.state = :state OR c.state = :state2')
            ->setParameters([
                'author' => $author,
                'state' => Comment::STATUS_VALID,
                'state2' => Comment::STATUS_MODERATE,
            ]);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create comment
     *
     * @param Comment $comment
     * @param User $user
     * @param mixed $object
     * @param null $commentForm
     *
     * @throws \Exception
     */
    public function createNewComment(Comment $comment, User $user, $object = null, $commentForm = null)
    {
        if (method_exists($object, 'isPrivate') && $object->isPrivate()) {
            $comment->setPrivate(true);
        }

        if ($comment->getId()) {
            throw new \Exception(sprintf('Comment already exists %d', $comment->getId()));
        }

        if ($user instanceof User) {
            $comment->setAuthor($user);
        }

        if ($commentForm && !$commentForm->getData()->getAuthorName()) {
            $comment->setAuthorName();
        }

        $this->saveComment($comment);
    }

    /**
     * Remove comment
     *
     * @param Comment $comment
     * @param bool $flush
     */
    public function removeComment(Comment $comment, $flush = true)
    {
        if ($comment->getState() != Comment::STATUS_INVALID) {
            $comment->getThread()->setNumComments($comment->getThread()->getNumComments() - 1);
        }

        $this->em->remove($comment);

        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * Remove comment
     *
     * @param Comment $comment
     * @param Comment $oldComment
     * @param bool $flush
     */
    public function updateComment(Comment $comment, Comment $oldComment, $flush = true)
    {
        if ($oldComment->getState() == Comment::STATUS_VALID || $oldComment->getState() == Comment::STATUS_MODERATE) {
            if ($comment->getState() == Comment::STATUS_INVALID) {
                $comment->getThread()->setNumComments($comment->getThread()->getNumComments() - 1);
            }
        }

        if ($oldComment->getState() == Comment::STATUS_INVALID) {
            if ($comment->getState() == Comment::STATUS_VALID || $comment->getState() == Comment::STATUS_MODERATE) {
                $comment->getThread()->setNumComments($comment->getThread()->getNumComments() + 1);
            }
        }

        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * Invalidate comment
     *
     * @param Comment $comment
     * @param bool $flush
     */
    public function invalidateComment(Comment $comment, $flush = true)
    {
        $oldComment = clone $comment;
        $comment->setState(Comment::STATUS_INVALID);
        $this->updateComment($comment, $oldComment, $flush);
    }
}
