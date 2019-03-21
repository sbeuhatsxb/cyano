<?php

namespace App\Command;

use App\Service\GenerateArticleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateArticle extends Command
{
    protected static $defaultName = 'app:generate:article';

    /**
     * @var GenerateArticleService $generateArticleService
     */
    protected $generateArticleService;

    /**
     * @param GenerateArticleService $generateArticleService
     */
    public function __construct(GenerateArticleService $generateArticleService) {
        $this->generateArticleService = $generateArticleService;
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
        $this->generateArticleService->generate();

    }
}
