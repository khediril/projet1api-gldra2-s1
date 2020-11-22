<?php

namespace App\Repository;



use Pagerfanta\Pagerfanta;
use Doctrine\ORM\QueryBuilder;
//use PagerFanta\Adapater\DoctrineORMAdapter;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractRepository extends ServiceEntityRepository
{
    protected function paginate(QueryBuilder $qb, $limit = 20, $offset = 0)
    {
        if (0 == $limit || 0 == $offset) {
            throw new \LogicException('$limit & $offstet must be greater than 0.');
        }
        //$pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager = new Pagerfanta(
            new QueryAdapter($qb)
        );
        
        $currentPage = ceil(($offset + 1) / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage((int) $limit);
        
        return $pager;
    }
}