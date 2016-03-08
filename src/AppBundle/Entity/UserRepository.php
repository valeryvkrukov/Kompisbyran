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

    public function updateUserInfo($userInfo)
    {
    }

    public function getMatchedUsers($userId, $filters = [], $limit = null, $offset = null)
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository('AppBundle:User')->find($userId);
        $_catList = [];
        foreach ($user->getCategories() as $_cat) {
            $_catList[] = $_cat->getId();
        }
        $where = [
            'u.id <> :userId',
            'u.want_to_learn <> :wantToLearn',
        ];
        $params = [
            'userId' => intval($user->getId()),
            'municipality' => intval($user->getMunicipality()->getId()),
            'gender' => $user->getGender(),
            'age' => intval($user->getAge()),
            'children' => intval($user->hasChildren()),
            'wantToLearn' => intval($user->getWantToLearn()),
        ];
        if (sizeof($filters) > 0) {
            $_filters = [];
            foreach ($filters as $key => $filter) {
                $_key = key($filter);
                $_filters[$_key] = $filter[$_key];
            }
            $filters = $_filters;
            if (isset($filters['ageFrom']) && isset($filters['ageTo'])) {
                $where[] = '(u.age BETWEEN '.$filters['ageFrom'].' AND '.$filters['ageTo'].')';
                unset($filters['ageFrom']);
                unset($filters['ageTo']);
            }
            if (isset($filters['municipality'])) {
                $where[] = 'u.municipality_id='.$filters['municipality'];
                unset($filters['municipality']);
            }
            if (sizeof($filters) > 0) {
                foreach ($filters as $field => $value) {
                    $where[] = 'u.'.$field.'='.(is_numeric($value) ? $value : "'".$value."'");
                }
            }
        }
        $sql = '
    		SELECT u.*,
    			(
    				(SELECT COUNT(*)*3 FROM users_categories AS c WHERE c.user_id=u.id AND c.category_id IN('.implode(', ', $_catList).'))+
    				(CASE WHEN(u.municipality_id=:municipality) THEN 2 ELSE 0 END)+
    				(CASE WHEN((u.age-:age)<5) THEN 2 ELSE 0 END)+
    				(CASE WHEN(u.gender=:gender) THEN 1 ELSE 0 END)+
    				(CASE WHEN((u.has_children>0) AND (0<:children)) THEN 2 ELSE 0 END)
    			) AS summary_points
    		FROM fos_user AS u
    			WHERE '.implode(' AND ', $where).'
    		GROUP BY u.id 
    			ORDER BY summary_points DESC
    	';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = [
            'result' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'user' => $user,
        ];

        return $result;
    }
}
