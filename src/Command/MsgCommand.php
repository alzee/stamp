<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Alzee\Fwc\Message;
use Alzee\Fwc\Media;
use Alzee\Fwc\Fwc;

#[AsCommand(
    name: 'msg',
    description: 'Add a short description for your command',
)]
class MsgCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }
        
        $corpId = 'ww598d275367f9ce0a';
        $secret = 'ZuPrsDdR4e0-4NeHNtcPAHcZO8cvXCqcNYnTzqveTsU';
        $fwc = new Fwc();
        $token = $fwc->getAccessToken($corpId, $secret);
        dump($token);
        
        $msg = new Message($token);
        $content = 'hi';
        $agentId = '1000005';
        // $agentId = '3010040';
        $data = $msg->sendTextTo("HouFei", $content, $agentId);
        dump($data);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
