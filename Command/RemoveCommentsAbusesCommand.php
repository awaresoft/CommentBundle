<?php

namespace Awaresoft\CommentBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveCommentsAbusesCommand
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RemoveCommentsAbusesCommand extends ContainerAwareCommand
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this->setName('awaresoft:comment:remove-comments-abuses')
            ->setDescription('Remove old abuses. Default, older than 30 days.')
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'Set how older abuses have to be removed in days', 30);
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
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $qb = $em->createQueryBuilder()
            ->delete()
            ->from('ApplicationCommentBundle:Abuse', 'a')
            ->where('a.createdAt <= :date')
            ->setParameter('date', new \DateTime(sprintf('-%d days', $input->getOption('days'))));
        $qb->getQuery()->execute();
    }
}
