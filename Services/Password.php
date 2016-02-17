<?php
namespace WH\UserBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class Password
{

    public function genere ($size) {


        $src = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '8', '9');

        $mot_de_passe = '';

        for($i = 0; $i < $size; $i++) {

            $mot_de_passe .= $src[rand(0, 30)];

        }

        return $mot_de_passe;

    }



}
