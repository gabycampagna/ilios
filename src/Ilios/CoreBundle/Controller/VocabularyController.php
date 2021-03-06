<?php

namespace Ilios\CoreBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ilios\CoreBundle\Exception\InvalidFormException;
use Ilios\CoreBundle\Entity\VocabularyInterface;

/**
 * Class VocabularyController
 * @package Ilios\CoreBundle\Controller
 * @RouteResource("Vocabularies")
 */
class VocabularyController extends FOSRestController
{
    /**
     * Get a Vocabulary.
     *
     * @ApiDoc(
     *   section = "Vocabulary",
     *   description = "Get a Vocabulary.",
     *   resource = true,
     *   requirements={
     *     {
     *        "name"="id",
     *        "dataType"="integer",
     *        "requirement"="\d+",
     *        "description"="Vocabulary identifier."
     *     }
     *   },
     *   output="Ilios\CoreBundle\Entity\Vocabulary",
     *   statusCodes={
     *     200 = "Vocabulary.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        $vocabulary = $this->getOr404($id);

        $authChecker = $this->get('security.authorization_checker');
        if (! $authChecker->isGranted('view', $vocabulary)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        $answer['vocabularies'][] = $vocabulary;

        return $answer;
    }

    /**
     * Get all Vocabularies.
     *
     * @ApiDoc(
     *   section = "Vocabulary",
     *   description = "Get all Vocabularies.",
     *   resource = true,
     *   output="Ilios\CoreBundle\Entity\Vocabulary",
     *   statusCodes = {
     *     200 = "List of all Vocabularies",
     *     204 = "No content. Nothing to list."
     *   }
     * )
     *
     * @QueryParam(
     *   name="offset",
     *   requirements="\d+",
     *   nullable=true,
     *   description="Offset from which to start listing notes."
     * )
     * @QueryParam(
     *   name="limit",
     *   requirements="\d+",
     *   default="20",
     *   description="How many notes to return."
     * )
     * @QueryParam(
     *   name="order_by",
     *   nullable=true,
     *   array=true,
     *   description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC"
     * )
     * @QueryParam(
     *   name="filters",
     *   nullable=true,
     *   array=true,
     *   description="Filter by fields. Must be an array ie. &filters[id]=3"
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $orderBy = $paramFetcher->get('order_by');
        $criteria = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : [];
        $criteria = array_map(function ($item) {
            $item = $item == 'null' ? null : $item;
            $item = $item == 'false' ? false : $item;
            $item = $item == 'true' ? true : $item;

            return $item;
        }, $criteria);

        $manager = $this->container->get('ilioscore.vocabulary.manager');
        $result = $manager->findBy($criteria, $orderBy, $limit, $offset);

        $authChecker = $this->get('security.authorization_checker');
        $result = array_filter($result, function ($entity) use ($authChecker) {
            return $authChecker->isGranted('view', $entity);
        });

        //If there are no matches return an empty array
        $answer['vocabularies'] = $result ? array_values($result) : [];

        return $answer;
    }

    /**
     * Create a Vocabulary.
     *
     * @ApiDoc(
     *   section = "Vocabulary",
     *   description = "Create a Vocabulary.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\VocabularyType",
     *   output="Ilios\CoreBundle\Entity\Vocabulary",
     *   statusCodes={
     *     201 = "Created Vocabulary.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        try {
            $handler = $this->container->get('ilioscore.vocabulary.handler');
            $vocabulary = $handler->post($this->getPostData($request));

            $authChecker = $this->get('security.authorization_checker');
            if (! $authChecker->isGranted('create', $vocabulary)) {
                throw $this->createAccessDeniedException('Unauthorized access!');
            }

            $manager = $this->container->get('ilioscore.vocabulary.manager');
            $manager->update($vocabulary, true, false);

            $answer['vocabularies'] = [$vocabulary];

            $view = $this->view($answer, Codes::HTTP_CREATED);

            return $this->handleView($view);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update a Vocabulary.
     *
     * @ApiDoc(
     *   section = "Vocabulary",
     *   description = "Update a Vocabulary entity.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\VocabularyType",
     *   output="Ilios\CoreBundle\Entity\Vocabulary",
     *   statusCodes={
     *     200 = "Updated Vocabulary.",
     *     201 = "Created Vocabulary.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        try {
            $manager = $this->container->get('ilioscore.vocabulary.manager');
            $vocabulary = $manager->findOneBy(['id'=> $id]);
            if ($vocabulary) {
                $code = Codes::HTTP_OK;
            } else {
                $vocabulary = $manager->create();
                $code = Codes::HTTP_CREATED;
            }

            $handler = $this->container->get('ilioscore.vocabulary.handler');
            $vocabulary = $handler->put($vocabulary, $this->getPostData($request));

            $authChecker = $this->get('security.authorization_checker');
            if (! $authChecker->isGranted('edit', $vocabulary)) {
                throw $this->createAccessDeniedException('Unauthorized access!');
            }

            $manager->update($vocabulary, true, true);

            $answer['vocabulary'] = $vocabulary;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

        $view = $this->view($answer, $code);

        return $this->handleView($view);
    }

    /**
     * Delete a Vocabulary.
     *
     * @ApiDoc(
     *   section = "Vocabulary",
     *   description = "Delete a Vocabulary entity.",
     *   resource = true,
     *   requirements={
     *     {
     *         "name" = "id",
     *         "dataType" = "integer",
     *         "requirement" = "\d+",
     *         "description" = "Vocabulary identifier"
     *     }
     *   },
     *   statusCodes={
     *     204 = "No content. Successfully deleted Vocabulary.",
     *     400 = "Bad Request.",
     *     404 = "Not found."
     *   }
     * )
     *
     * @Rest\View(statusCode=204)
     *
     * @param $id
     * @internal VocabularyInterface $vocabulary
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $vocabulary = $this->getOr404($id);

        $authChecker = $this->get('security.authorization_checker');
        if (! $authChecker->isGranted('delete', $vocabulary)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        try {
            $manager = $this->container->get('ilioscore.vocabulary.manager');
            $manager->delete($vocabulary);

            return new Response('', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $exception) {
            throw new \RuntimeException("Deletion not allowed: " . $exception->getMessage());
        }
    }

    /**
     * Get an entity or throw a exception.
     *
     * @param $id
     * @return VocabularyInterface $vocabulary
     */
    protected function getOr404($id)
    {
        $manager = $this->container->get('ilioscore.vocabulary.manager');
        $vocabulary = $manager->findOneBy(['id' => $id]);
        if (!$vocabulary) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $vocabulary;
    }

    /**
     * Parse the request for the form data.
     *
     * @param Request $request
     * @return array
     */
    protected function getPostData(Request $request)
    {
        if ($request->request->has('vocabulary')) {
            return $request->request->get('vocabulary');
        }

        return $request->request->all();
    }
}
