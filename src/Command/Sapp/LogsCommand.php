<?php


namespace NorthStack\NorthStackClient\Command\Sapp;

use GuzzleHttp\Client;
use NorthStack\NorthStackClient\API\Logs\LogsClient;
use NorthStack\NorthStackClient\Command\Command;
use NorthStack\NorthStackClient\Command\OauthCommandTrait;
use NorthStack\NorthStackClient\Command\UserSettingsCommandTrait;
use NorthStack\NorthStackClient\JSON\Merger;
use NorthStack\NorthStackClient\LogFormat\LogFormat;
use NorthStack\NorthStackClient\LogFormat\LogFormatInterface;
use Ratchet\RFC6455\Messaging\Message;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LogsCommand extends Command
{
    use OauthCommandTrait;
    use SappEnvironmentTrait;
    use UserSettingsCommandTrait;
    /**
     * @var LogsClient
     */
    protected $api;
    /**
     * @var Client
     */
    private $guzzle;
    /**
     * @var Merger
     */
    private $merger;

    const LOG_TOPICS = ['access', 'error', 'build'];

    public function __construct(
        LogsClient $api
    )
    {
        parent::__construct('app:logs');
        $this->api = $api;
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('NorthStack App Logs')
            ->addArgument('name', InputArgument::REQUIRED, 'App name')
            ->addArgument('environment', InputArgument::REQUIRED, 'Environment (prod, test, or dev)')
            ->addArgument('topic', InputArgument::REQUIRED, 'Log type (access, error, build)')
            ->addOption('topicOverride', 't', InputOption::VALUE_REQUIRED, 'Override Topic (You should know what you are doing if you are using this)')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output raw json', null);
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $args = $input->getArguments();
        $options = $input->getOptions();

        if (!$options['topicOverride']) {
            [$sappId] = $this->getSappIdAndFolderByOptions(
                $this->findDefaultAppsDir($input, $output, $this->getHelper('question')),
                $args['name'],
                $args['environment']
            );

            if (!in_array($args['topic'], self::LOG_TOPICS)) {
                $output->writeln('<error>Log topic must be one of the following: ' . implode(',', self::LOG_TOPICS) . '</error>');
                return;
            }
            $topic = "{$sappId}_{$args['topic']}";
        } else {
            $topic = $options['topicOverride'];
        }

        $formatHint = $options['json'] ? 'json' : $args['topic'];
        $format = LogFormat::getFormat($formatHint);
        /** @var LogFormatInterface $formatter */
        $formatter = new $format($output);

        if ($args['topic'] === 'build') {
            if (!isset($sappId)) {
                throw new \RuntimeException('App not determined for build logs');
            }
            $this->showBuildLog($sappId, $formatter);
            exit;
        }
        $this->api->streamTopic($this->token->token, function (Message $message) use ($formatter) {
            $data = json_decode((string)$message);
            $formatter->render($data);
        }, $topic, $output);
    }

    protected function showBuildLog(string $sappId, LogFormatInterface $formatter)
    {
        $resp = $this->api->getBuildLog(
            $this->token->token,
            $sappId
        );
        $data = json_decode($resp->getBody()->getContents());
        foreach ($data->data as $msg) {
            // Massaging things a bit to get the events into the same format as the streaming API
            $msg->{'@timestamp'} = $msg->timestamp / 1000;
            $formatter->render(
                (object)[
                    'type' => 'log',
                    'message' => json_encode($msg)
                ]
            );
        }
    }
}
