<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Serializer\JMSSerializerAdapter;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\RestBundle\DependencyInjection\Compiler\JMSHandlersPass;
use FOS\RestBundle\View\ViewHandlerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ArticleController extends AbstractFOSRestController // AbstractController 
{ 
    /**
     * @Get(
     *     path = "/articles/{id}",
     *     name = "app_article_show",
     *     requirements = {"id"="\d+"}
     * )
     * @View(
     *     statusCode = 200
     * )
     */
    public function showAction(Article $article)
    {
        return $article;
    }

    /**
     * @Rest\Post("/articles")
     * @Rest\View(statusCode = 201)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction(Request $request,Article $article,SerializerInterface $seri)
    {
       // dd($article);
        $data=$request->getContent();
        $em=$this->getDoctrine()->getManager();
        $dataserialized=$seri->deserialize($data,Article::class,"json");
        dd($dataserialized);
        $em->persist($article);
        $em->flush();
       

        //return $article;
        return $this->view($article, 
                           Response::HTTP_CREATED, 
                           ['Location' => $this->generateUrl('app_article_show', ['id' => $article->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }
    /**
     * @Rest\Post("/articles1")
     * @Rest\View(statusCode = 201)
     * 
     */
    public function createAction1(Request $request)
    {
         $test[]=$request->request->get('num1');
         $test[]=$request->request->get('num2');
        // dd($article);
        //$data=$request->getContent();
        //$em=$this->getDoctrine()->getManager();
        //$dataserialized=$seri->deserialize($data,Article::class,"json");
        dd($test);
        //$em->persist($article);
        //$em->flush();
       

        //return $article;
        //return $this->view($article, 
          //                 Response::HTTP_CREATED, 
           //                ['Location' => $this->generateUrl('app_article_show', ['id' => $article->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }
}
