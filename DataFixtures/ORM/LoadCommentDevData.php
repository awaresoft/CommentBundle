<?php

namespace Awaresoft\CommentBundle\DataFixtures\ORM;

use Awaresoft\Doctrine\Common\DataFixtures\AbstractFixture as AwaresoftAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadCommentData
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class LoadCommentDevData extends AwaresoftAbstractFixture
{

    private $threadId = 1;
    private $commentId = 1;

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironments()
    {
        return array('dev');
    }

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->createThreadWithComments(3);
        $this->createThreadWithComments(5);
        $this->createThreadWithComments(8);
        $this->createThreadWithComments(2);
    }

    /**
     * Create thread with specified count od comments.
     *
     * @param $commentsCount
     */
    protected function createThreadWithComments($commentsCount)
    {
        $faker = $this->getFaker();
        $threadManager = $this->container->get('fos_comment.manager.thread');
        $thread = $threadManager->createThread();
        $thread->setId($this->threadId);
        $thread->setPermalink(sprintf('%s/%d', $faker->url, $this->threadId));
        $thread->setClass('Application\UserBundle\Entity\User');
        $threadManager->saveThread($thread);

        for ($i = 0; $i < $commentsCount; $i++, $this->commentId++) {
            $commentManager = $this->container->get('fos_comment.manager.comment');
            $comment = $commentManager->createComment($thread);
            $comment->setBody(sprintf('%s, comment id: %d', $faker->text(255), $this->commentId));
            $commentManager->saveComment($comment);
        }

        $this->threadId++;
    }
}
