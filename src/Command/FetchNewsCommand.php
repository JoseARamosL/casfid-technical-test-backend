<?php

namespace App\Command;

use App\Service\NewsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-news',
    description: 'Descarga noticias de las fuentes configuradas',
)]
class FetchNewsCommand extends Command
{
    public function __construct(private NewsService $newsService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando descarga de noticias...');

        $stats = $this->newsService->fetchAndSaveNews();

        $io->success(sprintf(
            'Proceso finalizado. Total: %d noticias (El Mundo: %d, El País: %d)',
            $stats['total'],
            $stats['totalElMundo'],
            $stats['totalElPais']
        ));

        return Command::SUCCESS;
    }
}
