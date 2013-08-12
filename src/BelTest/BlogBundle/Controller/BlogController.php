<?php

namespace BelTest\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class BlogController extends Controller
{
    public function indexAction($page)
    {
        // On ne sait pas combien de pages il y a
        // Mais on sait qu'une page doit être supérieure ou égale à 1
        if( $page < 1 )
        {
            // On déclenche une exception NotFoundHttpException
            // Cela va afficher la page d'erreur 404 (on pourra personnaliser cette page plus tard d'ailleurs)
            throw $this->createNotFoundException('Page inexistante (page = '.$page.')');
        }

        // Ici, on récupérera la liste des articles, puis on la passera au template

        // Mais pour l'instant, on ne fait qu'appeler le template
        return $this->render('BelTestBlogBundle:Blog:index.html.twig');
    }


    public function voirAction($id)
    {
        // Ici, on récupérera l'article correspondant à l'id $id

        return $this->render('BelTestBlogBundle:Blog:voir.html.twig', array(
          'id' => $id
        ));
    }

    public function ajouterAction()
    {
        // La gestion d'un formulaire est particulière, mais l'idée est la suivante :

        if( $this->get('request')->getMethod() == 'POST' )
        {
            // Ici, on s'occupera de la création et de la gestion du formulaire

            $this->get('session')->getFlashBag()->add('notice', 'Article bien enregistré');

            // Puis on redirige vers la page de visualisation de cet article
            return $this->redirect( $this->generateUrl('sdzblog_voir', array('id' => 5)) );
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('BelTestBlogBundle:Blog:ajouter.html.twig');
    }

    public function modifierAction($id)
    {
        // Ici, on récupérera l'article correspondant à $id

        // Ici, on s'occupera de la création et de la gestion du formulaire

        return $this->render('BelTestBlogBundle:Blog:modifier.html.twig');
    }

    public function supprimerAction($id)
    {
        // Ici, on récupérera l'article correspondant à $id

        // Ici, on gérera la suppression de l'article en question

        return $this->render('BelTestBlogBundle:Blog:supprimer.html.twig');
    }
}