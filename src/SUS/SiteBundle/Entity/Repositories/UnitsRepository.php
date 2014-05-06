<?php

namespace SUS\SiteBundle\Entity\Repositories;

use SUS\SiteBundle\Entity\UnitCategory;
use SUS\SiteBundle\Entity\UnitFy;

class UnitsRepository extends BaseRepository
{
    public function findCategories($filters = array()) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u.categoryName');
        $qb->from($this->_entityName, 'u');
        $qb->groupBy('u.categoryName');
        if(isset($filters['name']) && $filters['name'] != '') {
            $qb->andWhere('u.categoryName LIKE :catname');
            $qb->setParameter('catname', '%'.$filters['name'].'%');
        }
        $categories = $qb->getQuery()->getResult();
        $catobjects = array();
        foreach($categories as $curCategory) {
            if($curCategory['categoryName'] != '') {
                $catobject = new UnitCategory();
                $catobject->setName($curCategory['categoryName']);
                $catobjects[] = $catobject;
            }
        }
        return $catobjects;
    }

    public function findFys($filters = array()) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u.fyName, u.fyInitials');
        $qb->from($this->_entityName, 'u');
        $qb->groupBy('u.fyName, u.fyInitials');
        if(isset($filters['name']) && $filters['name'] != '') {
            $qb->andWhere('u.fyName LIKE :fyname');
            $qb->setParameter('fyname', '%'.$filters['name'].'%');
        }
        $fys = $qb->getQuery()->getResult();
        $fyobjects = array();
        foreach($fys as $curFy) {
            if($curFy['fyName'] != '') {
                $fyobject = new UnitFy();
                $fyobject->setName($curFy['fyName']);
                $fyobject->setInitials($curFy['fyInitials']);
                $fyobjects[] = $fyobject;
            }
        }
        return $fyobjects;
    }
}