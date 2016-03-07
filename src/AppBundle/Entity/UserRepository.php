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
    
    public function updateUserInfo($userInfo){
    	
    }
    
    public function getMatchedUsers($userId,$filters=[],$limit=null,$offset=null){
    	$em=$this->getEntityManager();
    	$user=$em->getRepository('AppBundle:User')->find($userId);
    	$where=[
    		'u.id <> :userId',
    		'u.wantToLearn <> :wantToLearn'
    	];
    	$params=[
	    	'userId'=>$user->getId(),
	    	'municipality'=>$user->getMunicipality(),
	    	'gender'=>$user->getGender(),
	    	'age'=>$user->getAge(),
	    	'children'=>$user->hasChildren(),
	    	'wantToLearn'=>$user->getWantToLearn()
    	];
    	if(sizeof($filters)>0){
    		$_filters=[];
    		foreach($filters as $key=>$filter){
    			$_key=key($filter);
    			$_filters[$_key]=$filter[$_key];
    		}
    		$filters=$_filters;
    		if(isset($filters['ageFrom'])&&isset($filters['ageTo'])){
    			$where[]='(u.age BETWEEN '.$filters['ageFrom'].' AND '.$filters['ageTo'].')';
    			unset($filters['ageFrom']);
    			unset($filters['ageTo']);
    		}
    		if(isset($filters['municipality'])){
    			$where[]='m.id='.$filters['municipality'];
    			unset($filters['municipality']);
    		}
    		if(sizeof($filters)>0){
		    	foreach($filters as $field=>$value){
		    		$where[]='u.'.$field.'='.(is_numeric($value)?$value:"'".$value."'");
		    	}
    		}
    	}
    	$dql='SELECT u.id,u.firstName,u.lastName,u.username,u.age,
    			u.gender,u.from,u.about,u.hasChildren,m.name as municipality,cat.name,
    			(
    				COUNT(cat.id)+
    				(CASE WHEN(u.municipality=:municipality) THEN 2 ELSE 0 END)+
    				(CASE WHEN((u.age-:age)<5) THEN 2 ELSE 0 END)+
    				(CASE WHEN(u.gender=:gender) THEN 1 ELSE 0 END)+
    				(CASE WHEN((u.hasChildren>0) AND (1>:children)) THEN 2 ELSE 0 END)
    			) AS summary_points
    		FROM AppBundle:User u
    			JOIN u.categories cat
    			JOIN u.municipality m
    		WHERE '.implode(' AND ',$where).'
    			GROUP BY u.id 
    		ORDER BY summary_points DESC
    	';
    	$query=$em->createQuery($dql)->setParameters($params);
    	if($limit!==null&&$offset!==null){
    		$query->setMaxResults(intval($limit));//->setFirstResult(intval($offset));
    	}
    	$result=[
    		'result'=>$query->getResult(),
    		'user'=>$user
    	];
    	return $result;
    }
}
