<?php

namespace NorthStack\NorthStackClient\LogFormat;

use NorthStack\NorthStackClient\LogFormat\LogFormatInterface;

class BaseLogFormat implements LogFormatInterface
{
    protected $io;
    protected $timeFmt = DATE_ISO8601;

    public function __construct($io)
    {
        $this->io = $io;
    }

    public function render($message)
    {
        if ($message->type === 'log') {
            $this->renderLog($message);
        } else {
            $this->renderInfo($message);
        }
    }

    protected function renderLog($msg)
    {
        $this->io->writeln($msg->message);
    }

    protected function renderInfo($msg)
    {
        $this->io->writeln($msg->message);
    }

    protected function renderTimestamp($ts)
    {
        return date($this->timeFmt, $ts);
    }
}
