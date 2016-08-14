<?php

namespace Awaresoft\CommentBundle\Command;

use Application\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ThreadInitializationCommand
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ThreadInitializationCommand extends ContainerAwareCommand
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName('awaresoft:comment:initialize-threads')
            ->setDescription('Initialize thread for objects from selected class')
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'clear all thread id in table')
            ->addArgument('class', InputArgument::REQUIRED, 'class of object');
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

        if (!class_exists($input->getArgument('class'))) {
            throw new \Exception(sprintf('class %s does not exist', $input->getArgument('class')));
        }

        $allObjects = $em->getRepository($input->getArgument('class'))->findAll();

        if ($input->getOption('clear')) {
            $i = 0;
            foreach($allObjects as $object) {
                $object->setThread(null);

                if(++$i % 20 == 0) {
                    $em->flush();
                }
            }

            $em->flush();
        }

        foreach ($allObjects as $object) {
            if ($object instanceof User) {
                $owner = $object;
            } elseif (method_exists($object, 'getUser') && $object->getUser() instanceof User) {
                $owner = $object->getUser();
            } else {
                throw new \Exception(sprintf('Can\'t find User object (owner) for selected class %s', $input->getArgument('class')));
            }

            $object = $threadManager->initializeThread($object, $owner);
            $em->flush();
        }
    }
}
