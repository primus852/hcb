<?php

namespace AppBundle\Util;


use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManager;

class Helper
{


    private $authorizationChecker;
    private $router;
    private $em;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, Router $router, EntityManager $em)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->em = $em;

    }


    /**
     * @param $returnTo
     * @return bool|RedirectResponse
     */
    public function checkLogin($returnTo)
    {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') && !$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse(
                $this->router->generate('app_login', array(
                    'page' => $returnTo
                )), Response::HTTP_TEMPORARY_REDIRECT);
        }

        return true;
    }

    /**
     * @param $bundle
     * @param $search
     * @return bool|string
     * @throws \Exception
     */
    public function getRepo($bundle, $search)
    {

        /* Decrypted Value */
        $val = $this->simpleCrypt($search, false);
        if (!$val) {
            return false;
        }

        try {
            $this->em->getRepository($bundle . ':' . $val);
        } catch (\Exception $e) {
            if ($e instanceof MappingException || $e instanceof ORMException) {
                return false;
            }
            throw $e;
        }

        return $bundle . ':' . $val;

    }

    /**
     * @param $string
     * @param bool $action
     * @param string $s_key
     * @param string $s_iv
     * @return bool|string
     */
    public function simpleCrypt($string, $action = true, $s_key = Constants::CRYPT_KEY, $s_iv = Constants::CRYPT_IV)
    {

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $s_key);
        $iv = substr(hash('sha256', $s_iv), 0, 16);

        if ($action === true) {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else if ($action === false) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    /**
     * @param $field
     * @return bool|string
     */
    public function getTemplateByClass($field)
    {
        return ':default/modal:modal'.$this->simpleCrypt($field, false).'.html.twig';

    }


}