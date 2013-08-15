<?php

namespace BelTest\BlogBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends EntityRepository
{
    public function getArticles($nombreParPage, $page)
    { 
        // On ne sait pas combien de pages il y a
        // Mais on sait qu'une page doit être supérieure ou égale à 1
        if( $page < 1 )
        {
            throw new \InvalidArgumentException('L\'argument $page ne peut être inférieur à 1 (valeur : "'.$page.'").');
        }
        
        $query = $this->createQueryBuilder('a')
                        ->addSelect('i')
                        ->addSelect('c')
                        ->leftJoin('a.image', 'i')
                        ->leftJoin('a.categories', 'c')
                        ->orderBy('a.date', 'DESC')
                        ->getQuery();

        // On définit l'article à partir duquel commencer la liste
        $query->setFirstResult(($page-1) * $nombreParPage)
        // Ainsi que le nombre d'articles à afficher
              ->setMaxResults($nombreParPage);

        // Enfin, on retourne l'objet Paginator correspondant à la requête construite
        // (n'oubliez pas le use correspondant en début de fichier)
        return new Paginator($query);

    }
}