<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllWithCategoryJoinAssoc()
    {
        $sql = "
            SELECT u.id, u.email, u.first_name, u.last_name, u.want_to_learn, u.gender, u.age, u.from_country,
                GROUP_CONCAT(uc.category_id) as category_ids
            FROM fos_user u
            LEFT JOIN users_categories uc on u.id = uc.user_id
            WHERE u.roles != 'a:0:{}'
            GROUP BY u.id
            ORDER BY u.id";

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getMatchedUsers($userId){
    	$em=$this->getEntityManager();
    	$user=$em->getRepository('AppBundle:User')->find($userId);
    	$dql='SELECT DISTINCT u.id,u.firstName,u.lastName,u.username,u.age,
    			u.gender,u.from,u.about,u.hasChildren,
    			(	
    				COUNT(cat.id)+
    				(CASE WHEN(u.municipality=:municipality) THEN 2 ELSE 0 END)+
    				(CASE WHEN((u.age-:age)<5) THEN 2 ELSE 0 END)+
    				(CASE WHEN(u.gender=:gender) THEN 1 ELSE 0 END)+
    				(CASE WHEN((u.hasChildren>0) AND (1>:children)) THEN 2 ELSE 0 END)
    			) AS summary_points
    		FROM AppBundle:User u
    			LEFT JOIN u.categories cat
    		WHERE u.id <> :userId AND u.wantToLearn <> :wantToLearn
    			ORDER BY summary_points DESC
    	';
    	$result=[
    		'result'=>$em->createQuery($dql)
	    		->setParameters([
	    			'userId'=>$user->getId(),
	    			'municipality'=>$user->getMunicipality(),
	    			'gender'=>$user->getGender(),
	    			'age'=>$user->getAge(),
	    			'children'=>$user->hasChildren(),
	    			'wantToLearn'=>$user->getWantToLearn()
	    		])
	    		->getResult(),
    		'user'=>$user
    	];
    	return $result;
    }
}
