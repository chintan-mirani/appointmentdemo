<?php

namespace App\Repository;

use App\Entity\AppointmentUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppointmentUser>
 *
 * @method AppointmentUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppointmentUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppointmentUser[]    findAll()
 * @method AppointmentUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppointmentUser::class);
    }

    public function add(AppointmentUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AppointmentUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * @param array $userdId
     * @return array
     */
    public function getSelectedUsersAppointment($usersId, $appointmentId)
    {
        $query = $this->createQueryBuilder('au');
        $query->leftJoin('App\Entity\Appointment', 'a', \Doctrine\ORM\Query\Expr\Join::WITH, 'au.appointment = a.id')
            ->leftJoin('App\Entity\User', 'u', \Doctrine\ORM\Query\Expr\Join::WITH, 'au.user = u.id')
            ->where('au.user IN ('.implode(', ', $usersId).')');
        
            if ($appointmentId) {
                $query->andWhere('a.id != :appointment')
                    ->setParameter('appointment', $appointmentId);
            }

        $result = $query->getQuery()
        ->getResult();
                    
        return $result;
    }

//    /**
//     * @return AppointmentUser[] Returns an array of AppointmentUser objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AppointmentUser
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }



}
