<?php

namespace BelTest\BlogBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
 
class BlogController extends Controller
{
  public function indexAction()
  {
    return $this->render('BelTestBlogBundle:Blog:index.html.twig', array('nom' => 'winzou'));
  }
}