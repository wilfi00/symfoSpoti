<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Kerox\OAuth2\Client\Provider\SpotifyResourceOwner as SpotifyResourceOwner;
use League\OAuth2\Client\Token\AccessToken;


/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function createFromSpotify(SpotifyResourceOwner $spotifyUser, AccessToken $token): User
    {
        $user = new User();
        $user->setUuid($spotifyUser->getId());
        $user->setUsername($spotifyUser->getDisplayName());
        $user->setImageUrl(User::getImageUrlFromSpotifyInformations($spotifyUser));
        $user->setAccessToken($token->getToken());
        $user->setRefreshToken($token->getRefreshToken());
        $user->setLastConn();
        $this->_em->persist($user);
        $this->_em->flush();
        return $user;
    }
    
    public function updateFromSpotify(User $user, SpotifyResourceOwner $spotifyUser, AccessToken $token): User
    {
        $user->setUsername($spotifyUser->getDisplayName());
        $user->setImageUrl(User::getImageUrlFromSpotifyInformations($spotifyUser));
        $user->setAccessToken($token->getToken());
        $user->setRefreshToken($token->getRefreshToken());
        $user->setLastConn();
        $this->_em->persist($user);
        $this->_em->flush();
        return $user;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
