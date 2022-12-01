<?php

namespace App\Manager;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractManager
 *
 * Every manager should extends this class.
 *
 * @package AciaPro\AppBundle\Services\Manager
 */
abstract class AbstractManager
{
    private ?EntityRepository $repository = null;

    /**
     * @var string $entity
     */
    protected $entityClassName;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * AbstractManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        if (null === $this->entityClassName) {
            throw new \LogicException(
                'You must set $entityClassName attribute prior to call AbstractManager constructor.'
            );
        }

        $this->setEntityManager($entityManager);
    }

    /**
     * @return null|object
     */
    public function getReference(int $id)
    {
        try {
            return $this->entityManager->getReference($this->entityClassName, $id);
        } catch (ORMException) {
            return $this->find($id);
        }
    }

    /**
     * Allows entity manager overrideing
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository($this->entityClassName);
    }

    /**
     * @todo should be protected to avoid use from controller
     * Gets managed entity's repository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * Flushes all changes
     */
    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * Clear all changes
     * @param null $objectName
     */
    public function clear($objectName = null): void
    {
        $this->entityManager->clear($objectName);
    }

    /**
     * Finds an object by its primary key / identifier
     *
     * @return null|object
     */
    public function find(array|int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * Finds a single entity by a set of criteria
     *
     * @return null|object
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    /**
     * Finds entities by a set of criteria
     *
     * @param array $criteria An array used by findBy doctrine method
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     */
    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Finds all entities in the repository
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }
    
    /**
     * Finds all entities in the repository in array format
     */
    public function findAllInArray(): array
    {
        return $this->repository->createQueryBuilder('g')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Soft delete an entity
     *
     * @param $entity
     * @param UserInterface|null $user
     */
    public function softDelete($entity, UserInterface $user = null, bool $flush = true): void
    {
        if (!method_exists($entity, 'setDeletedAt')) {
            throw new \LogicException(sprintf('Entity %s is not soft deletable.', $entity::class));
        }

        $entity->setDeletedAt(new DateTime());

        if (null !== $user && method_exists($entity, 'setDeleteuser')) {
            $entity->setDeleteuser($user->getUserIdentifier());
        }

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Soft delete an array of object instances
     *
     *
     * @todo replace array by iterable from php 7.1
     */
    public function softDeleteMany(array $objects, bool $flush = true)
    {
        foreach ($objects as $object) {
            $this->softDelete($object);
        }

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Removes an object instance
     *
     * @todo: extends all entities from one interface
     *
     * @param mixed  $entity
     */
    public function delete($entity, bool $flush = true): void
    {
        $this->entityManager->remove($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Removes an array of object instances
     *
     *
     * @todo replace array by iterable from php 7.1
     */
    public function deleteMany(array $objects, bool $flush = true)
    {
        foreach ($objects as $object) {
            $this->delete($object, false);
        }

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Persists and optionally flushes an entity
     *
     * @todo: extends all entities from one interface
     *
     * @param mixed $object
     */
    public function save($object, bool $flush = true): void
    {
        $this->entityManager->persist($object);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Persists an array of object instances
     *
     * @todo replace array by iterable from php 7.1
     */
    public function saveMany(array $objects, bool $flush = true)
    {
        foreach ($objects as $object) {
            $this->save($object, false);
        }

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Refresh entity
     * @param $entity
     * @return mixed
     */
    public function refresh($entity)
    {
        $this->entityManager->refresh($entity);
        return $entity;
    }
}