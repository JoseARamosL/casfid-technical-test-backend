<?php

namespace App\Service\Scraper;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElMundoScraper implements NewsScraperInterface
{
    private const URL = 'https://www.elmundo.es';

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger
    ) {}

    public function scrape(): array
    {
        $data = [];

        try {
            $response = $this->client->request('GET', self::URL);
            $crawler = new Crawler($response->getContent());

            $articles = $crawler->filter('article')->slice(0, 5);

            $articles->each(function (Crawler $node) use (&$data) {

                $titleNode = $node->filter('h2')->first();

                $linkNode = $node->filter('a')->first();

                $descNode = $node->filter('p')->first();

                if ($titleNode->count() > 0 && $linkNode->count() > 0) {
                    $url = $linkNode->attr('href');

                    // Aseguramos que la URL sea absoluta
                    if (!str_starts_with($url, 'http')) {
                        $url = self::URL . $url;
                    }

                    $data[] = [
                        'title' => trim($titleNode->text()),
                        'url' => $url,
                        'description' => $descNode->count() > 0 ? trim($descNode->text()) : null,
                        'date' => new \DateTime()
                    ];
                }
            });

        } catch (\Exception $e) {
            $this->logger->error(sprintf('Error scraping El Mundo: %s', $e->getMessage()));
        }

        return $data;
    }

    public function getSource(): string
    {
        return 'El Mundo';
    }
}
