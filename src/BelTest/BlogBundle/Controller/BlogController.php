<?php

namespace BelTest\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Beltest\BlogBundle\Entity\Article;
use Beltest\BlogBundle\Entity\Image;
use Beltest\BlogBundle\Entity\Commentaire;
use Beltest\BlogBundle\Entity\ArticleCompetence;

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
        
        // On récupère l'EntityManager
        $em = $this->getDoctrine()
                   ->getManager();

        $liste_articles = $em->getRepository('BelTestBlogBundle:Article')
                                 ->findAll();
        

        return $this->render('BelTestBlogBundle:Blog:index.html.twig', array(
            'liste_articles' => $liste_articles
        ));
    }


    public function voirAction($id)
    {
        // On récupère l'EntityManager
        $em = $this->getDoctrine()
                   ->getManager();

        // On récupère l'entité correspondant à l'id $id
        $article = $em->getRepository('BelTestBlogBundle:Article')
                      ->find($id);

        // Ou null si aucun article n'a été trouvé avec l'id $id
        if($article === null)
        {
            throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
        }

        // On récupère les articleCompetence pour l'article $article
        $liste_articleCompetence = $em->getRepository('BelTestBlogBundle:ArticleCompetence')
                                      ->findByArticle($article->getId());
        
        // Puis modifiez la ligne du render comme ceci, pour prendre en compte l'article :
        return $this->render('BelTestBlogBundle:Blog:voir.html.twig', array(
            'article'        => $article,
            'liste_articleCompetence' => $liste_articleCompetence
        ));
    }

    public function ajouterAction()
    {

        // Création de l'entité
        $article = new Article();
        $article->setTitre('Mon dernier weekend');
        $article->setAuteur('Bibi');
        $article->setContenu("C'était vraiment super et on s'est bien amusé.");
        
        // Création d'un premier commentaire
        $commentaire1 = new Commentaire();
        $commentaire1->setAuteur('winzou');
        $commentaire1->setContenu('On veut les photos !');

        // Création d'un deuxième commentaire, par exemple
        $commentaire2 = new Commentaire();
        $commentaire2->setAuteur('Choupy');
        $commentaire2->setContenu('Les photos arrivent !');

        // On lie les commentaires à l'article
        $commentaire1->setArticle($article);
        $commentaire2->setArticle($article);
        
        // Création de l'entité Image
        $image = new Image();
        $image->setUrl('http://uploads.siteduzero.com/icones/478001_479000/478657.png');
        $image->setAlt('Logo Symfony2');
        
        // On lie l'image à l'article
        $article->setImage($image);
        
        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Étape 1 : On « persiste » les entités
        $em->persist($article);
        // Pour cette relation pas de cascade, car elle est définie dans l'entité Commentaire et non Article
        // On doit donc tout persister à la main ici
        $em->persist($commentaire1);
        $em->persist($commentaire2);

        // Étape 2 : On « flush » tout ce qui a été persisté avant
        $em->flush();
        
        
        // Les compétences existent déjà, on les récupère depuis la bdd
        $liste_competences = $em->getRepository('BelTestBlogBundle:Competence')
                                ->findAll(); // Pour l'exemple, notre Article contient toutes les Competences

        // Pour chaque compétence
        foreach($liste_competences as $i => $competence)
        {
            // On crée une nouvelle « relation entre 1 article et 1 compétence »
            $articleCompetence[$i] = new ArticleCompetence;

            // On la lie à l'article, qui est ici toujours le même
            $articleCompetence[$i]->setArticle($article);
            // On la lie à la compétence, qui change ici dans la boucle foreach
            $articleCompetence[$i]->setCompetence($competence);

            // Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
            $articleCompetence[$i]->setNiveau('Expert');

            // Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
            $em->persist($articleCompetence[$i]);
        }

        // On déclenche l'enregistrement
        $em->flush();

        if ($this->getRequest()->getMethod() == 'POST') {
          $this->get('session')->getFlashBag()->add('info', 'Article bien enregistré');
          return $this->redirect( $this->generateUrl('sdzblog_voir', array('id' => $article->getId())));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('BelTestBlogBundle:Blog:ajouter.html.twig');
    }

    public function modifierAction($id)
    {
        // On récupère l'EntityManager
        $em = $this->getDoctrine()
                   ->getManager();

        // On récupère l'entité correspondant à l'id $id
        $article = $em->getRepository('BelTestBlogBundle:Article')
                      ->find($id);

        if ($article === null) {
            throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
        }

        // On récupère toutes les catégories :
        $liste_categories = $em->getRepository('BelTestBlogBundle:Categorie')
                               ->findAll();

        // On boucle sur les catégories pour les lier à l'article
        foreach($liste_categories as $categorie)
        {
            $article->addCategorie($categorie);
        }
         
        // Inutile de persister l'article, on l'a récupéré avec Doctrine

        // Étape 2 : On déclenche l'enregistrement
        $em->flush();
        
        // Puis modifiez la ligne du render comme ceci, pour prendre en compte l'article :
        return $this->render('BelTestBlogBundle:Blog:modifier.html.twig', array(
            'article' => $article
        ));
    }
    
public function supprimerAction($id)
  {
        // On récupère l'EntityManager
        $em = $this->getDoctrine()
                   ->getManager();

        // On récupère l'entité correspondant à l'id $id
        $article = $em->getRepository('BelTestBlogBundle:Article')
                      ->find($id);

        if ($article === null) {
            throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
        }

        // On récupère toutes les catégories :
        $liste_categories = $em->getRepository('BelTestBlogBundle:Categorie')
                               ->findAll();

        // On enlève toutes ces catégories de l'article
        foreach($liste_categories as $categorie)
        {
            // On fait appel à la méthode removeCategorie() dont on a parlé plus haut
            // Attention ici, $categorie est bien une instance de Categorie, et pas seulement un id
            $article->removeCategorie($categorie);
        }

        // On n'a pas modifié les catégories : inutile de les persister

        // On a modifié la relation Article - Categorie
        // Il faudrait persister l'entité propriétaire pour persister la relation
        // Or l'article a été récupéré depuis Doctrine, inutile de le persister

        // On déclenche la modification
        $em->flush();

        return new Response('OK');
  }
    
    public function menuAction($nombre)
    {
        // On fixe en dur une liste ici, bien entendu par la suite on la récupérera depuis la BDD !
        $liste = array(
          array('id' => 2, 'titre' => 'Mon dernier weekend !'),
          array('id' => 5, 'titre' => 'Sortie de Symfony2.1'),
          array('id' => 9, 'titre' => 'Petit test')
        );

        return $this->render('BelTestBlogBundle:Blog:menu.html.twig', array(
          'liste_articles' => $liste // C'est ici tout l'intérêt : le contrôleur passe les variables nécessaires au template !
        ));
    }
}