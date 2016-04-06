<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TokenController extends BaseController
{

    /**
     * @Route("/api/tokens")
     * @Method("POST")
     */
    public function newTokenAction(Request $request)
    {

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['username' => $request->getUser()]);

        if (!$user) {
            $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')->isPasswordValid($user, $request->getPassword());

        if (!$isValid) {
            Throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')->encode(
            [
                'username' => $user->getUsername(),
            ]
        );

        return new JsonResponse(
            [
                'token' => $token
            ]
        );
    }
}