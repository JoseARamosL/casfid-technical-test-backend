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
                $this->logger->info('Procesando fuente: ' . $scraper->getSource());
                $articles = $scraper->scrape();

                foreach ($articles as $articleData) {
                    // Verificamos duplicados
                    $exists = $this->dm->getRepository(News::class)->findOneBy(['url' => $articleData['url']]);

                    if (!$exists) {
                        $news = new News(
                            $articleData['title'],
                            $articleData['url'],
                            $scraper->getSource(),
                            $articleData['date']
                        );
                        $news->setDescription($articleData['description']);

                        $this->dm->persist($news);
                        $totalSaved++;
                    }
                }

                $this->dm->flush();

            } catch (\Exception $e) {
                $this->logger->error('Error en ' . $scraper->getSource() . ': ' . $e->getMessage());
            }
        }

        return $totalSaved;
    }
}
