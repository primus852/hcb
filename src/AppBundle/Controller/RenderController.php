<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /**
     * @Route("/_render/_setup/_rates", name="setupRatesOverview")
     * @param $request Request
     * @return Response
     */
    public function setupRates(Request $request)
    {

        /* Check Login Status */
        if (($checkLogin = $this->get('app.helper')->checkLogin($request->get('_route'))) !== true) {
            return $checkLogin;
        }

        /* Get all Baserates */
        $rates = $this->getDoctrine()->getRepository('AppBundle:Ratecode')->findAll();

        return $this->render(
            ':default/pages:setupRates.html.twig',
            array(
                'rates' => $rates,
            )
        );
    }

    /**
     * @Route("/_render/_setup/_bar", name="setupBar")
     * @param $request Request
     * @return Response
     */
    public function setupBar(Request $request)
    {

        /* Check Login Status */
        if (($checkLogin = $this->get('app.helper')->checkLogin($request->get('_route'))) !== true) {
            return $checkLogin;
        }

        $base = $this->getDoctrine()->getRepository('AppBundle:SetupBar')->find(1);
        if($base === null){
            throw new NotFoundHttpException();
        }

        return $this->render(
            ':default/pages:setupBar.html.twig',
            array(
                'base' => $base,
            )
        );
    }

    /**
     * @Route("/admin/_render/_modalAddField/{field}", name="renderModalAddField", defaults={"field"="0"})
     * @param $field string
     * @return Response
     * @throws Exception
     */
    public function renderModalAddFieldAction($field)
    {

        $template = $this->get('app.helper')->getTemplateByClass($field);

        if ($template === false) {
            throw new NotFoundHttpException('Class not found');
        }

        $rates = $this->getDoctrine()->getRepository('AppBundle:Ratecode')->findAll();


        return $this->render($template, array(
            'entity' => null,
            'rates' => $rates
        ));

    }

}