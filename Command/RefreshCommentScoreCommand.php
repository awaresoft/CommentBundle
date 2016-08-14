<?php

namespace Awaresoft\CommentBundle\Command;

use Awaresoft\CommentBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RefreshCommentScoreCommand
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RefreshCommentScoreCommand extends ContainerAwareCommand
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName('awaresoft:comment:refresh-comment-score')
            ->setDescription('Refresh comments scores by counting votes.');
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
        $commentManager = $this->getContainer()->get('awaresoft.comment.manager.comment');

        /**
         * @var Comment[] $comments
         */
        $comments = $commentManager->getRepository()->findAll();

        $i = 0;
        foreach ($comments as $comment) {
            $votes = $comment->getVotes();
            $votesScoreSum = 0;
            $oldComment = clone $comment;

            foreach ($votes as $vote) {
                $votesScoreSum += $vote->getValue();
            }

            if ($votesScoreSum == $comment->getScore()) {
                $logger->info(sprintf('Comment %d score is the same as counted vote\'s scores sum.', $comment->getId()));

                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln(sprintf('Comment %d score is the same as counted vote\'s scores sum.', $comment->getId()));
                }

                continue;
            }

            $logger->info(sprintf('Comment %d score updated, from %d to %d.', $comment->getId(), $comment->getScore(), $votesScoreSum));

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf('Comment %d score updated, from %d to %d.', $comment->getId(), $comment->getScore(), $votesScoreSum));
            }

            $comment->setScore($votesScoreSum);
            $commentManager->updateComment($comment, $oldComment, false);

            if ($i % 10 == 0) {
                $em->flush();
            }
        }

        $em->flush();
    }
}
