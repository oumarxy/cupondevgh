<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class OfertaRepository extends EntityRepository
{
    /**
     * Encuentra la oferta cuyo slug y ciudad coinciden con los indicados
     *
     * @param string $ciudad El slug de la ciudad
     * @param string $slug El slug de la oferta
     * @return mixed
     */
    public function findOferta($ciudad, $slug)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQuery('
            SELECT o, c, t
            FROM AppBundle:Oferta o
            JOIN o.ciudad c JOIN o.tienda t
            WHERE o.revisada = true
            AND o.slug = :slug
            AND c.slug = :ciudad
        ');
        $consulta->setParameter('slug', $slug);
        $consulta->setParameter('ciudad', $ciudad);
        $consulta->setMaxResults(1);
        return $consulta->getOneOrNullResult();
    }

    /**
     * Encuentra la oferta del día en la ciudad indicada
     *
     * @param string $ciudad El slug de la ciudad
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOfertaDelDia($ciudad)
    {
        $date = new \DateTime('today');
        $date->setTime(23, 59, 59);

        // $date = new \DateTime();
        // $date->setDate(2015, 10, 30);
        // 2015-10-30 13:00:00
        // $date->setTime(13, 00);

        $em = $this->getEntityManager();

        $dql = 'SELECT o, c, t
                  FROM AppBundle:Oferta o
                  JOIN o.ciudad c JOIN o.tienda t
                WHERE o.revisada = TRUE
                  AND o.fechaPublicacion < :fecha
                  AND c.slug = :ciudad
                ORDER BY o.fechaPublicacion DESC';

        $consulta = $em->createQuery($dql);
        $consulta->setParameter('ciudad', $ciudad);
        $consulta->setParameter('fecha', $date);
        $consulta->setMaxResults(1);

        return $consulta->getOneOrNullResult();

    }

    /**
     * Encuentra las cinco ofertas más cercanas a la ciudad indicada
     *
     * @param string $ciudad El slug de la ciudad
     * @return array
     */
    public function findCercanas($ciudad)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQuery('
            SELECT o, c
            FROM AppBundle:Oferta o
            JOIN o.ciudad c
            WHERE o.revisada = true
            AND o.fechaPublicacion <= :fecha
            AND c.slug != :ciudad
            ORDER BY o.fechaPublicacion DESC
        ');
        $consulta->setMaxResults(5);
        $consulta->setParameter('ciudad', $ciudad);
        $consulta->setParameter('fecha', new \DateTime('today'));

        return $consulta->getResult();
    }

    /**
     * Encuentra las cinco ofertas más recientes de la ciudad indicada.
     *
     * @param $ciudadId
     * @return array
     */
    public function findRecientes($ciudadId)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQuery('
            SELECT o, t
            FROM AppBundle:Oferta o JOIN o.tienda t
            WHERE o.revisada = true
            AND o.fechaPublicacion < :fecha
            AND o.ciudad = :id
            ORDER BY o.fechaPublicacion DESC
        ');
        $consulta->setMaxResults(5);
        $consulta->setParameter('id', $ciudadId);
        $consulta->setParameter('fecha', new \DateTime('today'));
        return $consulta->getResult();
    }

}