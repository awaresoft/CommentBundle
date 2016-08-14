<?php

namespace Awaresoft\CommentBundle\Manager;

use Application\UserBundle\Entity\User;
use FOS\CommentBundle\Event\ThreadEvent;
use FOS\CommentBundle\Events;
use FOS\CommentBundle\Model\ThreadInterface;
use Sonata\CommentBundle\Manager\ThreadManager as BaseThreadManager;

/**
 * Extended ORM ThreadManager.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ThreadManager extends BaseThreadManager
{
    /**
     * Default permalink adding to thread
     */
    const THREAD_DEFAULT_PERMALINK = 'regenerate';

    /**
     * @param mixed $object
     * @param User $owner
     * @param string $permalink
     * @param bool $andFlush
     *
     * @return mixed
     * @throws \Exception
     */
    public function initializeThread($object, User $owner, $permalink = null, $andFlush = true)
    {
        if ($object->getThread()) {
            return $object;
        }

        if (!is_object($object)) {
            throw new \Exception('$object parameter is not a object type in thread initialization');
        }

        if (!method_exists($object, 'setThread')) {
            throw new \Exception('$object->setThread() method does not exists');
        }

        if (!$permalink) {
            $permalink = self::THREAD_DEFAULT_PERMALINK;
        }

        /**
         * @var Thread $thread
         */
        $thread = $this->createThread();
        $thread->setClass(get_class($object));
        $thread->setPermalink($permalink);
        $thread->setOwner($owner);
        $this->saveThreadCustom($thread, $andFlush);
        $object->setThread($thread);

        return $object;
    }

    /**
     * Extended saveThread method. Added condition for flushing.
     *
     * @param ThreadInterface $thread
     * @param bool $andFlush
     */
    public function saveThreadCustom(ThreadInterface $thread, $andFlush = true)
    {
        $event = new ThreadEvent($thread);
        $this->dispatcher->dispatch(Events::THREAD_PRE_PERSIST, $event);

        $this->doSaveThreadCustom($thread, $andFlush);

        $event = new ThreadEvent($thread);
        $this->dispatcher->dispatch(Events::THREAD_POST_PERSIST, $event);
    }

    /**
     * Extended doSaveThread method. Added condition for flushing.
     *
     * @param ThreadInterface $thread
     * @param bool $andFlush
     */
    public function doSaveThreadCustom(ThreadInterface $thread, $andFlush = true)
    {
        $this->em->persist($thread);

        if ($andFlush) {
            $this->em->flush();
        }
    }
}
