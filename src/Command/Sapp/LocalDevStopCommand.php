<?php


namespace NorthStack\NorthStackClient\Command\Sapp;

use NorthStack\NorthStackClient\API\Sapp\SappClient;

use NorthStack\NorthStackClient\Command\Command;
use NorthStack\NorthStackClient\Command\OauthCommandTrait;

use NorthStack\NorthStackClient\Docker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LocalDevStopCommand extends Command
{
    protected $commandName = 'app:localdev:stop';
    protected $commandDescription = 'Stop the local dev environment';

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $args = $input->getArguments();

        [$sappId] = $this->getSappIdAndFolderByOptions(
            $args['name'],
            $args['environment']
        );

        $this->getComposeClient('wordpress')->stop();

    }
}
