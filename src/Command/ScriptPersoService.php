<?php

namespace App\Command;

use App\Service\GenerateArticleService;
use App\Service\ScriptService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScriptPersoService extends Command
{
    protected static $defaultName = 'app:script';

    /**
     * @var ScriptService $scriptService
     */
    protected $scriptService;

    /**
     * @param ScriptService $scriptService
     */
    public function __construct(ScriptService $scriptService) {
        $this->scriptService = $scriptService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Download images')
            ->setHelp('Download images from BDM');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
            $this->scriptService->script();
    }
}
