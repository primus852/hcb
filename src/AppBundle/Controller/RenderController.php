<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RenderController extends Controller
{

    /**
     * @Route("/_render/_overview", name="renderOverview")
     * @param $request Request
     * @return Response
     */
    public function mainAction(Request $request)
    {

        /* Check Login Status */
        if (($checkLogin = $this->get('app.helper')->checkLogin($request->get('_route'))) !== true) {
            return $checkLogin;
        }

        return $this->render(
            ':default/pages:blank.html.twig',
            array(
            )
        );
    }

}