<?php

namespace App\Tests\Service;

use App\Document\News;
use App\Service\NewsService;
use App\Service\Scraper\NewsScraperInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NewsServiceTest extends TestCase
{
    public function testFetchAndSaveNewsPersistsNewArticles(): void
    {
        $scraperMock = $this->createMock(NewsScraperInterface::class);
        $dmMock = $this->createMock(DocumentManager::class);
        $repoMock = $this->createMock(DocumentRepository::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $scraperMock->method('scrape')->willReturn([
            [
                'title' => 'Noticia Test',
                'url' => 'http://test.com/noticia-1',
                'description' => 'Descripción de prueba',
                'date' => new \DateTime()
            ]
        ]);
        $scraperMock->method('getSource')->willReturn('Fuente Test');

        $repoMock->method('findOneBy')->willReturn(null);
        $dmMock->method('getRepository')->willReturn($repoMock);

        $dmMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(News::class));

        $dmMock->expects($this->once())->method('flush');

        $service = new NewsService(
            [$scraperMock],
            $dmMock,
            $loggerMock
        );

        $count = $service->fetchAndSaveNews();

        $this->assertEquals(1, $count);
    }
}
