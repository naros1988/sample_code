<?php

namespace ApiBundle\Controller;

use Doctrine\ORM\Query;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Elastica\Query as ElasticaQuery;

class ApiBaseController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ApiProblemFactoryInterface
     */
    protected $apiProblemFactory;

    private $paginator;

    /** @var PaginatedFinderInterface */
    protected $finder;

    public function __construct(
        SerializerInterface $serializer,
        RequestStack $requestStack,
        ApiProblemFactoryInterface $apiProblemFactory,
        PaginatedFinderInterface $finder
    ) {
        $this->serializer = $serializer;
        $this->request = $requestStack->getCurrentRequest();
        $this->apiProblemFactory = $apiProblemFactory;
        $this->finder = $finder;
    }

    /**
     * @param Paginator $paginator
     *
     * @required
     */
    public function setSlidingPagination(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    protected function paginatedView(Query $query, array $groups = ['Default'], array $extraData = [])
    {
        $requestQuery = $this->request->query;

        $page = ($requestQuery->get('page')) ? (int)$requestQuery->get('page') : 1;
        $limit = ($requestQuery->get('limit')) ? (int)$requestQuery->get('limit') : 10;

        /** @var SlidingPagination $pagination */
        $pagination = $this->paginator->paginate($query, $page, $limit, ['wrap-queries' => true]);

        $items = $pagination->getItems();
        if (empty($items)) {
            return $this->emptyView();
        } else {
            $data = [
                'current_page'   => $pagination->getCurrentPageNumber(),
                'items_per_page' => $pagination->getItemNumberPerPage(),
                'total_items'    => $pagination->getTotalItemCount(),
                'items'          => $items,
            ];

            if(!empty($extraData)){
                $data = array_merge($data, $extraData);
            }
        }

        return $this->serializedResponse($data, $groups);
    }

    protected function serializedResponse($data, array $groups = ['Default']): Response
    {
        $context = new SerializationContext();
        $context->setGroups($groups);
        $context->setSerializeNull(true);

        $response = $this->serializer->serialize($data, 'json', $context);

        return new JsonResponse($response, 200, [], true);
    }

    protected function notFoundView($message)
    {
        $apiProblem = $this->apiProblemFactory->create(Response::HTTP_NOT_FOUND, null, $message);

        throw new ApiProblemException($apiProblem);
    }

    protected function emptyView()
    {
        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    protected function errorView($errors)
    {
        $apiProblem = $this->apiProblemFactory->create(Response::HTTP_BAD_REQUEST, null, $errors);

        throw new ApiProblemException($apiProblem);
    }

    protected function serializeData($data, array $groups = ['Default'])
    {
        $context = new SerializationContext();
        $context->setGroups($groups);
        $context->setSerializeNull(true);

        return $this->serializer->serialize($data, 'json', $context);
    }

    protected function paginatedElasticView(ElasticaQuery $query, array $groups = ['Default'], array $extraData = [])
    {
        $requestQuery = $this->request->query;

        $page = ($requestQuery->get('page')) ? (int)$requestQuery->get('page') : 1;
        $limit = ($requestQuery->get('limit')) ? (int)$requestQuery->get('limit') : 10;

        $adapter = $this->finder->createPaginatorAdapter($query);
        $adapterResult = $adapter->getResults(($page - 1) * $limit, $limit);
        $totalItems = $adapter->getTotalHits();

        $items = $adapterResult->toArray();

        if (empty($items)) {
            return $this->emptyView();
        } else {
            $data = [
                'current_page'   => $page,
                'items_per_page' => $limit,
                'total_items'    => $totalItems,
                'items'          => $items,
            ];

            if (!empty($extraData)) {
                $data = array_merge($data, $extraData);
            }
        }

        return $this->serializedResponse($data, $groups);
    }
}
