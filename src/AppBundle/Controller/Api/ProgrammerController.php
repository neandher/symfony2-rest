<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Programmer;
use AppBundle\Form\ProgrammerType;
use AppBundle\Form\UpdateProgrammerType;
use AppBundle\Pagination\PaginatedCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProgrammerController
 *
 * @Security("is_granted('ROLE_USER')")
 */
class ProgrammerController extends BaseController
{

    /**
     * @Route("/api/programmers")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        //$this->denyAccessUnlessGranted('ROLE_USER');

        $programmer = new Programmer();
        $form = $this->createForm(new ProgrammerType(), $programmer);

        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiProblemValidationException($form);
        }

        //$programmer->setUser($this->findUserByUsername('weaverryan'));

        $programmer->setUser($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $response = $this->createApiResponse($programmer, 201);

        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            [
                'nickname' => $programmer->getNickname()
            ]
        );

        $response->headers->set('Location', $programmerUrl);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}", name="api_programmers_show")
     * @Method("GET")
     * @param $nickname
     * @return Response
     */
    public function showAction($nickname)
    {
        $programmer = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findOneByNickname($nickname);

        if (!$programmer) {
            throw $this->createNotFoundException(
                sprintf(
                    'No programmer with nickname "%s"',
                    $nickname
                )
            );
        }

        $response = $this->createApiResponse($programmer, 200);

        return $response;
    }

    /**
     * @Route("/api/programmers", name="api_programmers_collection")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $qb = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findAllQueryBuilder($filter);

        $paginatedCollection = $this->get('pagination_factory')
            ->createCollection($qb, $request, 'api_programmers_collection');

        $response = $this->createApiResponse($paginatedCollection);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}")
     * @Method({"PUT", "PATCH"})
     * @param $nickname
     * @param Request $request
     * @return Response
     */
    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findOneByNickname($nickname);

        if (!$programmer) {
            throw $this->createNotFoundException(
                sprintf(
                    'No programmer found with nickname "%s"',
                    $nickname
                )
            );
        }

        $form = $this->createForm(new UpdateProgrammerType(), $programmer);

        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $response = $this->createApiResponse($programmer, 200);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}")
     * @Method("DELETE")
     * @param $nickname
     * @param Request $request
     * @return Response
     */
    public function deleteAction($nickname, Request $request)
    {
        $programmer = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findOneByNickname($nickname);

        if ($programmer) {
            // debated point: should we 404 on an unknown nickname?
            // or should we just return a nice 204 in all cases?
            // we're doing the latter
            $em = $this->getDoctrine()->getManager();
            $em->remove($programmer);
            $em->flush();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/programmers/{nickname}/battles", name="api_programmers_battles_list")
     * @Method("GET")
     * @param Programmer $programmer
     * @param Request $request
     * @return Response
     */
    public function battlesListAction(Programmer $programmer, Request $request)
    {
        $battlesQb = $this->getDoctrine()->getRepository('AppBundle:Battle')->createQueryBuilderForProgrammer(
            $programmer
        );

        //$collection = new PaginatedCollection($battles, count($battles));
        
        $collection = $this->get('pagination_factory')->createCollection(
            $battlesQb,
            $request,
            'api_programmers_battles_list',
            ['nickname' => $programmer->getNickname()]
        );

        return $this->createApiResponse($collection);
    }
}