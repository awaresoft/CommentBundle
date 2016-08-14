<?php

namespace Awaresoft\CommentBundle\Command;

use Application\CommentBundle\Entity\Thread;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveUnusedThreadsCommand
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RemoveUnusedThreadsCommand extends ContainerAwareCommand
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName('awaresoft:comment:remove-unused-threads')
            ->setDescription('Remove unused threads');
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
        $em = $this->getContainer()->get('doctrine')->getManager();
        $threadManager = $this->getContainer()->get('awaresoft.comment.manager.thread');
        /**
         * @var Thread[] $threads
         */
        $threads = $threadManager->findAllThreads();
        $i = 0;

        foreach ($threads as $thread) {
            $object = $em->getRepository($thread->getClass())->findOneBy(array(
                'thread' => $thread,
            ));

            if ($object) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln(sprintf('Thread %d is useful, nothing to remove.', $thread->getId()));
                }

                continue;
            }

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf('Thread %d has been removed.', $thread->getId()));
            }

            $em->remove($thread);

            if (++$i % 10 == 0) {
                $em->flush();
            }
        }

        $em->flush();
    }
}
