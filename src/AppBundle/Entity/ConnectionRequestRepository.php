<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ConnectionRequestRepository extends EntityRepository
{
    /**
     * @param City $city
     * @param bool $wantToLearn
     * @param bool $musicFriend
     *
     * @return ConnectionRequest[]
     */
    public function findForCity(City $city, $wantToLearn, $musicFriend)
    {
        return $this
            ->createQueryBuilder('cr')
            ->where('cr.wantToLearn = :wantToLearn')
            ->andWhere('cr.city = :city')
            ->andWhere('cr.musicFriend = :musicFriend')
            ->setParameters([
                'wantToLearn' => $wantToLearn,
                'city' => $city,
                'musicFriend' => $musicFriend,
            ])
            ->orderBy('cr.sortOrder', 'DESC')
            ->addOrderBy('cr.createdAt', 'ASC')
            ->getQuery()
            ->execute()
            ;
    }

    public function findOldRequests($city, $limit = 5, $offset = 0, $exclude = null)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->where('c.city = :city')
            ->setParameter('city', intval($city));
        if ($exclude !== null) {
            $qb->andWhere('c.id NOT IN (:id_list)')
                ->setParameter('id_list', $exclude);
        }

        return $qb->getQuery()->execute();
    }

    public function getStat()
    {
        $sql = 'SELECT 
				SUM(IF(c.want_to_learn = 0,0,1)) AS learners,
				SUM(IF(c.want_to_learn = 0,1,0)) AS established 
			FROM connection_request AS c
    	';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
