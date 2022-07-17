<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\RepositoryService;
use Exception;

class RepositoryController extends AbstractController
{
    private $repositoryService;

    function __construct(RepositoryService $repositoryService)
    {
        $this->repositoryService = $repositoryService;
    }

    /**
     * @Route("/", name="home",  methods={"GET"})
     */
    public function index(Request $request): Response
    {   
        return $this->render('index.html');
    }

    /**
     * @Route("/repositories", name="repositories",  methods={"POST"})
     */
    public function getRepositories(Request $request): Response
    {
        $organizationName = json_decode($request->getContent(), true)['name'];
        $repositoriesInfo =  $this->repositoryService->getRepositoriesInfo($organizationName);

        return new JsonResponse(
            $data = $repositoriesInfo['data'],
            $status = $repositoriesInfo['statusCode']
        );
    }
}
