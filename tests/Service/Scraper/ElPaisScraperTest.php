<?php

namespace App\Tests\Service\Scraper;

use App\Service\Scraper\ElPaisScraper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ElPaisScraperTest extends TestCase
{
    public function testScrapeExtractsTitleFromHtml(): void
    {
        $html = <<<HTML
        <html>
            <body>
                <article>
                    <header>
                        <h2><a href="/noticia-falsa">Titular de Prueba</a></h2>
                    </header>
                    <p>Descripción corta</p>
                </article>
            </body>
        </html>
        HTML;

        $mockResponse = new MockResponse($html);
        $httpClient = new MockHttpClient($mockResponse);

        $logger = $this->createMock(LoggerInterface::class);

        $scraper = new ElPaisScraper($httpClient, $logger);

        $news = $scraper->scrape();

        $this->assertCount(1, $news); // Debe encontrar las noticias de El Pais que hayamos rescatado con el comando
        $this->assertEquals('Titular de Prueba', $news[0]['title']); // El título debe coincidir
        $this->assertEquals('El Pais', $scraper->getSource());
    }
}
