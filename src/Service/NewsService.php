<?php

namespace App\Service;

use App\Document\News;
use App\Service\Scraper\NewsScraperInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class NewsService
{
    private iterable $scrapers;
    private const MAX_TOTAL_SAVED = 250;

    public function __construct(
        #[AutowireIterator('app.news_scraper')] iterable $scrapers,
        private DocumentManager $dm,
        private LoggerInterface $logger
    ) {
        $this->scrapers = $scrapers;
    }

    public function fetchAndSaveNews(): array
    {
        $globalStats = [
            'total' => 0,
            'totalElMundo' => 0,
            'totalElPais' => 0
        ];

        /** @var NewsScraperInterface $scraper */
        foreach ($this->scrapers as $scraper) {
            try {
                $this->logger->info(sprintf('Procesando fuente: %s', $scraper->getSource()));

                $scraperStats = $this->saveAndGetNewArticles($scraper);

                $globalStats['total'] += $scraperStats['total'];
                $globalStats['totalElMundo'] += $scraperStats['totalElMundo'];
                $globalStats['totalElPais'] += $scraperStats['totalElPais'];

            } catch (\Exception $e) {
                $this->logger->error(sprintf('Error en %s: %s', $scraper->getSource(), $e->getMessage()));
            }
        }

        return $globalStats;
    }

    private function saveAndGetNewArticles(NewsScraperInterface $scraper): array
    {
        $totalSaved = 0;
        $totalElMundo = 0;
        $totalElPais = 0;

        try {
            $articles = $scraper->scrape();

            foreach ($articles as $articleData) {
                $exists = $this->dm->getRepository(News::class)->findOneBy(['url' => $articleData['url']]);

                if ($exists) {
                    continue;
                }

                $newArticle = new News(
                    $articleData['title'],
                    $articleData['url'],
                    $scraper->getSource(),
                    $articleData['date']
                );

                if (isset($articleData['description'])) {
                    $newArticle->setDescription($articleData['description']);
                }

                $this->dm->persist($newArticle);

                switch ($newArticle->getSource()) {
                    case 'El Mundo':
                        $totalElMundo++;
                        break;
                    case 'El Pais':
                    case 'El País':
                        $totalElPais++;
                        break;
                }

                $totalSaved++;

                if ($totalSaved % self::MAX_TOTAL_SAVED === 0) {
                    $this->dm->flush();
                }
            }

            $this->dm->flush();

        } catch (\Exception $e) {
            // Guardamos lo que tengamos hasta el error
            $this->dm->flush();
            $this->logger->error(sprintf('Error en %s: %s', $scraper->getSource(), $e->getMessage()));
        }

        return [
            'total' => $totalSaved,
            'totalElMundo' => $totalElMundo,
            'totalElPais' => $totalElPais
        ];
    }
}
