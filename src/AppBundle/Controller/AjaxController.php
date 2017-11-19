<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ratecode;
use AppBundle\Entity\SetupBar;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AjaxController extends Controller
{


    /**
     * @Route("/_ajax/_updateModifier", name="ajaxUpdateModifier")
     * @param $request Request
     * @return Response
     */
    public function updateModifierAction(Request $request)
    {

        $val = $request->get('val');
        $modField = $request->get('field');

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $setup SetupBar */
        $setup = $em->getRepository('AppBundle:SetupBar')->find(1);

        /* On first run, create new SetupConfig */
        if ($setup === null) {
            $setup = new SetupBar();
        }

        /* Get Property Accessor */
        $accessor = PropertyAccess::createPropertyAccessor();

        /* Check if writable */
        if ($accessor->isWritable($setup, $modField)) {
            $accessor->setValue($setup, $modField, $val);
        } else {

            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Accessor not writeable',
            ));
        }


        $em->persist($setup);


        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(
                array(
                    'result' => 'error',
                    'message' => 'Error saving value',
                )
            );
        }

        return new JsonResponse(
            array(
                'result' => 'success',
                'message' => 'Modifier updated',
            )
        );

    }

    /**
     * @Route("/_ajax/_editFieldModal", name="ajaxEditFieldModal")
     * @param $request Request
     * @return Response
     */
    public function editFieldModalAction(Request $request)
    {

        /* Check Login Status */
        if (($checkLogin = $this->get('app.helper')->checkLogin($request->get('_route'))) !== true) {
            return $checkLogin;
        }

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        /* Data */
        $data = $request->get('data');

        /* Repository */
        $parent = 'AppBundle\Entity\\' . $this->get('app.helper')->simpleCrypt($request->get('parent'), false);
        $this->get('app.helper')->getRepo('AppBundle', $parent);
        $repo = new $parent();
        $accessor = PropertyAccess::createPropertyAccessor();

        $extra = null;
        $heading = null;
        $subHeading = null;
        $revise = null;
        $cat = null;
        foreach ($data as $d) {

            $field = $this->get('app.helper')->simpleCrypt($d['name'], false);
            $val = $valResult = $d['val'];
            $type = $d['type'];
            $i = intval($d['index']);
            $cellClass = null;

            /* Replace Checkbox Values */
            if ($type === 'checkbox') {
                if ($val === 'true') {
                    $val = true;
                    $valResult = '<i class="fa fa-check"></i>';
                } else {
                    $val = false;
                    $valResult = '<i class="fa fa-remove"></i>';
                }
            }

            /* Selectbox true/false */
            if($type === 'select'){
                if($val === 'true'){
                    $val = true;
                }
                if($val === 'false'){
                    $val = false;
                }
            }

            $e = null;
            if (array_key_exists('extra', $d)) {
                $e = $d['extra'];
            }

            if ($field === 'Base') {
                if ($val === 'null') {
                    $val = null;
                    $valResult = 'None';
                }else{
                    $sub = $em->getRepository('AppBundle:' . $field)->find($val);
                    if ($sub === null) {
                        return new JsonResponse(array(
                            'result' => 'error',
                            'message' => 'Could not find AppBundle:' . $field . ', Val: ' . $val,
                        ));
                    }

                    $val = $sub;

                    switch (true) {
                        case $sub instanceof Ratecode:
                            $valResult = $sub->getCode();
                            break;
                    }

                }
            }else{

                /* Special display Values */
                switch ($field){
                    case 'Modifier':
                        $valResult = '-';
                        if($val === 'add'){
                            $valResult = '+';
                        }
                        if($val === 'times'){
                            $valResult = 'x';
                        }
                        break;
                    case 'IsFlat':
                        $valResult = '%';
                        if($val === false){
                            $valResult = '€';
                        }
                        break;
                }
            }



            /* Check if writable */
            if ($accessor->isWritable($repo, $field)) {
                $accessor->setValue($repo, $field, $val);
            } else {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'Accessor not writeable: ' . $field,
                ));
            }

            $em->persist($repo);

            /* Add to output array */
            $extra[$i] = array(
                'name' => $field,
                'value' => $valResult,
                'type' => $type,
                'extra' => $e,
                'cellClass' => $cellClass,
            );
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(
                array(
                    'result' => 'error',
                    'message' => 'Error saving data, please try again'.$e->getMessage(),
                )
            );
        }

        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Data saved',
            'extra' => array(
                'fields' => $extra,
                'heading' => $heading,
                'subHeading' => $subHeading,
                'span' => $request->get('parent'),
                'id' => $repo->getId(),
            )
        ));


    }

    /**
     * @Route("/_ajax/_editField/{table}", name="ajaxEditField", defaults={"table"="0"})
     * @param $request Request
     * @return Response
     */
    public function editFieldAction(Request $request, $table)
    {

        /* Check Login Status */
        if (($checkLogin = $this->get('app.helper')->checkLogin($request->get('_route'))) !== true) {
            return $checkLogin;
        }

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        $repoString = $this->get('app.helper')->getRepo('AppBundle', $table);
        if ($repoString === FALSE) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Key not found: '.$table
            ));
        }


        $name = $request->get('name');
        $value = $request->get('value');

        $repo = $em->getRepository($repoString)->find($request->get('pk'));

        /* Get Property Accessor */
        $accessor = PropertyAccess::createPropertyAccessor();

        $security = $this->get('security.authorization_checker');

        if ($repoString === 'AppBundle:Bar') {
            if (!$security->isGranted('ROLE_SUPER_ADMIN')) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'No Permission',
                    'oldValue' => $value,
                ));
            }
        }


        /* Check if it is a price and replace ',' with '.' */
        if ($repoString == "AppBundle:SetupBar" &&
            (
                $name == "valSingle" ||
                $name == "valDouble" ||
                $name == "valTriple" ||
                $name == "valQuadruple" ||
                $name == "valExtra"
            ) && !is_array($value)) {

            $value = floatval(str_replace(',', '.', $value));
        }


        /* Check if writable */
        if ($accessor->isWritable($repo, $name)) {
            $accessor->setValue($repo, $name, $value);
        } else {

            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Accessor not writeable',
                'oldValue' => $value,
            ));
        }

        if (gettype($value) === 'double') {
            $value = number_format($value, 2);
        }

        $em->persist($repo);


        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(
                array(
                    'result' => 'error',
                    'message' => 'Error saving value',
                    'oldValue' => $value,
                )
            );
        }

        return new JsonResponse(
            array(
                'result' => 'success',
                'message' => 'Value updated',
                'oldValue' => 'none',
                'newPK' => $repo->getId(),
                'newValue' => $value,
            )
        );
    }


}