<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Exception\ResourceValidationException;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
#use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ArticleController extends AbstractFOSRestController // AbstractController 
{
    //////////////////////////////////////////////////////////////////////////////
    // Exemple de sérialisation 
    //////////////////////////////////////////////////////////////////////////////
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
    //////////////////////////////////////////////////////////////////////////////
    // Exemple de désérialisation - sans validation
    //////////////////////////////////////////////////////////////////////////////
    /**
     * @Rest\Post("/articles1",name="app_article_create")
     * @Rest\View(statusCode = 201)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction1(Request $request, Article $article, EntityManagerInterface $em)
    {
        //dd($article);      
        // il faut valider l'entité avant de la persister 
        // définir les contraites de validation dans les entités
                
        $em->persist($article);
        $em->flush();


        return $article;
       /* return $this->view(
            $article,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_article_show', ['id' => $article->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]
        );*/
    }
    //////////////////////////////////////////////////////////////////////////////
    // Exemple de désérialisation - avec validation symfony
    //////////////////////////////////////////////////////////////////////////////
    /**
     * @Rest\Post("/articles2",name="app_article_create")
     * @Rest\View(statusCode = 201)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction2(Request $request, Article $article, EntityManagerInterface $em,ValidatorInterface $validator)
    {
        //dd($article);      
        // il faut valider l'entité avant de la persister 
        // définir les contraites de validation dans les entités
        $errors = $validator->validate($article);

        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        
        $em->persist($article);
        $em->flush();


        return $article;
      
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Exemple de désérialisation - avec validation automatique de FODRestBundle
    // Etape 1 : Configuration 
    // fos_rest:
    //      # …
    //      body_converter:
    //      enabled: true
    //      validate: true
    //      validation_errors_argument: violations
    //      
    // validate: true : permet de faire que la validation de l'objet converti soit déclenchée 
    //                  juste avant qu'il soit passé à l'action ;
    // validation_errors_argument: violations : il s'agit du nom de l'argument contenant un objet de type  
    // ConstraintViolationList, contenant toutes les erreurs résultantes de la validation de l'objet.
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Rest\Post("/articles3",name="app_article_create")
     * @Rest\View(statusCode = 201)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction3(Request $request, Article $article, EntityManagerInterface $em,ConstraintViolationList $violations)
    {
        // il faut valider l'entité avant de la persister 
        // définir les contraites de validation dans les entités
        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }
                
        $em->persist($article);
        $em->flush();


        return $article;
       
    }




    ////////////////////////////////////////////////////////////////////////////////////////
    // Récupérer les paramètres grâce au param fetcher listener
    // La variable de configuration est  fos_rest.param_fetcher_listener 
    // et peut prendre deux valeurs :  true ou  force.
    // Cas1: fos_rest.param_fetcher_listener = true
    ///////////////////////////////////////////////////////////////////////////////////////
    /**
     * @Rest\Get("/articles1", name="app_article_list1")
     * @Rest\QueryParam(name="order")
     */
    public function listAction1(ParamFetcherInterface $paramFetcher)
    {
        dd($paramFetcher->get('order'));
        //return new Response("test");
    }

    /////////////////////////////////////////////////////////////////////////////////////
    // Cas1: fos_rest.param_fetcher_listener = force  c'est la valeur qui sera utilisée
    /////////////////////////////////////////////////////////////////////////////////////
    /**
     * @Rest\Get("/articles2", name="app_article_list2")
     * @Rest\QueryParam(name="order")
     */
    public function listAction($order)
    {
        dd($order);
        //return new Response("test");
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    // Validation du paramètre order 
    //    - la valeur attendue ne peut être que  asc  ou  desc ;
    //    - dans le cas où le paramètre n'est pas renseigné, la valeur par défaut doit être asc ;
    //    - le paramètre a pour nom  order.
    //
    // On peut indiquer n'importe quelle expression régulière dans l'option  requirements.
    //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @Rest\Get("/articles3", name="app_article_list3")
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     */
    public function listAction3($order)
    {
        dd($order);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    // Exemple avec RequestParam : 
    // On offre la possibilité de rechercher des articles via un terme 'search'. 
    // La validation de ce paramètre est selon les critères suivants :
    //   - la valeur attendue doit être des caractères allant de a à z et/ou de A à Z ;
    //   - dans le cas où le paramètre n'est pas renseigné, il doit être  null ;
    //   - la valeur de ce paramètre peut être null;
    //   - le paramètre a pour nom search.
    //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @Rest\Post("/articles4", name="app_article_list4")
     * @Rest\RequestParam(
     *     name="search",
     *     requirements="[a-zA-Z0-9]*",
     *     default=null,
     *     nullable=true,
     *     description="Search query to look for articles"
     * )
     */
    public function listAction4($search)
    {
        dd($search);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////
    // Liste de ressources non paginée
    //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @Rest\Get("/articles5", name="app_article_list5")
     * @View(
     *     statusCode = 200
     * )
     */
    public function listAction5(ArticleRepository $repos)
    {
        
        $articles = $repos->findAll();
        
        return $articles;
        // les deux ligne précédentes peuvent être remplacé par 
        // return $repos->findAll();
    }
    //////////////////////////////////////////////////////////////////////////////////////////////
    // Paginez une liste de ressources
    // installer le bundle BabDev/pagerfanta-bundle qui integre la librairie PagerFanta (à la place de white-october/pagerfanta-bundle)
    // composer require babdev/pagerfanta-bundle
    // Documentation : https://www.babdev.com/open-source/packages/pagerfantabundle/docs/2.x/intro
    //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @Rest\Get("/articles6", name="app_article_list6")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]*",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of movies per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="0",
     *     description="The pagination offset"
     * )
     * @Rest\View()
     */
    public function listAction6($keyword,$order,$limit,$offset,ArticleRepository $repos)
    {
        $pager = $repos->search($keyword,$order,$limit,$offset);
           
        return $pager->getCurrentPageResults();
    }
    
    // …
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Exemple de désérialisation - avec validation automatique de FODRestBundle
    // + gestion d'erreur par configuration
    // configuration :
    // exception:
    //     enabled: true
    //     codes:
    //         { App\Exception\ResourceValidationException: 400 }
    //  Créer la classe ResourceValidationException qui étend Exception
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Rest\Post("/articles31",name="app_article_create31")
     * @Rest\View(statusCode = 201)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction31(Request $request, Article $article, EntityManagerInterface $em,ConstraintViolationList $violations)
    {
        // il faut valider l'entité avant de la persister 
        // définir les contraites de validation dans les entités
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            //throw new ResourceValidationException($message);
            throw new ResourceValidationException($message);
        }
                
        $em->persist($article);
        $em->flush();


        return $article;
       
    }

}
