<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ifresco\ClientBundle\Security\User;

class AssociationController extends Controller
{
    public function getAutoCompleteContentDataAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $q = $request->get('q');
        $dataTypeParam = $request->get('dataTypeParam');
        if (empty($dataTypeParam)) {
            $dataTypeParam = "cm:content";
        }
        $response = array();

        $results = $session->query($spacesStore, 'TYPE:"'.$dataTypeParam.'" AND (@cm\:name:'.$q.' OR TEXT:'.$q.')');
        if ($results != null) {
            for ($i = 0; $i < count($results); $i++) {
                $node = $results[$i];
                $extension = preg_replace("/.*\.(.*)/is", "$1", $node->cm_name);
                if (!file_exists($this->get('kernel')->getRootDir() . "/web/images/filetypes/16x16/{$extension}.png")) {
                    $extension = "txt";
                }

//                $response .= "$extension/{$node->getId()}/{$node->cm_name}" . "\n";
                $response[] = array(
                    'extension' => $extension,
                    'nodeId' => $node->getId(),
                    'nodeCmName' => $node->cm_name
                );
            }
        }

        return new JsonResponse(array(
            'success' => true,
            'data' => $response
        ));
    }

    public function getAutoCompleteUserDataAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        
        $q = $request->get('q');
        $response = arrray();

        $results = $session->query($spacesStore, "@cm\:userName:*$q* OR @cm\:email:*$q* OR @cm\:lastName:*$q* OR @cm\:firstName:*$q*");
        if ($results != null) {
            for ($i = 0; $i < count($results); $i++) {
                $node = $results[$i];
                $userName = $node->cm_userName;
                $firstName = $node->cm_firstName;
                $lastName = $node->cm_lastName;
                $email = $node->cm_email;

//                $response .= "{$email}/{$node->getId()}/{$firstName}/{$lastName}/{$userName}" . "\n";
                $response[] = array(
                    'email' => $email,
                    'nodeId' => $node->getId(),
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'userName' => $userName
                );
            }
        }

        return new JsonResponse(array(
            'success' => true,
            'data' => $response
        ));
    }
}