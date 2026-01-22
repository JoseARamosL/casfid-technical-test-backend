<?php

namespace App\Service;

use App\Document\News;
use App\Service\Scraper\NewsScraperInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class NewsService
{
    private iterable $scrapers;
    private const MAX_TOTAL_SAVED = 250;

    public function __construct(
        #[TaggedIterator('app.news_scraper')] iterable $scrapers,
        private DocumentManager $dm,
        private LoggerInterface $logger
    ) {
        $this->scrapers = $scrapers;
    }

    public function fetchAndSaveNews(): int
    {
        $totalSaved = 0;

        /** @var NewsScraperInterface $scraper */
        foreach ($this->scrapers as $scraper) {
            try {
                $this->logger->info(sprintf('Procesando fuente: %s', $scraper->getSource()));
                $articles = $scraper->scrape();

                foreach ($articles as $articleData) {
                    // Verificamos duplicados
                    $exists = $this->dm->getRepository(News::class)->findOneBy(['url' => $articleData['url']]);

                    if ($exists) {
                        continue;
                    }

                    $news = new News(
                        $articleData['title'],
                        $articleData['url'],
                        $scraper->getSource(),
                        $articleData['date']
                    );
                    $news->setDescription($articleData['description']);

                    $this->dm->persist($news);
                    $totalSaved++;

                    if ($totalSaved % 250 === 0) {
                        $this->dm->flush();
                    }
                }

                $this->dm->flush();

            } catch (\Exception $e) {
                $this->logger->error(sprintf('Error en %s: %s', $scraper->getSource(), $e->getMessage()));
            }
        }

        return $totalSaved;
    }
}
