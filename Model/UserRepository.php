<?php
/*
 *
 * @author Genar
 */
namespace ACS\ACSPanelUsersBundle\Model;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function qbSuperadminUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->from('ACS\ACSPanelUsersBundle\Entity\FosUser','usr')
            ->innerJoin('u.groups','g')
            ->where('g.roles LIKE :roles OR u.roles LIKE :roles')
            ->setParameter('roles', '%ROLE_SUPER_ADMIN%')
        ;
    }

    public function getSuperadminUsers()
    {
        return $this->qbSuperadminUsers()
            ->getQuery()
            ->getResult()
        ;
    }
}
