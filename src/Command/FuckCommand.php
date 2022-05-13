<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Alzee\Qstamp\Qstamp;
use App\Entity\Device;
use App\Entity\Fingerprint;
use App\Entity\Organization;
use App\Entity\Wecom;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

#[AsCommand(
    name: 'fuck',
    description: 'Add a short description for your command',
)]
class FuckCommand extends Command
{
    private $em;

    function __construct(EntityManagerInterface $em) 
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $arg1 = $input->getArgument('arg1');

        $device = $this->em->getRepository(Device::class)->find(2);
        $org = $this->em->getRepository(Organization::class)->find(1);
        // dump($org);

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }

        $cache = new RedisAdapter(RedisAdapter::createConnection('redis://localhost'));

        $cache->get('word', function (ItemInterface $item) {
            $item->expiresAfter(2);
            return true;
        });

        // sleep(2);

        $i = $cache->getItem('word');
        if ($i->isHit()) {
            echo 'yes';
        } else {
            echo 'no';
        }
        dump($i);

        $io->success($org->getId() . ' ' . $org->getName());

        return Command::SUCCESS;
    }
}
