<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\BaseController;
use AppBundle\Form\BattleType;
use AppBundle\Form\Model\BattleModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BattleController
 * 
 * @Security("is_granted('ROLE_USER')")
 */
class BattleController extends BaseController
{
    /**
     * @Route("/api/battles")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $battleModel = new BattleModel();
        $form = $this->createForm(BattleType::class, $battleModel, ['user' => $this->getUser()]);

        $this->processForm($request, $form);

        if(!$form->isValid()){
            $this->throwApiProblemValidationException($form);
        }

        $battle = $this->getBattleManager()->battle($battleModel->getProgrammer(), $battleModel->getProject());

        $response = $this->createApiResponse($battle, 201);

        return $response;
    }
}