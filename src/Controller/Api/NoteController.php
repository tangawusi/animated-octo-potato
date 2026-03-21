<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class NoteController extends AbstractController
{
    /**
     * @Route("/api/notes", name="api_notes_index", methods={"GET"})
     */
    public function index(Request $request, NoteRepository $noteRepository): JsonResponse
    {
        $user = $this->requireUser();

        $search = trim((string) $request->query->get('q', ''));
        $status = trim((string) $request->query->get('status', ''));
        $category = trim((string) $request->query->get('category', ''));

        $notes = $noteRepository->searchByFilters(
            $user,
            $search !== '' ? $search : null,
            $status !== '' ? $status : null,
            $category !== '' ? $category : null
        );

        return $this->json([
            'items' => array_map(static function (Note $note): array {
                return $note->toArray();
            }, $notes),
            'filters' => [
                'statuses' => Note::STATUSES,
                'categories' => $noteRepository->findCategoriesForUser($user),
            ],
        ]);
    }

    /**
     * @Route("/api/notes", name="api_notes_create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->requireUser();

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return $this->json([
                'message' => 'Invalid JSON payload.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $title = trim((string) ($payload['title'] ?? ''));
        $content = trim((string) ($payload['content'] ?? ''));
        $category = trim((string) ($payload['category'] ?? ''));
        $status = trim((string) ($payload['status'] ?? Note::STATUS_NEW));

        if ($title === '' || $content === '' || $category === '') {
            return $this->json([
                'message' => 'Title, content and category are required.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!Note::isValidStatus($status)) {
            return $this->json([
                'message' => 'Status must be one of: '.implode(', ', Note::STATUSES).'.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $note = new Note();
        $note->setOwner($user);
        $note->setTitle($title);
        $note->setContent($content);
        $note->setCategory($category);
        $note->setStatus($status);

        $entityManager->persist($note);
        $entityManager->flush();

        return $this->json([
            'message' => 'Note created successfully.',
            'item' => $note->toArray(),
        ], JsonResponse::HTTP_CREATED);
    }

    private function requireUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }

        return $user;
    }
}
