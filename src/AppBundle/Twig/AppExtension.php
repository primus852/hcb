<?php

namespace AppBundle\Twig;

use AppBundle\Util\Constants;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('decrypt', array($this, 'decryptFilter')),
            new \Twig_SimpleFilter('encrypt', array($this, 'encryptFilter')),
        );
    }

    public function encryptFilter($string)
    {

        return $this->simpleCrypt($string);

    }

    public function decryptFilter($string)
    {

        return $this->simpleCrypt($string, false);

    }

    /**
     * @param $string
     * @param bool $action
     * @param string $s_key
     * @param string $s_iv
     * @return bool|string
     */
    private function simpleCrypt($string, $action = true, $s_key = Constants::CRYPT_KEY, $s_iv = Constants::CRYPT_IV)
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

    public function getName()
    {
        return 'app_extension';
    }

}