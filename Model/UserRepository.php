<?php

namespace WH\UserBundle\Model;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserRepository extends EntityRepository
{

    public function getQuery()
    {

        return $this
            ->createQueryBuilder('user')
            ->orderBy('user.lastname', 'ASC');
    }



    public function get($type = 'all', $options = array(), $admin = false)
    {

        $qb = $this->getQuery();

        foreach ($options as $key => $value) {

            switch ($key) {

                case 'limit':
                    $qb->setMaxResults($value);
                    break;

                case 'order':
                    $qb->orderBy('user.createdAt', $value);
                    break;

                case 'conditions':

                    foreach($value as $k => $v) {

                        if(empty($v)) continue;

                        switch($k) {

                            case 'Search' :

                                $qb->orWhere('user.firstname LIKE :search');
                                $qb->orWhere('user.lastname LIKE :search');
                                $qb->orWhere('user.email LIKE :search');
                                $qb->setParameter('search', '%'.$v.'%');

                                break;


                            default :

                                $cond = preg_replace('#\.#', '', $k);
                                $cond = strtolower($cond);


                                $qb->andWhere($k. ' = :'.$cond);
                                $qb->setParameter($cond, $v);

                                break;


                        }



                    }

            }

        }

        $query = $qb->getQuery();

        switch ($type) {

            case 'query' :

                return $query;

            break;

            case 'all':

                return $query->getResult();

                break;

            case 'one':

                return $query->getOneOrNullResult();

                break;

            case 'paginate':

                if (!empty($typeOptions['page'])) {

                    $qb->setFirstResult(($typeOptions['page'] - 1) * $typeOptions['limit']);
                }

                if (!empty($typeOptions['limit'])) {

                    $qb->setMaxResults($typeOptions['limit']);
                }

                return new Paginator($qb, true);

                break;

        }

        return false;

    }



}

