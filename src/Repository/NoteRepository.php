<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Note;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
final class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * @return list<Note>
     */
    public function searchByFilters(
        User $owner,
        ?string $search = null,
        ?string $status = null,
        ?string $category = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('note')
            ->andWhere('note.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('note.updatedAt', 'DESC');

        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere('(LOWER(note.title) LIKE :term OR LOWER(note.content) LIKE :term)')
                ->setParameter('term', '%'.mb_strtolower($search).'%');
        }

        if ($status !== null && $status !== '') {
            $queryBuilder
                ->andWhere('note.status = :status')
                ->setParameter('status', $status);
        }

        if ($category !== null && $category !== '') {
            $queryBuilder
                ->andWhere('LOWER(note.category) = :category')
                ->setParameter('category', mb_strtolower($category));
        }

        /** @var list<Note> $notes */
        $notes = $queryBuilder->getQuery()->getResult();

        return $notes;
    }

    /**
     * @return list<string>
     */
    public function findCategoriesForUser(User $owner): array
    {
        $rows = $this->createQueryBuilder('note')
            ->select('DISTINCT note.category AS category')
            ->andWhere('note.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('note.category', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $categories = [];

        foreach ($rows as $row) {
            $category = trim((string) ($row['category'] ?? ''));

            if ($category !== '') {
                $categories[] = $category;
            }
        }

        return $categories;
    }
}
