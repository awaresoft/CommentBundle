<?php

namespace Awaresoft\CommentBundle\Entity\Repository;

use Awaresoft\CommentBundle\Entity\Comment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ThreadRepository class.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ThreadRepository extends EntityRepository
{
    /**
     * Count all threads in database
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countAll(array $criteria = [])
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        if (count($criteria)) {
            foreach ($criteria as $i => $crit) {
                $qb->andWhere('t.' . $i . ' = :' . $i);
                $qb->setParameter($i, $crit);
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }
}