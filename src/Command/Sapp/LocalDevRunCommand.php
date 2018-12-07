<?php


namespace NorthStack\NorthStackClient\Command\Sapp;


use NorthStack\NorthStackClient\Command\Command;
use NorthStack\NorthStackClient\Docker\Action\RunCmdAction;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class LocalDevRunCommand extends AbstractLocalDevCmd
{
    protected $commandDescription = 'Run a docker-compose command';

    protected function commandName(): string {
        return 'app:localdev:run';
    }

    protected function getDockerAction()
    {
        return RunCmdAction::class;
    }

    public function configure()
    {
        parent::configure();
        $this
            ->addArgument('run', InputArgument::IS_ARRAY, 'Command to run', ['help'])
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $action = $this->getAction();
        $action->setCmd($input->getArgument('run'));
        $action->run();
    }
}