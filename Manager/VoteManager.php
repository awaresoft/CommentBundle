<?php

namespace Awaresoft\CommentBundle\Manager;

use Application\UserBundle\Entity\User;
use Awaresoft\CommentBundle\Entity\Comment;
use Awaresoft\CommentBundle\Entity\Repository\VoteRepository;
use FOS\CommentBundle\Entity\VoteManager as BaseVoteManager;
use FOS\CommentBundle\Model\VoteInterface;

/**
 * Extended ORM VoteManager.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class VoteManager extends BaseVoteManager
{
    /**
     * @return \Doctrine\ORM\EntityRepository|\FOS\CommentBundle\Entity\EntityRepository|VoteRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param User $user
     * @param Comment $comment
     *
     * @return null|object
     */
    public function findUserCommentVote(User $user, Comment $comment)
    {
        return $this->repository->findOneBy(['voter' => $user, 'comment' => $comment]);
    }

    /**
     * Create new vote for selected comment
     *
     * @param Comment $comment
     * @param User $user
     * @param $action up/down
     */
    public function createNewVote(Comment $comment, User $user, $action)
    {
        $vote = $this->createVote($comment);

        if ($action == 'up') {
            $vote->setValue(VoteInterface::VOTE_UP);
        } else {
            $vote->setValue(VoteInterface::VOTE_DOWN);
        }

        $this->saveVote($vote);
    }
}
