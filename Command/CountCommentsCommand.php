<?php

namespace Awaresoft\CommentBundle\Command;

use Application\CommentBundle\Entity\Thread;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CountCommentsCommand
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class CountCommentsCommand extends ContainerAwareCommand
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName('awaresoft:comment:count')
            ->setDescription('Count comments in threads (only active)');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $threadManager = $this->getContainer()->get('awaresoft.comment.manager.thread');
        $commentManager = $this->getContainer()->get('awaresoft.comment.manager.comment');
        /**
         * @var Thread[] $threads
         */
        $threads = $threadManager->findAllThreads();
        $i = 0;

        foreach ($threads as $thread) {
            $commentsCount = $commentManager->countCommentsByThread($thread);

            if ($commentsCount == $thread->getNumComments()) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln(sprintf('Comments count %d for thread %d is correct.', $thread->getNumComments(), $thread->getId()));
                }

                continue;
            }

            $logger->info(sprintf('Changed count of comments from %d to %d in thread %d', $thread->getNumComments(),
                $commentsCount, $thread->getId()), [
                'thread' => $thread,
                'threadsCount' => count($threads),
            ]);

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf('Comments count for thread %d changed, from: %d to %d.', $thread->getId(), $thread->getNumComments(), $commentsCount));
            }

            $thread->setNumComments($commentsCount);

            if ($i % 10 == 0) {
                $em->flush();
            }
        }

        $em->flush();
    }
}
