<?php

namespace App\Controller;

use App\Document\News;
use App\Dto\NewsDto;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/feeds')]
class NewsController extends AbstractController
{
    public function __construct(
        private DocumentManager $dm,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $news = $this->dm->getRepository(News::class)->findBy([], ['publishedAt' => 'DESC']);
        return $this->json($news, 200, [], ['groups' => 'news:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $news = $this->dm->getRepository(News::class)->find($id);
        if (!$news) return $this->json(['error' => 'Not found'], 404);

        return $this->json($news, 200, [], ['groups' => 'news:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Convertir JSON a DTO
        try {
            /** @var NewsDto $dto */
            $dto = $this->serializer->deserialize($request->getContent(), NewsDto::class, 'json');
        } catch (\Exception $e) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        // Validar
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        // Crear Entidad
        $news = new News($dto->title, $dto->url, $dto->source);
        $news->setDescription($dto->description);

        $this->dm->persist($news);
        $this->dm->flush();

        return $this->json($news, 201, [], ['groups' => 'news:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $news = $this->dm->getRepository(News::class)->find($id);
        if (!$news) return $this->json(['error' => 'Not found'], 404);

        try {
            $dto = $this->serializer->deserialize($request->getContent(), NewsDto::class, 'json');
        } catch (\Exception $e) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        // Solo actualizamos si nos envían el dato
        if ($dto->title) $news->setTitle($dto->title);
        if ($dto->description) $news->setDescription($dto->description);

        $this->dm->flush();

        return $this->json($news, 200, [], ['groups' => 'news:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $news = $this->dm->getRepository(News::class)->find($id);
        if (!$news) return $this->json(['error' => 'Not found'], 404);

        $this->dm->remove($news);
        $this->dm->flush();

        return $this->json(null, 204);
    }
}
