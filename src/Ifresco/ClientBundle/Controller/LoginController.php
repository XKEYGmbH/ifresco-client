<?php

namespace Ifresco\ClientBundle\Controller;

use DateTime;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTAuthentication;
use Ifresco\ClientBundle\Entity\AlfrescoAccount;
use Ifresco\ClientBundle\Form\Type\LoginType;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    public function indexAction(Request $request)
    {
        if ($request->get("module") == "NodeActions" && $request->get("action") == "ImagePreview") {
            //TODO: i guess it is an old part of code but need to be tested before final delete
        }

        if(
            !$this->container->hasParameter('alfresco_repository_url') ||
            !$this->container->hasParameter('database_name') ||
            $this->container->getParameter('database_name') == ''
        ) {
            //TODO : remake for install controller
            return $this->redirect($this->generateUrl('ifrescoClientInstallBundle_install'), 307);
        }

        /**
         * @var Form $form
         */
        $form = $this->createForm(new LoginType());

        $AcceptLanguages = $this->getRequest()->getLanguages();
        $LanguageDefault = "";
        if (count($AcceptLanguages) > 0) {
            $langKey = $AcceptLanguages[0];
            $langKey = preg_replace("/(.*?)_.*/","$1",$langKey);
            $LanguageDefault = $langKey;
        }

        if ($this->loginProcess($request, $form)) {
            return $this->redirect($this->generateUrl('ifresco_client_index'));
        }

        return $this->render('IfrescoClientBundle:Login:index.html.twig', array(
            'form' => $form->createView(),
            'Languages' => array(
                array("short" => "en", "long" => "English"),
                array("short" => "de", "long" => "Deutsch"),
                //array("short" => "ru", "long" => "Russian")
            ),
            'LanguageDefault' => $LanguageDefault,
            'ifrescoVersion' => $this->container->getParameter("ifresco.client.version")
        ));
    }

    /**
     * @param Request $request
     * @param Form $form
     * @return bool
     */
    public function loginProcess(Request $request, $form)
    {
        if ($request->isMethod("POST")) {
            $lang = $request->get('lang');
            $this->get('session')->set('_locale', $lang);

            $form->bind($request);

            if ($form->isValid()) {
                $repositoryUrl = $this->container->getParameter('alfresco_repository_url');
                $loginData = $request->get('login_form');

                $userName = $loginData["username"];
                $password = $loginData["password"];


                $alfrescoLogin = new RESTAuthentication($repositoryUrl);

                $ticket = $alfrescoLogin->login($userName, $password);
       
                if ($ticket != null && !empty($ticket)) {
                    $em  = $this->getDoctrine()->getManager();
                    $alfrescoAccount = $em->getRepository('IfrescoClientBundle:AlfrescoAccount')->findOneBy(array(
                        'user_token' => $ticket
                    ));

                    if (!$alfrescoAccount) {
                        $alfrescoAccount = new AlfrescoAccount();
                        $alfrescoAccount->setUserToken($ticket);
                        $alfrescoAccount->setCreatedAt(new DateTime());
                        $alfrescoAccount->setUpdatedAt(new DateTime());
                    }

                    $alfrescoAccount->setLastLogin(new DateTime());

                    $em->persist($alfrescoAccount);
                    $em->flush();

                    $token = new User($userName, $password, 'secured_area', array('ROLE_USER'));
                    $context = $this->get('security.context');

                    $token->setAttribute('AlfrescoTicket', $ticket);
                    $token->setAttribute('AlfrescoUsername', $userName);
                    $context->setToken($token);
                    return true;
                }
            }
        }

        return false;
    }
}
