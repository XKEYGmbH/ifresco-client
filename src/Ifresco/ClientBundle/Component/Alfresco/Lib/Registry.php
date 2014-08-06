<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use \Doctrine\ORM\EntityManager;

/**
 * @package    AlfrescoClient
 * @author Dominik Danninger 
 *
 * ifresco Client
 * 
 * Copyright (c) 2013 X.KEY GmbH
 * 
 * This file is part of "ifresco Client".
 * 
 * "ifresco Client" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco Client" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco Client".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */

 class Registry {
     public static function getSetting($Setting,$default=null) {

         /**
          * @var EntityManager $em
          */
         $em = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();

         $query = $em->createQuery(
             'SELECT s FROM IfrescoClientBundle:Setting s WHERE s.key_string = :key_string'
         )->setParameter('key_string', $Setting);

         $Setting = $query->getOneOrNullResult();

        if ($Setting != null)
            return $Setting->getValuestring();

        return $default;
     }
 }
?>