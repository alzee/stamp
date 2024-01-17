<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'adduser',
    description: 'Create user',
)]
class AddUserCommand extends Command
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addOption('super', 's', InputOption::VALUE_NONE, 'Is super')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Is admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $roles = [];
        ;
        if ($input->getOption('admin')) {
            $roles = ["ROLE_ADMIN"];
        }

        if ($input->getOption('super')) {
            $roles = ["ROLE_SUPER_ADMIN"];
        }
        
        $user = new User();
        $user->setUsername($username);
        $user->setPlainPassword($password);
        $user->setRoles($roles);
        $this->em->persist($user);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
