<?php

namespace App\Service\Scraper;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElPaisScraper implements NewsScraperInterface
{
    private const URL = 'https://elpais.com';

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger
    ) {}

    public function scrape(): array
    {
        $data = [];

        try {
            // Petición HTTP
            $response = $this->client->request('GET', self::URL);

            // Parseo del HTML
            $crawler = new Crawler($response->getContent());

            // Extracción
            $articles = $crawler->filter('article')->slice(0, 5);

            $articles->each(function (Crawler $node) use (&$data) {

                $titleNode = $node->filter('header h2, h2')->first();
                $linkNode = $node->filter('a')->first();
                $descNode = $node->filter('p')->first();

                if ($titleNode->count() > 0 && $linkNode->count() > 0) {
                    $url = $linkNode->attr('href');

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
            $this->logger->error(sprintf('Error scraping El Pais: %s', $e->getMessage()));
        }

        return $data;
    }

    public function getSource(): string
    {
        return 'El Pais';
    }
}
