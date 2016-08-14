<?php

namespace Awaresoft\CommentBundle\Entity\Repository;

use Awaresoft\CommentBundle\Entity\Comment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
}