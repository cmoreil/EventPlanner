<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\SearchData;
use App\Entity\User;
use App\utils\UpdatingDatabase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    //private UpdatingDatabase $updatingDatabase;
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Event::class);
        $this->security = $security;
        //$this->updatingDatabase = $updatingDatabase;
    }

    public function add(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listOpenedEvents()
    {
        //$em = $this->getEntityManager();
        $date = date('Y-m-d h:i:s', strtotime("-30 days"));
        //dump(date('Y-m-d h:i:s'));
        //dd($date);

    return $this->createQueryBuilder('e')
        ->andWhere('e.endDateTime > :n30days')
        ->setParameter('n30days', $date)
        ->getQuery()
        ->getResult();

    //->setParameter('today', date('Y-m-d h:i:s'))

//        $qb = $this->createQueryBuilder('e')
//            ->andWhere('e.endDateTime > :today')
//            ->setParameter('today', date('Y-m-d h:i:s'))
//            ->setParameter('n30days', $date)
//            ->orderBy(' e.endDateTime  ', 'ASC');
//
//        $query = $qb->getQuery();
//
//        return $query->getResult();
    }


    public function findByIdForShow(int $id){

        //QUERY BUILDER
        $qb = $this->createQueryBuilder('e');
        $qb->leftJoin('e.location','location')
            ->leftJoin('e.participants','participants')
            ->addSelect('location')
            ->addSelect('participants')
            ->andWhere('e.id = :val')
            ->setParameter('val', $id);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function addParticipant(Event $event, User $user )
    {
        $event->addParticipant($user);
        $this->add($event, true);
    }
    public function removeParticipant(Event $event, User $user )
    {
        $event->removeParticipant($user);
        $this->add($event, true);
    }




//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findFromFilters(SearchData $searchData)
    {
        $query = $this
            ->createQueryBuilder('e')
            ->leftjoin('e.referentSite', 'site')
            ->leftjoin('e.location', 'loc')
            ->leftjoin('e.organizer', 'orga')
            ->leftjoin('e.status', 'stat')
            ->leftJoin('e.participants', 'part');

        /*
         * Attention, l'ordre des if pour les andWhere/orWhere est ici très important !
         * Il faut mettre en premier les conditions qui prennent un orWhere() pour les radio checkbox
         * PUIS mettre les conditions qui prennent toujours un andWhere (dates, le titre de la sortie, site référent etc.)
         * Sans ça Doctrine ne place pas bien les parenthèses et les opérateurs dans le SQL
         */
        //conditions qui peuvent s'ajouter sans s'exclure (correspond aux checkbox dans les filtres de recherche)
        if (!empty($searchData->getConnectedUserIsOrganizing())){
            $query = $query->andWhere('orga.id = :connectedUser')
                ->setParameter('connectedUser', $this->security->getUser());
        }

        if (!empty($searchData->getConnectedUserIsRegistered())){
            if (empty($searchData->getConnectedUserIsOrganizing())){
                 $query = $query->andWhere('part.id = :connectedUser')
                    ->setParameter('connectedUser', $this->security->getUser());
            }else {
                $query = $query->orWhere('part.id = :connectedUser')
                    ->setParameter('connectedUser', $this->security->getUser());
            }
        }

        if (!empty($searchData->getConnectedUserIsNotRegistered())){
            if (empty($searchData->getConnectedUserIsOrganizing()) &&
                empty($searchData->getConnectedUserIsRegistered())){
            $query = $query->andWhere(':connectedUser NOT MEMBER OF e.participants')
                ->setParameter('connectedUser', $this->security->getUser());
            }else {
                $query = $query->orWhere(':connectedUser NOT MEMBER OF e.participants')
                    ->setParameter('connectedUser', $this->security->getUser());
            }
        }

        if (!empty($searchData->getCancelledEvents())){
            if (empty($searchData->getConnectedUserIsOrganizing()) &&
                empty($searchData->getConnectedUserIsRegistered()) &&
                empty($searchData->getConnectedUserIsNotRegistered())) {
                $query = $query->andWhere('stat.id = :status')
                    ->setParameter('status', "6");
            }else {
                $query = $query->orWhere('stat.id = :status')
                    ->setParameter('status', "6");
            }
        }else {
            $query = $query->andWhere('stat.id <> :status')
                ->setParameter('status', "6");
        }

        //conditions qui se combinent pour exclure des résultats (correspond aux checkbox dans les filtres de recherche)
        $query->andWhere('e.startDateTime > :startsearch')
            ->andWhere('e.startDateTime < :endsearch')
            ->setParameter('startsearch', $searchData->getFromSearchDateTime())
            ->setParameter('endsearch', date_add($searchData->getToSearchDateTime(),new \DateInterval('P1D')));
        //le date_add juste au-dessus permet de contourner le fait que dans le formulaire, l'heure du dateTime est automatiquement setté
        //à minuit (ce qui écarte les events qui commencent le même jour que la date de fin du filtre)

        if (!empty($searchData->getReferentSite())){
            $query = $query->andWhere('site.id IN (:sites)')
                ->setParameter('sites', $searchData->getReferentSite());
        }

        if (!empty($searchData->getEventNameContains())){
            $query = $query->andWhere('e.name LIKE :searchName')
                ->setParameter('searchName', "%{$searchData->getEventNameContains()}%");
        }
        $query = $query->orderBy('e.startDateTime', 'DESC');
        return $query->getQuery()->getResult();
    }

    public function findAllExceptPastSortedByDate($orderDirection)
    {
        return $this->createQueryBuilder('e')
            ->leftjoin('e.referentSite', 'site')
            ->leftjoin('e.location', 'loc')
            ->leftjoin('e.organizer', 'orga')
            ->leftjoin('e.status', 'stat')
            ->leftJoin('e.participants', 'part')
            ->andWhere('e.status <> :status')
            ->setParameter('status', '5')
            ->andWhere('e.status <> :status2')
            ->setParameter('status2', '7')
            ->orderBy('e.startDateTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


}
