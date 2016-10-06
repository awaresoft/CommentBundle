<?php

namespace Awaresoft\CommentBundle\Entity\Repository;

use Awaresoft\CommentBundle\Entity\Comment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VoteRepository class.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class VoteRepository extends EntityRepository
{
    /**
     * Return votes by comment
     *
     * @param Comment $comment
     *
     * @return array
     */
    public function findByComment(Comment $comment)
    {
        return $this->findBy([
            'comment' => $comment,
        ]);
    }

    /**
     * Return count of votes by user per day
     *
     * @param UserInterface $user
     *
     * @return int
     */
    public function countDailyVotesByUser(UserInterface $user)
    {
        $dateNow = new \DateTime();

        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.voter = :voter')
            ->andWhere('DATE(v.createdAt) = :date')
            ->setParameter('voter', $user)
            ->setParameter('date', $dateNow->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}