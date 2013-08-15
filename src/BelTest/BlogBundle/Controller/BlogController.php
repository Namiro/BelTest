<?php

namespace BelTest\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Beltest\BlogBundle\Entity\Article;
use BelTest\BlogBundle\Form\ArticleType;
use BelTest\BlogBundle\Form\ArticleEditType;

class BlogController extends Controller
{
    public function indexAction($page)
    {        
        $liste_articles = $this->getDoctrine()
                         ->getManager()
                         ->getRepository('BelTestBlogBundle:Article')
                         ->getArticles(1, $page); // 3 articles par page : c'est totalement arbitraire !

        // On ajoute ici les variables page et nb_page à la vue
        return $this->render('BelTestBlogBundle:Blog:index.html.twig', array(
            'liste_articles'   => $liste_articles,
            'page'       => $page,
            'nombrePage' => ceil(count($liste_articles)/1)
        ));
    }


    public function voirAction(Article $article)
    {
        // On récupère l'EntityManager
        $em = $this->getDoctrine()
                   ->getManager();

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
        $article = new Article;

        $form = $this->createForm(new ArticleType, $article);

        // On récupère la requête
        $request = $this->get('request');

        // On vérifie qu'elle est de type POST
        if ($request->getMethod() == 'POST') 
        {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $article contient les valeurs entrées dans le formulaire par le visiteur
            $form->bind($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) 
            {
                // On l'enregistre notre objet $article dans la base de données
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();

                // On redirige vers la page de visualisation de l'article nouvellement créé
                return $this->redirect($this->generateUrl('belTestblog_voir', array('id' => $article->getId())));
            }
        }
        // À ce stade :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau

        return $this->render('BelTestBlogBundle:Blog:ajouter.html.twig', array(
          'form' => $form->createView(),
        ));
    }
    
    public function supprimerAction(Article $article)
    {
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'article contre cette faille
        $form = $this->createFormBuilder()->getForm();

        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') 
        {
            $form->bind($request);

            if ($form->isValid()) 
            {
                // On supprime l'article
                $em = $this->getDoctrine()->getManager();
                $em->remove($article);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');

                // Puis on redirige vers l'accueil
                return $this->redirect($this->generateUrl('belTestblog_accueil'));
            }
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('BelTestBlogBundle:Blog:supprimer.html.twig', array(
            'article' => $article,
            'form'    => $form->createView()
        ));
    }
    
    public function menuAction($nombre)
    {
        $liste = $this->getDoctrine()
                      ->getManager()
                      ->getRepository('BelTestBlogBundle:Article')
                      ->findBy(
                        array(),          // Pas de critère
                        array('date' => 'desc'), // On trie par date décroissante
                        $nombre,         // On sélectionne $nombre articles
                        0                // À partir du premier
                      );

        return $this->render('BelTestBlogBundle:Blog:menu.html.twig', array(
          'liste_articles' => $liste // C'est ici tout l'intérêt : le contrôleur passe les variables nécessaires au template !
        ));
    }
    
    public function modifierAction(Article $article)
    {
        $form = $this->createForm(new ArticleEditType, $article);

        $request = $this->get('request');

        if ($request->getMethod() == 'POST') 
        {
            $form->bind($request);
            
            if ($form->isValid()) 
            {
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();

                return $this->redirect($this->generateUrl('belTestblog_voir', array('id' => $article->getId())));
            }
        }

        return $this->render('BelTestBlogBundle:Blog:modifier.html.twig', array(
            'form' => $form->createView(),
            'article' => $article

        ));
    }
        }
