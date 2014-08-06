<?php
namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTTags;
use Ifresco\ClientBundle\Entity\AlfrescoAccount;
use Ifresco\ClientBundle\Entity\Setting;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTAuthentication;

class RESTServiceController extends Controller
{
	public function isLoggedIn($user,$repositoryUrl,$doctrine) {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="ifresco REST API"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Failed!';
            exit;
        } else {
            
            $userName = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];

            $alfrescoLogin = new RESTAuthentication($repositoryUrl);

            $ticket = $alfrescoLogin->login($userName,$password);
            if ($ticket != null && !empty($ticket)) {
                $em = $doctrine->getEntityManager();
                $alfrescoAccount = $doctrine
                    ->getRepository('IfrescoClientBundle:AlfrescoAccount')
                    ->findOneBy(array('user_token' => $ticket));
                if (!$alfrescoAccount) {
                    $alfrescoAccount = new AlfrescoAccount();
                    $alfrescoAccount->setUserToken( $ticket );
                }

                $alfrescoAccount->setLastLogin( new \DateTime() );
                if($alfrescoAccount->getCreatedAt() == null)
                    $alfrescoAccount->setCreatedAt( new \DateTime() );
                $alfrescoAccount->setUpdatedAt( new \DateTime() );

                $em->persist($alfrescoAccount);
                $em->flush();

                if(!$user->isAuthenticated())
                    $user->setAuthenticated( true );
                $user->setAttribute( 'AlfrescoTicket', $ticket );
                $user->setAttribute( 'AlfrescoUsername', $userName );

                return true;

            }
            else {
                header('HTTP/1.0 401 Unauthorized');
                echo "Failed!";
                exit;
            }
            exit;
        }
        return false;
    }

    public function increaseCounterAction(Request $request) {
        $Response = array();

        $user = $this->get('security.context')->getToken();
        $repositoryUrl = $this->container->getParameter('alfresco_repository_url');
        if ($this->isLoggedIn($user,$repositoryUrl,$this->getDoctrine())) {
            try {
                $CounterName = "MainCounter";

                $em = $this->getDoctrine()->getEntityManager();
                $query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:Setting', 's')
                    ->where('s.key_string = :keystring')
                    ->setParameter('keystring', $CounterName);
                $CounterSetting = $query->getQuery()->getOneOrNullResult();


                if ($CounterSetting == null) {
                    $CounterSetting = new Setting();
                    $CounterSetting->setKeyString($CounterName);

                    $Counter = 1;
                }
                else {
                    $Counter = (int)$CounterSetting->getValueString();
                    $Counter++;
                }
                $CounterSetting->setValueString($Counter);
                $em->persist($CounterSetting);
                $em->flush();

                $Response["success"] = true;
                $Response["counter"] = $Counter;

            }
            catch (\Exception $e) {
            	echo $e->getMessage();
                $Response["success"] = false;
                $Response["counter"] = null;
            }
        }

        $response = new JsonResponse($Response);
        //$return = json_encode($Response);
        //$resp->setContent($return);
        return $response;
    }


    private function forward400() {
        $this->forward('IfrescoClientBundle:RESTService:error', array('request' => $this->getRequest()), array('Method' => $this->getRequest()->getMethod(), 'FromUrl' => $this->getRequest()->getUri(), 'ErrorCode' => 400 ));
    }

    private function forward405() {
        $this->forward('IfrescoClientBundle:RESTService:error', array('request' => $this->getRequest()), array('Method' => $this->getRequest()->getMethod(), 'FromUrl' => $this->getRequest()->getUri(), 'ErrorCode' => 405 ));
    }

    public function errorAction(Request $request) {
        $view_vars['code'] = $request->get("ErrorCode");
        $view_vars['uri'] = $request->get("FromUrl");
        $view_vars['method'] = $request->get("Method");
        switch ($request->get("ErrorCode")) {
            case 400:
                $view_vars['message'] = "Bad Request (Wrong Parameter)";
                break;
            case 405:
                $view_vars['message'] = "Method not allowed!";
                break;
            default:
                $view_vars['message'] = "Error!!";
                break;
        }

        return $this->render('IfrescoClientBundle:RESTService:error.html.php', $view_vars);
    }
}
