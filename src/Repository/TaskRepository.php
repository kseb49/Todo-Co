<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);

    }


    // /**
    //  * Retrieve tasks by its author
    //  *
    //  * @param [type] $id User id
    //  * @return void
    //  */
    // public function findByUsers($id)
    // {
    //     return $this->createQueryBuilder('t')
    //         ->andWhere('t.user = :val')
    //         ->setParameter('val', $id)
    //         ->orderBy('t.id', 'ASC')
    //         ->getQuery()
    //         ->getResult()
    //     ;

    // }

    /**
     * Retrieve all the tasks except those which match the ids
     *
     * @param string $user_id   Tasks of the user will be excluded
     * @param array  $tasks_ids Tasks in wich the user is mentionned will be excluded
     * @return array
     */
    public function findExcept(string $user_id, array|null $tasks_ids = null):array
    {
        if ($tasks_ids !== null) {
            return $this->createQueryBuilder('t')
                ->andWhere('t.user != :val')
                ->setParameter('val', $user_id)
                ->andWhere('t.id NOT IN (:tasks)')
                ->setParameter('tasks', $tasks_ids)
                ->getQuery()
                ->getResult()
            ;
        }

        return $this->createQueryBuilder('t')
            ->andWhere('t.user != :val')
            ->setParameter('val', $user_id)
            ->getQuery()
            ->getResult()
        ;

    }


}
