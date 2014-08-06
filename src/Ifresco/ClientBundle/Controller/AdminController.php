<?php

namespace Ifresco\ClientBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Ifresco\ClientBundle\Component\Alfresco\Jobs;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTAspects;
use Ifresco\ClientBundle\Component\Alfresco\SugarCRM\SugarWrapper;
use Ifresco\ClientBundle\Entity\AllowedAspect;
use Ifresco\ClientBundle\Entity\AllowedType;
use Ifresco\ClientBundle\Entity\ContentModelTemplate;
use Ifresco\ClientBundle\Entity\CurrentJob;
use Ifresco\ClientBundle\Entity\DataSource;
use Ifresco\ClientBundle\Entity\Lookup;
use Ifresco\ClientBundle\Entity\NamespaceMapping;
use Ifresco\ClientBundle\Entity\SavedSearch;
use Ifresco\ClientBundle\Entity\SearchColumnSet;
use Ifresco\ClientBundle\Entity\SearchTemplate;
use Ifresco\ClientBundle\Entity\Setting;
use Ifresco\ClientBundle\Security\User;
use Imagine\Filter\Transformation;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Renderer;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDictionary;
use Ifresco\ClientBundle\Component\Alfresco\Lib\CategoryCache;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTCategories;
use Symfony\Component\HttpFoundation\Response;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTocr;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTTags;

class AdminController extends Controller
{
    /**
     * @var RESTCategories $_restCategories
     */
    private $_restCategories;

    public function templatesListAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()){
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $templates = $em->createQueryBuilder()->select('t')
            ->from('IfrescoClientBundle:ContentModelTemplate', 't')
            ->getQuery()
            ->getResult()
        ;

        $templateArray = array("templates" => array());

        /**
         * @var ContentModelTemplate $template
         */
        foreach ($templates as $template) {
            $jsonData = json_decode($template->getJsonData());
            $tabCount = 0;

            if (isset($jsonData->Tabs) && count($jsonData->Tabs) > 0 && count($jsonData->Tabs->tabs) > 0) {
                $tabCount = count($jsonData->Tabs->tabs);
            }

            $templateArray["templates"][] = array(
                "id" => $template->getId(),
                "class" => $template->getClass(),
                "multiColumns" => $template->getIsMulticolumn(),
                "aspectsView" => $template->getAspectView(),
                "tabs" => $tabCount
            );
        }

        $response = new JsonResponse($templateArray);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function deleteTemplatesAction(Request $request)
    {
        $ids = $request->get('ids');
        $data = array("success" => false);
        if (!empty($ids)) {
            $in = "";
            $params = array();

            foreach ($ids as $key => $id) {
                $in = strlen($in) > 0 ? $in . ", ?$key" : "?$key";
                $params[$key] = $id;
            }

            $em = $this->getDoctrine()->getManager();
            $em->createQueryBuilder()->delete()
                ->from('IfrescoClientBundle:ContentModelTemplate', 'c')
                ->where("c.id IN($in)")
                ->setParameters($params)->getQuery()->execute();

            $data["success"] = true;
        }

        return new JsonResponse($data);
    }

    public function getTemplateContentTypesAction(Request $request) {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $asmSelect = $request->get('asmselect');
        $aspect = $request->get('aspect');
        $values = $request->get('values');

        if (empty($asmSelect)) {
            $asmSelect = false;
        }

        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $valuesArr = array();
        if (!empty($values)) {
            $split = explode(",",$values);
            if (count($split) > 0) {
                for ($i = 0; $i < count($split); $i++) {
                    $valuesArr[] = $split[$i];
                }
            } else {
                $valuesArr[] = $values;
            }
        }

        $RestDictionary = new RESTDictionary($repository, $spacesStore, $session);

        $SubClasses = $aspect == 'true' ? $RestDictionary->GetClasses() : $RestDictionary->GetSubClassDefinitions("cm_cmobject");
        $types = $asmSelect == false ? array("types" => array()) : array();

        if ($SubClasses != null) {
            foreach ($SubClasses as $SubClass) {
                $name = $SubClass->name;
                $title = $SubClass->title;
                $description = $SubClass->description;

                $title = empty($title) ? $name : $name . " - " . $title;

                if ($asmSelect == false) {
                    $types["types"][] = array(
                        "name" => $name,
                        "title" => $title,
                        "description" => $description
                    );
                } else {
                    $state = in_array($name, $valuesArr) ? "selected" : "";
                    $types[] = array(
                        'attributes' => array(
                            'value' => $name,
                            'id' => str_replace(":", "_", $name)
                        ),
                        'state' => $state,
                        'text' => $title
                    );
                }
            }
        }

        if(isset($types['types'])) {
            $types['types'] = $this->arraySort($types['types'], 'name');
            $typesArr = $this->arraySort($types['types'], "name");
            $types['types'] = $typesArr;
            if (count($typesArr) > 0) {
                $types['types'] = array();
                foreach ($typesArr as $v) {
                    $types['types'][] = $v;
                }
            }
        } else {
            $typesArr = $this->arraySort($types, "text");
            $types = $typesArr;
            if (count($typesArr) > 0) {
                $types = array();
                foreach ($typesArr as $v) {
                    $types[] = $v;
                }
            }
        }

        return new JsonResponse($types);
    }

    public function getTemplateDesignerAction(Request $request)
    {
        $params = array();
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $class = $request->get('class');
        $templateId = $request->get('id');

        $params['FoundTemplate'] = false;

        $params['DeletedProps'] = array();
        $params['DeletedAssocs'] = array();

        if (!empty($templateId)) {
            $em = $this->getDoctrine()->getManager();
            /**
             * @var ContentModelTemplate $template
             */
            $template = $em->getRepository('IfrescoClientBundle:ContentModelTemplate')->find($templateId);

            if ($template != null) {
                $jsonData = $template->getJsonData();

                if (!empty($jsonData)) {
                    $params['FoundTemplate'] = true;
                    $usedFields = array();

                    $jsonData = json_decode($jsonData);
                    $params['Column1'] = isset($jsonData->Column1) ? $jsonData->Column1 : array();
                    $params['Column2'] = isset($jsonData->Column2) ? $jsonData->Column2 : array();
                    $params['Tabs'] = isset($jsonData->Tabs) ? $jsonData->Tabs : '';
                    $params['Multicolumn'] = $template->getIsMulticolumn();
                    $params['Aspectsview'] = $template->getAspectView();

                    $params['Class'] = $template->getClass();
                    $params['Id'] = $template->getId();


                    $RestDictionary = new RESTDictionary($repository, $spacesStore, $session);
                    $ClassProperties = $RestDictionary->GetClassProperties($params['Class']);
                    $ClassAssociation = $RestDictionary->GetClassAssociations($params['Class']);


                    foreach ($params['Column1'] as $Prop) {
                        $usedFields[] = $Prop->name;
                    }
                    foreach ($params['Column2'] as $Prop) {
                        $usedFields[] = $Prop->name;
                    }

                    if (isset($params['Tabs']->tabs) && count($params['Tabs']->tabs) > 0) {
                        foreach ($params['Tabs']->tabs as $Tab) {
                            foreach ($Tab->items as $Prop) {
                                $usedFields[] = $Prop->name;
                            }
                        }
                    }

                    if ($ClassProperties != null) {
                        $DeletedProps = array();
                        $ignoreArray = array("cm:content");
                        foreach ($ClassProperties as $Prop) {
                            if (!in_array($Prop->name, $ignoreArray) && !in_array($Prop->name, $usedFields)) {
                                $DeletedProps[] = $Prop;
                            }
                        }

                        $params['DeletedProps'] = $DeletedProps;
                    }

                    if ($ClassAssociation != null) {
                        $DeletedAssocs = array();
                        foreach ($ClassAssociation as $Assoc) {
                            if (!in_array($Assoc->name, $usedFields))
                                $DeletedAssocs[] = $Assoc;
                        }

                        $params['DeletedAssocs'] = $DeletedAssocs;
                    }
                }
            }
        }

        $params['associations'] = array();

        if ($params['FoundTemplate'] == false) {
            $RestDictionary = new RESTDictionary($repository, $spacesStore, $session);
            $class = str_replace(":", "_", $class);
            $ClassProperties = $RestDictionary->GetClassProperties($class);
            $ClassAssociation = $RestDictionary->GetClassAssociations($class);

            if ($ClassProperties != null) {
                $ClassPropertiesTemp = array();
                $ignoreArray = array("cm:content");
                foreach ($ClassProperties as $Prop) {
                    if (!in_array($Prop->name,$ignoreArray)) {
                        $ClassPropertiesTemp[] = $Prop;
                    }
                }

                $params['properties'] = $ClassPropertiesTemp;
            }

            if ($ClassAssociation != null) {
                $params['associations'] = $ClassAssociation;
            }

            $params['Class'] = $class;
            $params['Multicolumn'] = 1;
            $params['Aspectsview'] = "";
        }

        return new JsonResponse($params);
    }

    public function getTemplatePropertiesAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $isAdmin = $user->isAdmin();
        if ($isAdmin == false) {
            $this->forward('default', 'module');
        }

        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $RestDictionary = new RESTDictionary($repository, $spacesStore, $session);
        $props = $RestDictionary->GetAllProperties();

        $allowed = $this->getAllowedPrefs();
        $props = $this->filterProps($props, $allowed);
        $Classes = $RestDictionary->GetClasses();

        $classArray = array();
        if (count($Classes) > 0) {
            foreach ($Classes as $Class) {
                //bug on default script so i need to get the class again
                // autoocr bug 
                if ($Class->name == "ifresco_autoocr:ocr")
                	continue;
                $className = str_replace(":", "_", $Class->name);
                $ClassValues = $RestDictionary->GetClassProperties($className);
                if (count($ClassValues) > 0) {
                    foreach ($ClassValues as $property) {
                        $classArray[$property->name] = $Class->name;
                    }
                }
            }
        }

        $types = array();
        $index = 0;
        if ($props != null) {
            foreach ($props as $property) {
                $name = $property->name;
                $title = isset($property->title) ? $property->title : '';
                $dataType = $property->dataType;
                $class = "";
                if (array_key_exists($name,$classArray)){
                    $class = $classArray[$name];
                }
                if (empty($title) || $title == "null") {
                    $showTitle = $name;
                } else{
                    $showTitle = $name . " " . $title;
                }
                $value = $name . "/" . $class . "/" . $title . "/" . $dataType;

                $state = "";

                $types[$index] = array(
                    'attributes' => array(
                        'value' => $value,
                        'id' => str_replace(":", "_", $name)
                    ),
                    'state' => $state,
                    'text' => $showTitle
                );
                $index++;
            }
        }

        $typesArr = $this->arraySort($types, "text");
        $types = $typesArr;
        if (count($typesArr) > 0) {
            $types = array();
            foreach ($typesArr as $v) {
                $types[] = $v;
            }
        }

        $response = new JsonResponse(array('properties' => $types, 'success' => true));

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function addTemplatePropertiesAction(Request $request)
    {
        $returnArr = array("success" => false);
        $data = $request->get('data');

        if (!empty($data)) {
            $data = json_decode($data);

            $params = array(
                'edit'    => $data->edit,
                'class'    => $data->class,
                'multiColumns'    => isset($data->multiColumns) ? $data->multiColumns: '',
                'aspectsView'    => $data->aspectsView,
                'col1'    => isset($data->col1) ? $data->col1 : '',
                'col2'    => isset($data->col2) ? $data->col2 : '',
                'tabs'    => isset($data->tabs) ? $data->tabs : ''
            );
        }
        else {
            $params = array(
                'edit'    => $request->get('edit'),
                'class'    => $request->get('class'),
                'multiColumns'    => $request->get('multiColumns'),
                'aspectsView'    => $request->get('aspectsView'),
                'col1'    => $request->get('col1'),
                'col2'    => $request->get('col2'),
                'tabs'    => $request->get('tabs'),
            );
        }

        try {

            $requiredArr = isset($data->requiredVals) && is_array($data->requiredVals)?$data->requiredVals:array();
            $readonlyArr = isset($data->readonlyVals) && is_array($data->readonlyVals)?$data->readonlyVals:array();

            $newCol1 = array();
            if (count($params["col1"]) > 0) {
                foreach ($params["col1"] as $entity) {
                    $split = explode("/", $entity);
                    if (count($split) > 0) {
                        if(count($split) != 5) {
                            $name = $split[0];
                            $label = $split[1];
                            $dataType = $split[2];
                            $type = $split[3];
                        } else {
                            $name = $split[0];
                            $label = $split[2];
                            $dataType = $split[3];
                            $type = $split[4];
                        }

                        if (!empty($name)){
                            $newCol1[] = array(
                                "name" => $name,
                                "dataType" => $dataType,
                                "title" => $label,
                                "type" => $type,
                                'required' => in_array($name, $requiredArr),
                                'readonly' => in_array($name, $readonlyArr)
                            );
                        }
                    }
                }
            }

            $newCol2 = array();
            if (count($params["col2"]) > 0) {
                foreach ($params["col2"] as $entity) {
                    $split = explode("/", $entity);
                    if (count($split) > 0) {
                        if(count($split) != 5) {
                            $name = $split[0];
                            $label = $split[1];
                            $dataType = $split[2];
                            $type = $split[3];
                        } else {
                            $name = $split[0];
                            $label = $split[2];
                            $dataType = $split[3];
                            $type = $split[4];
                        }

                        if (!empty($name)) {
                            $newCol2[] = array(
                                "name" => $name,
                                "dataType" => $dataType,
                                "title" => $label,
                                "type" => $type,
                                'required' => in_array($name, $requiredArr),
                                'readonly'=>in_array($name, $readonlyArr)
                            );
                        }
                    }
                }
            }

            $newTabs = array();

            if (isset($params["tabs"]) && count($params["tabs"]) > 0) {
                $newTabs = $params["tabs"];
                foreach ($params["tabs"] as $TabKey => $Tab) {
                    if (count($Tab->items) > 0) {
                        foreach ($Tab->items as $key => $entity) {
                            $split = explode("/", $entity);
                            if (count($split) > 0) {
                                if(count($split) != 5) {
                                    $name = $split[0];
                                    $label = $split[1];
                                    $dataType = $split[2];
                                    $type = $split[3];
                                } else {
                                    $name = $split[0];
                                    $label = $split[2];
                                    $dataType = $split[3];
                                    $type = $split[4];
                                }

                                if (!empty($name)) {
                                    $newTabs[$TabKey]->items[$key] = array(
                                        "name" => $name,
                                        "dataType" => $dataType,
                                        "title" => $label,
                                        "type" => $type,
                                        'required' => in_array($name, $requiredArr),
                                        'readonly' => in_array($name, $readonlyArr)
                                    );
                                }
                            }
                        }
                    }
                }
            }

            $Columns = array(
                "Column1" => $newCol1,
                "Column2" => $newCol2,
                "Tabs" => array('tabs' => $newTabs)
            );

            $em = $this->getDoctrine()->getManager();
            if ($params["edit"] == null || empty($params["edit"]) || $params["edit"] == "null") {
                $template = new ContentModelTemplate();
            } else {
                $template = $em->getRepository('IfrescoClientBundle:ContentModelTemplate')->find($params["edit"]);
                if (!$template) {
                    throw new \Exception('Such template not found');
                }
            }

            $template->setClass($params['class']);
            $template->setIsMulticolumn($params['multiColumns']);
            $template->setAspectView($params['aspectsView']);
            $template->setJsonData(json_encode($Columns));
            $em->persist($template);
            $em->flush();

            $returnArr["editId"] = $template->getId();
            $returnArr["success"] = true;
        }
        catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function getDataSourcesAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $dataSources = $em->getRepository('IfrescoClientBundle:DataSource')->findAll();

        $dataSourcesArray = array("datasources" => array());

        /**
         * @var DataSource $source
         */
        foreach ($dataSources as $source) {
            $dataSourcesArray["datasources"][] = array(
                "data_source_id" => $source->getId(),
                "name" => $source->getName(),
                "type" => $source->getType(),
                "host" => $source->getHost(),
                "username" => $source->getUsername(),
                "database_name" => $source->getDatabaseName(),
                "password" => $source->getPassword(),
                "port" => $source->getPort()
            );
        }

        $response = new JsonResponse($dataSourcesArray);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function deleteDataSourceAction(Request $request)
    {
        $id = $request->get('id');
        $data = array("success" => false);
        if (!empty($id)) {
            $em = $this->getDoctrine()->getManager();
            $em->createQueryBuilder()->delete()
                ->from('IfrescoClientBundle:DataSource', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id)
                ->getQuery()->execute()
            ;

            $data["success"] = true;
        }

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function saveDataSourceAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $dataSource = $em->getRepository('IfrescoClientBundle:DataSource')->find($request->get('data_source_id', 0));

        if(!$dataSource) {
            $dataSource = new DataSource();
        }

        $dataSource->setName($request->get('name'));
        $dataSource->setType($request->get('type'));
        $dataSource->setDatabaseName($request->get('database_name', ''));
        $dataSource->setUsername($request->get('username', ''));
        $dataSource->setPassword($request->get('password', ''));
        $dataSource->setHost($request->get('host', ''));
        $dataSource->setPort($request->get('port', ''));

        $em->persist($dataSource);
        $em->flush();

        $editId = false;
        $result = true;
        $msg = '';

        if(! ($dataSource->getId() > 0) ) {
            $result = false;
            $msg = $this->get('translator')->trans("An error has occured" );
        } else {
            $editId = $dataSource->getId();
        }

        $response = new JsonResponse(array(
            'success' => $result,
            'msg' => $msg,
            'editId' => $editId
        ));

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function testDataSourceAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $msg = '';

        $type = $request->get('type', '');
        $databasename = $request->get('database_name', '');
        $username = $request->get('username', '');
        $password = $request->get('password', '');
        $host = $request->get('host', '');

        if ($type != "sugarcrm") {
            $result = true == $this->pdoConnect($username, $password, $databasename, $host, $type);
        } else {
            $url = $host;
            $url = rtrim($url, "/");
            $url .= "/service/v3/rest.php";

            $wrapper = new SugarWrapper($url, $username, $password);
            $error = $wrapper->get_error();

            if(is_bool($error) && $error == true) {
                $result = true;
            } else {
                $msg = $error['name'];
                $result = false;
            }

        }

        $response = new JsonResponse(array(
            'success' => $result,
            'msg' => $msg
        ));
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getLookupsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $repository = $user->getRepository();
        $session = $user->getSession();

        $spacesStore = new SpacesStore($session);
        $RestDictionary = new RESTDictionary($repository, $spacesStore, $session);
        $props = $RestDictionary->GetAllProperties();

        $fields = array();
        $customFields = array();
        $dataSourcesArr = array();

        $em = $this->getDoctrine()->getManager();
        $dataSources = $em->getRepository('IfrescoClientBundle:DataSource')->findAll();

        /**
         * @var DataSource $source
         */
        foreach ($dataSources as $source) {
            $dataSourcesArr[$source->getId()] = $source->getName();
        }

        $props = $this->filterProps($props, $this->getAllowedPrefs());

        if ($props != null) {
            foreach ($props as $property) {
                $name = $property->name;
                $title = isset($property->title) ? $property->title : '';
                $dataType = $property->dataType;
                if ($dataType != "d:text" && $dataType != "d:mltext" && $dataType != "d:int" && $dataType != "d:long") {
                    continue;
                }

                if (empty($title) || $title == "null") {
                    $showTitle = $name;
                } else {
                    $showTitle = $name." ".$title;
                }

                $fields[] = array(
                    "name" => $name,
                    "title" => $title,
                    "showTitle" => $showTitle
                );
            }
        }

        $fields = $this->arraySort($fields, 'name');
        $SearchTemplates = $em->getRepository('IfrescoClientBundle:SearchTemplate')->findAll();

        if ($SearchTemplates != null) {
            /**
             * @var SearchTemplate $template
             */
            foreach ($SearchTemplates as $template) {
                $data = $template->getJsonData();
                $data = json_decode($data, true);

                if(isset($data['customFields'])) {
                    foreach ($data['customFields'] as $customField) {
                        $name = $template->getName() . ':' . $customField['custom_field_lable'];
                        if(count($customField['customFieldValues'])) {
                            $mdStr = array();
                            foreach($customField['customFieldValues'] as $cValue) {
                                $mdStr[] = $cValue['name'];
                            }
                            sort($mdStr);
                            $mdStr = md5(implode('', $mdStr));
                            $customFields[] = array(
                                "name" => $name,
                                "index" => $mdStr,
                                "title" => $name,
                                "showTitle" => $name
                            );
                        }
                    }
                }
            }
        }

        $dbLookups = $em->getRepository('IfrescoClientBundle:Lookup')->findAll();
        $lookups = array();

        if ($dbLookups != null) {
            /**
             * @var Lookup $lookup
             */
            foreach ($dbLookups as $lookup) {
                $lookups[] = array(
                    "field"     => $lookup->getField(),
                    "type"      => $lookup->getType(),
                    "data"      => $lookup->getFieldData(),
                    "params"    => $lookup->getParams(),
                    "single"    => $lookup->getIsSingle(),
                    "applyto"   => $lookup->getApplyTo(),
                    "usecache"  => $lookup->getUseCache()
                );
            }
        }

        $result = array(
            'lookups' => $lookups,
            'fields' => $fields,
            'customFields' => $customFields,
            'dataSources' => $dataSourcesArr

        );
        $newResult = array('success' => true);
        foreach ($result as $key => $value) {
            $array = array();
            foreach($value as $fieldKey => $field) {
                if ($key == 'dataSources') {
                    $array[] = array(
                        'id' => $fieldKey,
                        'name' => $field
                    );
                } else {
                    $array[] = $field;
                }
            }

            $newResult[$key] = $array;
        }

        return new JsonResponse($newResult);
    }

    public function saveLookupsAction(Request $request) {
        $returnArr = array("success" => false);

        try {
            $dataGet = $request->get('data');

            $data = array();
            if (!empty($dataGet) && $dataGet != "{}") {
                $data = json_decode(($dataGet));
            }

            $em = $this->getDoctrine()->getManager();
            $em->createQueryBuilder()->delete()->from('IfrescoClientBundle:Lookup', 'c')->getQuery()->execute();

            if (count($data) > 0) {
                foreach ($data->fieldItem as $index => $field) {
                    $randNum = $data->lookupNum[$index];
                    $Type = $data->{"lookupType" . $randNum};

                    $Lookup = $em->getRepository('IfrescoClientBundle:Lookup')->findOneBy(array(
                        'field' => $field
                    ));

                    if (!$Lookup) {
                        $Lookup = new Lookup();
                    }

                    $Lookup->setField($field);
                    $Lookup->setIsSingle($data->{"singleSelect" . $randNum});
                    $Lookup->setApplyTo($data->{"applyTo" . $randNum});
                    $Lookup->setType($Type);

                    switch ($Type) {
                        case 'category':
                            $Lookup->setFieldData($data->{"categoryNodeId" . $randNum});
                            break;
                        case 'user':
                            $Lookup->setFieldData("");
                            break;
                        case 'sugar':
                            $Lookup->setUseCache($data->{"cacheSelect" . $randNum});
                            $Lookup->setFieldData(
                                $data->{"sugarsource" . $randNum} . "/" .
                                $data->{"sugarentity" . $randNum} . "/" .
                                $data->{"sugarfield" . $randNum}
                            );
                            $Lookup->setParams(json_encode(array(
                                'relatedcolumn' => $data->{"sugarrelatedcolumn" . $randNum},
                                'relatedfield' => $data->{"sugarrelated" . $randNum}
                            )));
                            break;
                        case 'datasource':
                            $Lookup->setUseCache($data->{"cacheSelect" . $randNum});
                            $dataSourceTable = $data->{"datasourcetable" . $randNum};
                            $dataSourceColumn = $data->{"datasourcecolumn" . $randNum};
                            $Lookup->setFieldData(
                                $data->{"datasource" . $randNum} . '/' .
                                $dataSourceTable . '/' .
                                $dataSourceColumn
                            );

                            $queryData = '';
                            if($dataSourceTable == 'sql' && $dataSourceColumn == 'sql') {
                                $relMap = array();
                                if(isset($data->{"fieldsMapCols" . $randNum})) {
                                    $mapCols = $data->{"fieldsMapCols" . $randNum};
                                    $mapVals = $data->{"fieldsMap" . $randNum};
                                    for($mapI = 0; $mapI < count($mapVals); $mapI++) {
                                        if((int) $mapVals[$mapI] > -1) {
                                            $relMap[$mapCols[$mapI]] = $mapVals[$mapI];;
                                        }
                                    }
                                }

                                $queryData = json_encode(array(
                                    'sql' => $data->{"datasourcesql" . $randNum},
                                    'where' => $data->{"datasourcesqlwhere" . $randNum},
                                    'relMap' => $relMap
                                ));
                            }

                            $Lookup->setParams($queryData);
                            break;
                        case 'datasourcerel':
                            $Lookup->setFieldData($data->{"datasource" . $randNum});
                            $Lookup->setUseCache($data->{"cacheSelect" . $randNum});

                            $relMap = array();
                            if(isset($data->{"fieldsMapCols" . $randNum})) {
                                $mapCols = $data->{"fieldsMapCols" . $randNum};
                                $mapVals = $data->{"fieldsMap" . $randNum};
                                for($mapI = 0; $mapI < count($mapVals); $mapI++) {
                                    if((int) $mapVals[$mapI] > -1) {
                                        $relMap[$mapCols[$mapI]] = $mapVals[$mapI];;
                                    }
                                }
                            }


                            $Lookup->setParams(json_encode(array(
                                't1' => array(
                                    'table' => $data->{"datasourcetable" . $randNum},
                                    'col' => $data->{"datasourcecolumn" . $randNum},
                                    'colRel' => $data->{"datasourcecolumnrel" . $randNum}
                                ),
                                't2' => array(
                                    'table' => $data->{"datasourcetable2" . $randNum},
                                    'col' => $data->{"datasourcecolumn2" . $randNum},
                                    'colRel' => $data->{"datasourcecolumnrel2" . $randNum}
                                ),
                                'relatedcolumn' => $data->{"datasourcerelatedcolumn" . $randNum},
                                'relMap' => count($relMap) == 0 ? null : $relMap
                            )));
                            break;
                        default: {
                            break;
                        }
                    }

                    $em->persist($Lookup);
                    $em->flush();
                }
            }

            $returnArr["success"] = true;
        }
        catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function getDataSourceTablesAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        /**
         * @var DataSource $dataSource
         */
        $dataSource = $em->getRepository('IfrescoClientBundle:DataSource')->find($request->get('dataSourceId'));

        $success = true;
        $tables = array();
        $msg = '';

        $pdo = $this->pdoConnect(
            $dataSource->getUsername(),
            $dataSource->getPassword(),
            $dataSource->getDatabaseName(),
            $dataSource->getHost(),
            $dataSource->getType()
        );

        if($pdo) {
            try {
                $tablesResult = $pdo->query('SHOW TABLES');
                if($tablesResult){
                    foreach($tablesResult->fetchAll() as $table) {
                        $tables[]['name'] = $table[0];
                    }
                }
            }
            catch(\Exception $e) {
                $msg = $e->getMessage();
                $success = false;
            }

        } else {
            $success = false;
        }

        $response = new JsonResponse(array(
            'success' => $success,
            'data' => $tables,
            'msg' => $msg
        ));

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function getDataSourceColumnsAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $tbl = $request->get('tbl');
        /**
         * @var DataSource $dataSource
         */
        $dataSource = $em->getRepository('IfrescoClientBundle:DataSource')->find($request->get('dataSourceId'));

        $success = false;
        $columns = array();
        $msg = '';

        $pdo = $this->pdoConnect(
            $dataSource->getUsername(),
            $dataSource->getPassword(),
            $dataSource->getDatabaseName(),
            $dataSource->getHost(),
            $dataSource->getType()
        );

        if($pdo) {
            try {
                $columns_result = $pdo->query('SHOW COLUMNS FROM ' . $tbl);
                if($columns_result) {
                    foreach($columns_result->fetchAll() as $table) {
                        $columns[]['name'] = $table[0];
                    }
                }
                $success = true;
            } catch(\Exception $e) {
                $msg = $e->getMessage();
            }
        }

        $response = new JsonResponse(array(
            'success' => $success,
            'data' => $columns,
            'msg' => $msg
        ));

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function getTreeRootFolderAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        /**
         * @var Setting $treeRootFolder
         */
        $treeRootFolder = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'treeRootFolder'
        ));

        $data = array();
        if ($treeRootFolder) {
            $data['nodeId'] = $treeRootFolder->getValueString();
        }

        return new JsonResponse(array(
            'success' => true,
            'data' => $data
        ));
    }

    public function getCategoriesTreeAction(Request $request) {
        $readAll = $request->get('readall');
        $values = $request->get('values');
        $nocache = $request->get('nocache');
        $defaultValues = array();
        if (!empty($values)) {
            $defaultValues = explode(",", $values);
            if (count($defaultValues) == 0) {
                $defaultValues = array($values);
            }
        }

        if (empty($readAll)) {
            $readAll = false;
        }

        if (!empty($readAll) && $readAll == "true" || $readAll == true) {
            $readAll = true;
        }

        $categoryName = $_POST['node'];
        if ($categoryName == "root" || empty($categoryName)) {
            $categoryName = "";
            $breadCrumb = "";
        } else {
            $breadCrumb = urldecode($categoryName) . "/";
        }

        if ($nocache == "true") {
            $nocache = true;
            /**
             * @var User $user
             */
            $user = $this->get('security.context')->getToken();
            $repository = $user->getRepository();
            $session = $user->getSession();
            $spacesStore = new SpacesStore($session);

            $this->_restCategories = new RESTCategories($repository, $spacesStore, $session);
            $categories = $this->_restCategories->GetCategories($categoryName);
        } else {
            $nocache = false;
            $categories = CategoryCache::getInstance($this->get('security.context')->getToken())->getCachedCategories($categoryName);
        }

        $array = array();

        $iconClasses = array(
            "tag_green",
            "tag_orange",
            "tag_pink",
            "tag_purple",
            "tag_yellow"
        );

        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis", $breadCrumb, $match);
            if ($count > 4) {
                $iconCls = "tag_red";
            } else {
                $iconCls = $iconClasses[$count];
            }
            foreach ($categories->items as $item) {
                $nodeId = str_replace("workspace://SpacesStore/", "", $item->nodeRef);
                $checked = false;
                if (in_array($item->nodeRef, $defaultValues)) {
                    $checked = true;
                }

                $arrVal = array(
                    "cls" => "folder",
                    "id" => str_replace(" ", "%20", $breadCrumb . $item->name),
                    "nodeId" => $nodeId,
                    "checked" => $checked,
                    "expanded" => $checked,
                    "leaf" => ($item->hasChildren == true ? false : true),
                    "iconCls" => 'category_' . $iconCls,
                    "text" => $item->name
                );


                if ($readAll == true && $item->hasChildren == true) {
                    $children = $this->readRecursiveCategory($breadCrumb . $item->name, $defaultValues, $nocache);
                    $arrVal["children"] = $children["items"];
                    if ($children["found"] == true) {
                        $arrVal["expanded"] = true;
                    }
                }

                $array[] = $arrVal;
            }
        }

        return new JsonResponse($array);
    }

    public function getSystemSettingsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('IfrescoClientBundle:Setting')->findAll();
        $settingsArray = array();

        /**
         * @var Setting $settingClass
         */
        foreach ($settings as $settingClass) {
            $realKey = $settingClass->getKeyString();
            $value = $settingClass->getValueString();
            switch ($realKey) {
            	case "DisableTab":
                case "Renderer":
                    if (!empty($value)) {
                        $value = json_decode($value);
                    }
                    break;
                    
                default:
                    break;
            }

            $settingsArray[$realKey] = $value;
        }

        if (!isset($settingsArray["DateFormat"]) || empty($settingsArray["DateFormat"])) {
            $settingsArray["DateFormat"] = "m/d/Y";
        }

        if (!isset($settingsArray["TimeFormat"]) || empty($settingsArray["TimeFormat"])) {
            $settingsArray["TimeFormat"] = "H:i";
        }

        if (!isset($settingsArray["DefaultNav"]) || empty($settingsArray["DefaultNav"])) {
            $settingsArray["DefaultNav"] = "folders";
        }

        $defaultNavs = array(
            "folders" => array(
                "text" => $this->get('translator')->trans("Folders"),
                "description" => $this->get('translator')->trans("Folder Tree")
            ),
            "categories" => array(
                "text" => $this->get('translator')->trans("Categories"),
                "description" => $this->get('translator')->trans("Categories tree")
            ),
            "favorites" => array(
                "text" => $this->get('translator')->trans("Favorites"),
                "description" => $this->get('translator')->trans("User favorite nodes/categories")
            ),
            "tags" => array(
                "text" => $this->get('translator')->trans("Tags"),
                "description" => $this->get('translator')->trans("Tag Scope")
            ),
        );

        if (!isset($settingsArray["DefaultTab"]) || empty($settingsArray["DefaultTab"])) {
            $settingsArray["DefaultTab"] = "preview";
        }

        $defaultTabs = array(
            "preview" => array(
                "text" => $this->get('translator')->trans("Preview"),
                "description" => $this->get('translator')->trans("Preview of the Node -> Renderer")
            ),
            "versions" => array(
                "text" => $this->get('translator')->trans("Versions"),
                "description" => $this->get('translator')->trans("Version Control of the Node")
            ),
            "metadata" => array(
                "text" => $this->get('translator')->trans("Metadata"),
                "description" => $this->get('translator')->trans("Display Metadata of the Node")
            ),
            "parentmetadata" => array(
                "text" => $this->get('translator')->trans("Parent Metadata"),
                "description" => $this->get('translator')->trans("Display Metadata of the Parent Node")
            )
        );

        return new JsonResponse(array(
            'Settings' => $settingsArray,
            'DefaultTabs' => $defaultTabs,
            'DefaultNavs' => $defaultNavs
//            'Renderers' => $renderers
        ));
    }

    public function saveSystemSettingsAction(Request $request)
    {
        $returnArr = array("success" => false);
        $dataGet = $request->get('data');
        $em = $this->getDoctrine()->getManager();

        $data = array();
        $defaultValues = array(
        	"DisableTab" => array(),
            "Renderer" => array(),
            "DefaultTab" => "preview"
        );

        if (!empty($dataGet)) {
            $data = json_decode(($dataGet));
        }

        if ($dataGet == "{}") {
            $data = $defaultValues;
        }

        try {
            $returnArr["success"] = true;
            if(!isset($data->Renderer)) {
                $data->Renderer = array();
            }
            
            if(!isset($data->DisableTab)) {
            	$data->DisableTab = array();
            }

            foreach ($data as $key => $value) {
                $continue = true;
                switch ($key) {
                    case "Renderer":
                        $value = json_encode($value);
                        break;
                    case "DefaultNav":
                    case "DefaultTab":
                    case "DateFormat":
                    case "TimeFormat":
                    case "ParentNodeMeta":
                    case "ParentNodeMetaLevel":
                    case "ParentMetaDocumentOnly":
                    case "CSVExport":
                    case "PDFExport":
                    case "openInAlfresco":
                    case "MetaOnTreeFolder":
                    case "TabTitleLength":
                    case "treeRootFolder":
                    case "dropboxApiKey":
                    case "thumbnailHover":
                    case "shareEnabled":
                    case "scanViaSane":
                    case "SearchPaging":
                    case "MaxSearchResults":
                        break;
                    case "logoURL":
                        if(stripos($value, 'http://') !== 0 && stripos($value, 'https://') !==0) {
                            $value = 'http://' . $value;
                        }
                        break;
                    case "uploadAllowedTypes":
                        $value = json_encode($value);
                        break;
                    case "NodeCache":
                    case "CategoryCache":
                    case "OCREnabled":
                    case "UserLookupLabel":
                    case "OCROnUpload":
                        $value = (string)$value;
                        break;
                    case "DisableTab":
                    	if (!is_array($value))
                    		$value = array($value);
                    	$value = json_encode($value);
                    	break;
                    default:
                        $continue = false;
                        break;
                }

                if ($continue == true) {
                    $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                        'key_string' => $key
                    ));

                    if (!$setting) {
                        $setting = new Setting();
                    }

                    $setting->setKeyString($key);
                    $setting->setValueString($value);
                    $em->persist($setting);
                }
            }

            $em->flush();
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getEmailSettingsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $foundSettings = false;
        $emailSettings = array();

        $getSettings = array("SMTP_HOST" => array("name" => $this->get('translator')->trans("SMTP Host"), "value" => ""),
            "SMTP_PORT" => array("name" => $this->get('translator')->trans("SMTP Port"), "value" => "25"),
            "SMTP_AUTH" => array("name" => $this->get('translator')->trans("SMTP Authentication"), "type" => "checkbox", "value" => "true"),
            "SMTP_USERNAME" => array("name" => $this->get('translator')->trans("SMTP Username"), "value" => ""),
            "SMTP_PASSWORD" => array("name" => $this->get('translator')->trans("SMTP Password"), "value" => ""),
            "" => array("name" => "", "value" => ""),
            "FROM_EMAIL" => array("name" => $this->get('translator')->trans("From Email"), "value" => ""),
            "FROM_NAME" => array("name" => $this->get('translator')->trans("From Name"), "value" => "ifresco client"),
        );
        foreach ($getSettings as $settingKey => $settingEntry) {
            $em = $this->getDoctrine()->getManager();
            /**
             * @var Setting $setting
             */
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => $settingKey
            ));

            $tmp[$settingKey] = array();
            $tmp[$settingKey]["name"] = $settingEntry["name"];
            if (isset($settingEntry["type"])) {
                $tmp[$settingKey]["type"] = $settingEntry["type"];
            }

            if ($setting != null) {
                $tmp[$settingKey]["value"] = $setting->getValueString();

                if (isset($settingEntry["type"]) && $settingEntry["type"] == "checkbox" && $tmp[$settingKey]["value"] == "true") {
                    $tmp[$settingKey]["checked"] = true;
                }

                if (isset($settingEntry["type"]) && $settingEntry["type"] == "checkbox")
                    $tmp[$settingKey]["value"] = "true";

                $foundSettings = true;
            } else {
                $tmp[$settingKey]["value"] = $settingEntry["value"];
            }
        }

        if(isset($tmp)) {
            $emailSettings = $tmp;
        }

        return new JsonResponse(array(
            'success' => $foundSettings,
            'data' => $emailSettings
        ));
    }

    public function saveEmailSettingsAction(Request $request)
    {
        $returnArr = array("success" => false);
        $dataGet = $request->get('data');

        $data = array();
        $defaultValues = array(
            "SMTP_AUTH" => "false",
            "SMTP_PORT" => "25",
            "FROM_NAME" => "ifresco client"
        );

        if (!empty($dataGet)) {
            $data = json_decode($dataGet);
        }

        if ($dataGet == "{}") {
            $data = $defaultValues;
        }

        try {
            $dataArr = (array)$data;
            if (!isset($dataArr["SMTP_AUTH"])) {
                $append = array("SMTP_AUTH" => "false");
                $data = (object)array_merge((array)$data, (array)$append);
            }

            foreach ($data as $key => $value) {
                $em = $this->getDoctrine()->getManager();
                $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                    'key_string' => $key
                ));


                if ($setting == null) {
                    $setting = new Setting();
                }

                $setting->setKeyString($key);
                $setting->setValueString($value);
                $em->persist($setting);
                $em->flush();
            }

            $returnArr["success"] = true;
        }
        catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0',false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function getNamespaceMappingSettingsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $nameSpaces = $em->getRepository('IfrescoClientBundle:NamespaceMapping')->findAll();

        $templateArray = array("namespacemaps" => array());

        /**
         * @var NamespaceMapping $namespaceMap
         */
        foreach ($nameSpaces as $namespaceMap) {
            $templateArray["namespacemaps"][] = array(
                "id" => $namespaceMap->getId(),
                "namespace" => $namespaceMap->getNamespace(),
                "prefix" => $namespaceMap->getPrefix()
            );
        }

        $response = new JsonResponse($templateArray);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function saveNamespaceMappingSettingsAction(Request $request)
    {
        $returnArr = array("success" => false);

        $data = $request->get('data');
        if (!empty($data)) {
            $returnArr["success"] = true;
            $data = json_decode($data);

            $em = $this->getDoctrine()->getManager();
            foreach ($data as $NameSpace) {
                if (isset($NameSpace->id) && $NameSpace->id != 0) {
                    /**
                     * @var NamespaceMapping $namespaceMapping
                     */
                    $namespaceMapping = $em->getRepository('IfrescoClientBundle:NamespaceMapping')->find($NameSpace->id);
                    $namespaceMapping->setNamespace($NameSpace->namespace);
                    $namespaceMapping->setPrefix($NameSpace->prefix);
                } else {
                    $NameSpaceMap = $em->getRepository('IfrescoClientBundle:NamespaceMapping')->findOneBy(array(
                        'namespace' => $NameSpace->namespace,
                        'prefix' => $NameSpace->prefix
                    ));

                    if (!$NameSpaceMap) {
                        $NameSpaceMap = new NamespaceMapping();
                    }

                    $NameSpaceMap->setNamespace($NameSpace->namespace);
                    $NameSpaceMap->setPrefix($NameSpace->prefix);
                    $em->persist($NameSpaceMap);
                }
            }

            $em->flush();
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function deleteNamespaceMappingSettingsAction(Request $request)
    {
        $returnArr = array("success" => false);

        $NameSpace = $request->get('data');
        if (!empty($NameSpace)) {
            $returnArr["success"] = true;
            $NameSpace = json_decode($NameSpace);

            $em = $this->getDoctrine()->getManager();
            $em->createQueryBuilder()
                ->delete()
                ->from('IfrescoClientBundle:NamespaceMapping', 'n')
                ->where('n.namespace = :namespace')
                ->andWhere('n.prefix = :prefix')
                ->setParameter('namespace', $NameSpace->namespace)
                ->setParameter('prefix', $NameSpace->prefix)
                ->getQuery()
                ->execute()
            ;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function getFilterPropertiesSettingsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $repository = $user->getRepository();
        $session = $user->getSession();

        $spacesStore = new SpacesStore($session);

        $RestDictionary = new RESTDictionary($repository, $spacesStore, $session);
        $props = $RestDictionary->GetAllProperties();

        $prefArr = array();
        if ($props != null) {
            foreach ($props as $property) {
                if(preg_match('/^([^:]+):/', $property->name, $pref)) {
                    if(!in_array($pref[1], $prefArr)) {
                        $prefArr[] = $pref[1];
                    }
                }
            }
        }

        $allowed = $this->getAllowedPrefs();

        $em = $this->getDoctrine()->getManager();
        /**
         * @var Setting $setting
         */
        $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'allowedPrefs'
        ));
        if ($setting) {
            $allowed = json_decode($setting->getValueString(), true);

            if(count($allowed) == 0) {
                $allowed = array('cm');
            }
        }

        asort($prefArr);

        return new JsonResponse(array(
            'success' => true,
            'properties' => array(
                'prefArr' => $prefArr,
                'allowed' => $allowed
            )
        ));
    }

    public function saveFilterPropertiesSettingsAction(Request $request)
    {
        $returnArr = array("success" => false);
        $dataGet = $request->get('data');

        if ($dataGet == "{}") {
            $data = array('cm');
        } else {
            $data = json_decode($dataGet);
            $data = $data->prefs;
        }

        try {
            $returnArr["success"] = true;
            $em = $this->getDoctrine()->getManager();
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => 'allowedPrefs'
            ));

            if (!$setting) {
                $setting = new Setting();
            }

            $setting->setKeyString('allowedPrefs');
            $setting->setValueString(json_encode($data));
            $em->persist($setting);
            $em->flush();
        }
        catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getOnlineEditingSettingsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $isAdmin = $user->isAdmin();
        if ($isAdmin == false) {
            $this->forward('default', 'module');
        }

        $foundSettings = false;

        $getSettings = array(
            "OnlineEditing" => "none",
            "OnlineEditingZohoApiKey" => "",
            "OnlineEditingZohoSkey" => ""
        );

        $data = array();
        foreach ($getSettings as $settingKey => $settingDefVal) {
            $em = $this->getDoctrine()->getManager();
            /**
             * @var Setting $setting
             */
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => $settingKey
            ));

            if ($setting) {
                $data[$settingKey] = $setting->getValueString();
                $foundSettings = true;
            } else {
                $data[$settingKey] = $settingDefVal;
            }
        }

        return new JsonResponse(array(
            'success' =>  $foundSettings,
            'data' => $data
        ));
    }

    public function saveOnlineEditingSettingsAction(Request $request)
    {
        $returnArr = array("success" => false);

        $dataGet = $request->get('data');
        $data = array();
        $defaultValues = array(
            "OnlineEditing" => null,
            "OnlineEditingZohoApiKey" => null,
            "OnlineEditingZohoSkey" => null
        );
        if (!empty($dataGet)) {
            $data = json_decode($dataGet);
        }

        if ($dataGet == "{}") {
            $data = $defaultValues;
        }

        try {
            $saveSettings = array(
                "OnlineEditing",
                "OnlineEditingZohoApiKey",
                "OnlineEditingZohoSkey"
            );

            $em = $this->getDoctrine()->getManager();

            foreach ($saveSettings as $settingKey) {
                $settingVal = isset($data->{$settingKey}) ? $data->{$settingKey} : '';
                $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                    'key_string' => $settingKey
                ));

                if ($setting == null) {
                    $setting = new Setting();
                }

                $setting->setKeyString($settingKey);
                $setting->setValueString($settingVal);
                $em->persist($setting);
            }

            $em->flush();

            $returnArr["success"] = true;
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getTreeCheckSettingsAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $dirVar = str_replace("/", "", $request->get('node'));
        $rootNode = empty($dirVar) || $dirVar == "root" ? $spacesStore->companyHome : $session->getNode($spacesStore, $dirVar);

        $array = array();

        if ($rootNode != null) {
            if (count($rootNode->children) > 0) {
                foreach ($rootNode->children as $child) {

                    $Node = $session->getNode($spacesStore, $child->child->id);
                    if (
                        $Node->type == "{http://www.alfresco.org/model/content/1.0}folder" ||
                        $Node->type == "{http://www.alfresco.org/model/site/1.0}sites" ||
                        $Node->type == "{http://www.alfresco.org/model/site/1.0}site"
                    ) {
                        $nodePath = $Node->getFolderPath() . "/" . $Node->cm_name . "/";
                        $qnodePath = $Node->getRealPath();
                        $arrVal = array(
                            "cls" => "folder",
                            "id" => $Node->getId(),
                            "checked" => false,
                            "leaf" => false,
                            "path" => $nodePath,
                            "qpath" => $qnodePath,
                            "text" => $Node->cm_name,
                            "qtip" => $Node->cm_title
                        );

                        if ($Node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
                            $arrVal["iconCls"] = "sites-icon";
                        }
                        $array[] = $arrVal;
                    }
                }
            }
        }

        $this->sortByOld("text", $array, SORT_ASC);
        return new JsonResponse($array);
    }

    public function getDropboxSettingsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $isAdmin = $user->isAdmin();
        if ($isAdmin == false) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('IfrescoClientBundle:Setting')->findAll();

        $settingsArray = array();

        /**
         * @var Setting $settingClass
         */
        foreach ($settings as $settingClass) {
            $realKey = $settingClass->getKeyString();
            $value = $settingClass->getValueString();
            switch ($realKey) {
                case "Renderer":
                    if (!empty($value)) {
                        $value = json_decode($value);
                    }
                    break;
                default:
                    break;
            }
            $settingsArray[$realKey] = $value;
        }

        return new JsonResponse(array(
            'success' => true,
            'data' => $settingsArray
        ));
    }

    public function getInterfaceAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('IfrescoClientBundle:Setting')->findAll();
        $settingsArray = array();

        /**
         * @var Setting $setting
         */
        foreach ($settings as $setting) {
            $realKey = $setting->getKeyString();
            $value = $setting->getValueString();
            switch ($realKey) {
                case "Renderer":
                    if (!empty($value)) {
                        $value = json_decode($value);
                    }
                    break;
                default:
                    break;
            }
            $settingsArray[$realKey] = $value;
        }

        return new JsonResponse(array(
            'success' => true,
            'data' => $settingsArray
        ));
    }

    public function uploadLogoAction()
    {
        //TODO: when will be available to change logo
        $logoWidthV1 = 94;
        $logoHeightV1 = 50;
        $logoPathV1 = 'images/custom_logo94x50.png';
        $logoWidthV2 = 170;
        $logoHeightV2 = 90;
        $logoPathV2 = 'images/custom_logo170x90.png';
        $logoWidthV3 = 200;
        $logoHeightV3 = 106;
        $logoPathV3 = 'images/custom_logo200x106.png';

        @set_time_limit(5 * 60);
        try {
            $json['id'] = "id";
            $json['jsonrpc'] = "2.0";
            $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

            $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
            $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
            $tmpFile = $this->get('kernel')->getCacheDir() . '/Upload/' . $fileName;

            if(!file_exists($this->get('kernel')->getCacheDir() . '/Upload')) {
                mkdir($this->get('kernel')->getCacheDir() . '/Upload', 0777);
            }

            $contentType = '';
            if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
                $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
            }

            if (isset($_SERVER["CONTENT_TYPE"])) {
                $contentType = $_SERVER["CONTENT_TYPE"];
            }

            $out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");

            if (strpos($contentType, "multipart") !== false) {
                if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                    if ($out) {
                        $in = fopen($_FILES['file']['tmp_name'], "rb");
                        if ($in) {
                            while ($buff = fread($in, 4096))
                                fwrite($out, $buff);
                        } else {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                        }

                        fclose($in);
                        fclose($out);
                        @unlink($_FILES['file']['tmp_name']);
                    } else {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                    }
                }
            } else {
                $out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    $in = fopen("php://input", "rb");
                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    }
                    fclose($in);
                    fclose($out);
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                }
            }

            if ((($chunk+1) == $chunks && $chunks > 0) || $chunks == 0) {
                $imagine = new Imagine();
                $transformation = new Transformation();

                $transformation->thumbnail(new Box($logoWidthV1, $logoHeightV1), 'outbound')
                    ->save($logoPathV1, array('quality' => 100));
                $transformation->apply($imagine->open($tmpFile));

                $imagine = new Imagine();
                $transformation = new Transformation();

                $transformation->thumbnail(new Box($logoWidthV2, $logoHeightV2), 'outbound')
                    ->save($logoPathV2, array('quality' => 100));
                $transformation->apply($imagine->open($tmpFile));

                $imagine = new Imagine();
                $transformation = new Transformation();

                $transformation->thumbnail(new Box($logoWidthV3, $logoHeightV3), 'outbound')
                    ->save($logoPathV3, array('quality' => 100));
                $transformation->apply($imagine->open($tmpFile));

                $json['filenamev1'] = $logoPathV1;
            }

            $json['result'] = "null";
        } catch (\Exception $e) {
            $json = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }

        $response = new JsonResponse($json);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getAspectListAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $values = $request->get('values');
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $valuesArr = array();
        if (!empty($values)) {
            $split = explode(",", $values);
            if (count($split) > 0) {
                for ($i = 0; $i < count($split); $i++) {
                    $valuesArr[] = $split[$i];
                }
            } else {
                $valuesArr[] = $values;
            }
        }

        $restAspects = new RESTAspects($repository, $spacesStore, $session);
        $aspectList = $this->arraySort($restAspects->GetAllAspects(), 'name');
        $em = $this->getDoctrine()->getManager();
        $savedAspectsList = $em->getRepository('IfrescoClientBundle:AllowedAspect')->findAll();
        $savedAspectsArray = array();
        /**
         * @var AllowedAspect $allowedAspect
         */
        foreach ($savedAspectsList as $allowedAspect) {
            $savedAspectsArray[] = $allowedAspect->getName();
        }
        $types = array();
        if ($aspectList != null) {
            foreach ($aspectList as $aspect) {
                $name = $aspect->name;
                $title = empty($aspect->title) ? $name : $name . ' - ' . $aspect->title;
                $state = in_array($name, $valuesArr) ? 'selected' : '';
                $type = array(
                    'attributes' => array(
                        'value' => $name,
                        'id' => str_replace(":", "_", $name)
                    ),
                    'state' => $state,
                    'text' => $title
                );

                if (count($savedAspectsArray) > 0 && empty($type['state']) && in_array($name, $savedAspectsArray)) {
                    $type['state'] = 'selected';
                }

                $types[] = $type;
            }
        }

        $response = new JsonResponse(array(
            'success' => true,
            'data' => $types
        ));
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function saveAspectListAction(Request $request)
    {
        $returnArr = array("success" => false);

        $dataGet = $request->get('data');
        $data = array();
        $defaultValues = array();
        if (!empty($dataGet)) {
            $data = json_decode($dataGet);
        }

        if ($dataGet == "{}") {
            $data = $defaultValues;
        }

        try {
            $returnArr["success"] = true;
            foreach ($data as $key => $value) {
                switch ($key) {
                    case "allowedAspects":
                        $em = $this->getDoctrine()->getManager();
                        $em->createQueryBuilder()->delete()
                            ->from('IfrescoClientBundle:AllowedAspect', 'a')
                            ->getQuery()
                            ->execute()
                        ;

                        if (is_array($value) && count($value) > 0) {
                            $array = $value[0];
                            foreach ($array as $aspect) {
                                $DBAspect = new AllowedAspect();
                                $DBAspect->setName($aspect);
                                $em->persist($DBAspect);
                            }

                            $em->flush();
                        }
                        break;
                    default:
                        break;
                }

            }
        }
        catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getTypeListAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $values = $request->get('values');
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $valuesArr = array();
        if (!empty($values)) {
            $split = explode(",",$values);
            if (count($split) > 0) {
                for ($i = 0; $i < count($split); $i++) {
                    $valuesArr[] = $split[$i];
                }
            } else {
                $valuesArr[] = $values;
            }
        }

        $restDictionary = new RESTDictionary($repository, $spacesStore, $session);
        $subClasses = $restDictionary->GetSubClassDefinitions("cm_content");
        $em = $this->getDoctrine()->getManager();
        $savedTypesList = $em->getRepository('IfrescoClientBundle:AllowedType')->findAll();
        $savedTypesArray = array();
        /**
         * @var AllowedType $allowedType
         */
        foreach ($savedTypesList as $allowedType) {
            $savedTypesArray[] = $allowedType->getName();
        }

        $types = array();
        if ($subClasses != null) {
            foreach ($subClasses as $subClass) {
                $name = $subClass->name;
                $title = empty($subClass->title) ? $name : $name . ' - ' . $subClass->title;
                $state = in_array($name, $valuesArr) ? 'selected' : '';
                $type = array(
                    'attributes' => array(
                        'value' => $name,
                        'id' => str_replace(":", "_", $name)
                    ),
                    'state' => $state,
                    'text' => $title
                );

                if (count($savedTypesArray) > 0 && empty($type['state']) && in_array($name, $savedTypesArray)) {
                    $type['state'] = 'selected';
                }

                $types[] = $type;
            }
        }

        $typesArr = $this->arraySort($types, "text");
        $types = $typesArr;
        if (count($typesArr) > 0) {
            $types = array();
            foreach ($typesArr as $v) {
                $types[] = $v;
            }
        }

        $response = new JsonResponse(array(
            'success' => true,
            'data' => $types
        ));
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function saveTypeListAction(Request $request)
    {
        $returnArr = array("success" => false);

        $dataGet = $request->get('data');
        $data = array();
        $defaultValues = array();
        if (!empty($dataGet)) {
            $data = json_decode($dataGet);
        }

        if ($dataGet == "{}") {
            $data = $defaultValues;
        }

        try {
            $returnArr["success"] = true;
            $em = $this->getDoctrine()->getManager();

            foreach ($data as $key => $value) {
                switch ($key) {
                    case "allowedTypes":
                        $em->createQueryBuilder()
                            ->delete()
                            ->from('IfrescoClientBundle:AllowedType', 'a')
                            ->getQuery()
                            ->execute()
                        ;

                        if (is_array($value) && count($value) > 0) {
                            $array = $value[0];
                            foreach ($array as $type) {
                                $dBType = new AllowedType();

                                $dBType->setName($type);
                                $em->persist($dBType);
                            }

                            $em->flush();
                        }
                        break;
                    default:
                        break;
                }
            }
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function uploadAllowedTypesAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        /**
         * @var Setting $uploadAllowedTypes
         */
        $uploadAllowedTypes = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'uploadAllowedTypes'
        ));
        $settingsArray = array();

        if ($uploadAllowedTypes) {
            $allowedTypes = $uploadAllowedTypes->getValueString();
            $settingsArray = explode(',', trim(trim($allowedTypes, '['), ']'));
            foreach ($settingsArray as $key => $value) {
                $settingsArray[$key] = trim($value, '"');
            }
        }

        $defaultTypes = array(
            "jpg", "gif", "png", "jpeg", "tif", "tga", "psd", "esp", "doc",
            "docx", "ppt", "pptx", "xls", "xlsx", "odt", "ods", "odp", "odg",
            "odc", "odf", "odi", "ott", "ots", "otp", "otg", "msg", "eml", "pdf",
            "txt", "csv", "rtf", "wmv", "avi", "mpeg", "flv", "zip", "mp3", "mp4", "xml", "ini"
        );

        return new JsonResponse(array(
            'success' => true,
            'types' => $defaultTypes,
            'selected' => $settingsArray
        ));
    }

    public function exportSettingsAction(Request $request)
    {
        try {
            $dataGet = $request->get('data');

            $data = array();
            $response = array();
            if (!empty($dataGet)) {
                $data = json_decode(($dataGet), true);
            }

            $em = $this->getDoctrine()->getManager();
            foreach($data as $key => $option) {
                $response[$key] = $this->{"_export_$key"}($em);
            }

            $returnArr["data"] = json_encode($response);
            $returnArr["success"] = true;
        } catch (\Exception $e) {
            $returnArr["success"] = false;
            $returnArr["err"] = $e->getMessage();
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function importSettingsAction(Request $request)
    {
        try {
            $dataGet = $request->get('data');

            $data = array();
            $response = array();
            if (!empty($dataGet)) {
                $data = json_decode($dataGet, true);
            }

            $em = $this->getDoctrine()->getManager();
            foreach($data as $key => $option) {
                $response[$key] = $this->{"_import_$key"}($em, $option);
            }

            $returnArr["success"] = true;
        }
        catch (\Exception $e) {
            $returnArr["success"] = false;
            $returnArr["err"] = $e->getMessage();
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getCurrentJobsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $jobs = $em->getRepository('IfrescoClientBundle:CurrentJob')->findAll();

        $jobsArray = array(
            "success" => true,
            "jobs" => array());

        /**
         * @var CurrentJob $job
         */
        foreach ($jobs as $job) {
            $type = $job->getType();
            $id = $job->getId();
            $jobsArray["jobs"][] = array(
                "id" => $id,
                "type" => $type,
                "created" => $job->getCreatedAt()->format(Registry::getSetting('DateFormat') . ' ' . Registry::getSetting('TimeFormat')),
                "status" => $job->getStatus());
        }

        $response = new JsonResponse($jobsArray);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function clearCurrentJobsAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $all = $request->get('all');
        $success = array("success" => false);
        try {
            $em = $this->getDoctrine()->getManager();
            if (isset($all) && $all == true) {
                $em->createQueryBuilder()->delete()->from('IfrescoClientBundle:CurrentJob', 'j')->getQuery()->execute();
            } else {
                $em->createQueryBuilder()->delete()->from('IfrescoClientBundle:CurrentJob', 'j')
                    ->where("j.status LIKE 'DONE%'")->getQuery()->execute();
            }

            $success["success"] = true;
        } catch (\Exception $e) {
            $success["success"] = false;
        }

        $response = new JsonResponse($success);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function downloadCurrentJobAction($id)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        /**
         * @var CurrentJob $job
         */
        $job = $em->getRepository('IfrescoClientBundle:CurrentJob')->find($id);

        if (!$job) {
            throw new \Exception("No job found");
        }

        if (!preg_match("/DONE/", $job->getStatus())) {
            throw new \Exception("Job is not finished yet!");
        }

        $data = $job->getJsonData();
        $data = json_decode($data);
        if (!is_array($data) && !is_object($data)) {
            throw new \Exception("Something went wrong. No file exists");
        }
        $data = (object)$data;

        if (!isset($data->link) || empty($data->link)) {
            throw new \Exception("Something went wrong. No file exists");
        }

        $csvContent = "";
        $handle = fopen($data->link, "r");
        while ( ($buf=fread( $handle, 8192 )) != '' ) {
            $csvContent .= $buf;
        }

        $response = new Response("\xEF\xBB\xBF" . $csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set(
            'Content-Disposition','attachment; filename=' .
            $user->getUsername() . '_' . date('Y-m-d-H-i-s') . '.csv', false
        );
        $response->headers->set('Pragma','no-cache');
        return $response;
    }

    public function getExportJobsAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }
        $result = array(
            'success' => true,
            'data' => array(
                'FoundSettings' => false,
                'FoundEmail' => false,
                'Email' => '',
                'ColumnSets' => null,
                'Fields' => null
            )
        );

        $em = $this->getDoctrine()->getManager();
        $columnSets = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->findAll();
        foreach($columnSets as $columnSet) {
            $result['data']['ColumnSets'][] = array(
                'id' => $columnSet->getId(),
                'name' => $columnSet->getName(),
                'jsonFields' => $columnSet->getJsonFields()
            );
        }

        /**
         * @var Setting $exportFields
         */
        $exportFields = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'ExportFields'
        ));
        /**
         * @var Setting $exportEmail
         */
        $exportEmail = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'ExportEmailField'
        ));

        if ($exportFields != null) {
            $jsonData = json_decode($exportFields->getValueString());
            if ($exportEmail != null) {
                $emailVal = $exportEmail->getValueString();
                if (!empty($emailVal)) {
                    $result['data']['Email'] = $emailVal;
                    $result['data']['FoundEmail'] = true;
                }
            }
            $result['data']['Fields'] = $jsonData;
            $result['data']['FoundSettings'] = true;
        } else {
            $result['data']['Fields'] = null;
        }

        return new JsonResponse($result);
    }

    public function saveExportJobsAction(Request $request)
    {
        @set_time_limit(5 * 60);

        $data = $request->get('data');
        $folderExport = $request->get('folders');
        $email = $request->get('email');

        if (!isset($folderExport) || empty($folderExport)) {
            $folderExport = true;
        }

        if ($folderExport == "true") {
            $folderExport = true;
        } else {
            $folderExport = false;
        }

        $emailFound = false;
        if (isset($email) && !empty($email)) {
            $emailFound = true;
        }

        if (!empty($data)) {
            $data = json_decode($data);
            $params = array(
                'edit'    => $data->edit,
                'fields'    => $data->fields
            );
        } else {
            $params = array(
                'edit'    => $request->get('edit'),
                'fields' => $request->get('fields')
            );
        }

        try {
            $fields = array();
            if (count($params["fields"]) > 0) {
                foreach ($params["fields"] as $entry) {
                    $split = explode("/", $entry);
                    if (count($split) > 0) {
                        $fields[] = array(
                            "name" => $split[0],
                            "class" => $split[1],
                            "dataType" => $split[3],
                            "title" => $split[2],
                            "type" => $split[4]
                        );
                    }
                }
            }

            $em = $this->getDoctrine()->getManager();
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => 'ExportFields'
            ));

            if (!$setting) {
                $setting = new Setting();
            }

            $setting->setKeyString("ExportFields");
            $setting->setValueString(json_encode($fields));
            $em->persist($setting);
            $em->flush();

            $settingEmail = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => 'ExportEmailField'
            ));

            if ($settingEmail == null) {
                $settingEmail = new Setting();
            }

            $settingEmail->setKeyString("ExportEmailField");
            $settingEmail->setValueString($email ? $email: '');
            $em->persist($settingEmail);
            $em->flush();

            $JobId = $this->addJob($em, "Export all nodes to CSV", "WAITING");

            if ($emailFound == false) {
                $email = "null";
            }

            $jobs = new Jobs();
            $jobs->exportNodes($JobId, $fields, $folderExport, $email);

            $returnArr["success"] = true;
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["errorMsg2"] = $e->getFile();
            $returnArr["errorMsg3"] = $e->getLine();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getColumnSetAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $columns = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->findAll();
        $columnsArray = array(
            'success' => true,
            'columns' => array()
        );

        /**
         * @var SearchColumnSet $column
         */
        foreach ($columns as $column) {
            $columnsArray["columns"][] = array(
                "id" => $column->getId(),
                "name" => $column->getName(),
                "defaultset" => $column->getIsDefaultSet(),
            	"hideInMenu" => $column->getHideInMenu()
            );
        }

        $response = new JsonResponse($columnsArray);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function saveColumnSetAction(Request $request)
    {
        $returnArr = array("success" => false);
        $data = $request->get('data');

        if (!empty($data)) {
            $data = json_decode($data);
            $params = array(
                'edit'    => $data->edit,
                'name'    => isset($data->name) ? $data->name : '',
            	'hideInMenu'    => isset($data->hideInMenu) ? $data->hideInMenu : '',
                'cols'    => $data->cols
            );
        } else {
            $params = array(
                'edit'    => $request->get('edit'),
                'name'    => $request->get('name'),
            	'hideInMenu'    => $request->get('hideInMenu'),
                'cols' => $request->get('cols')
            );
        }

        try {
            if (empty($params["cols"])) {
                $params["cols"] = array();
            }

            $Columns = array();
            if (count($params["cols"]) > 0) {
                foreach ($params["cols"] as $Entry) {
                    $split = explode("/", $Entry);
                    if (count($split) > 0) {
                        $name = $split[0];
                        $class = $split[1];
                        $label = $split[2];
                        $dataType = $split[3];
                        $type = $split[4];
                        $showHide = $split[5];
                        $sort = false;
                        $asc = false;

                        if (isset($split[6])) {
                            $sorting = $split[6];
                            if ($sorting == "sort") {
                                $sort = true;
                            }
                        }

                        if (isset($split[7])) {
                            $ascending = $split[7];
                            if ($ascending == "asc") {
                                $asc = true;
                            }
                        }

                        $hide = false;
                        if ($showHide == "hide") {
                            $hide = true;
                        }

                        $Columns[] = array(
                            "name" => $name,
                            "class" => $class,
                            "dataType" => $dataType,
                            "title" => $label,
                            "type" => $type,
                            "hide" => $hide,
                            "sort" => $sort,
                            "asc" => $asc
                        );
                    }
                }
            }

            $em = $this->getDoctrine()->getManager();
            $q = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->findOneBy(array(
                'is_default_set' => true
            ));

            if ($params["edit"] == null || empty($params["edit"]) || $params["edit"] == "null") {
                $ColumnSet = new SearchColumnSet();
                $ColumnSet->setIsDefaultSet(!$q ? true : false);
            } else {
                $ColumnSet = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->find($params["edit"]);
            }

            $ColumnSet->setName($params['name']);
            $ColumnSet->setHideInMenu($params['hideInMenu']);
            $ColumnSet->setJsonFields(json_encode($Columns));
            $em->persist($ColumnSet);
            $em->flush();

            $returnArr["editId"] = $ColumnSet->getId();
            $returnArr["success"] = true;
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function editColumnSetAction($id)
    {
        $result = array(
            'FoundColumnset' => false,
            'Name' => '',
        	'HideInMenu' => false,
            'Id' => '',
            'Columns' => array()

        );

        $em = $this->getDoctrine()->getManager();
        /**
         * @var SearchColumnSet $SearchColumnSet
         */
        $SearchColumnSet = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->find($id);

        if ($SearchColumnSet) {
            $jsonData = $SearchColumnSet->getJsonFields();
            if (!empty($jsonData)) {
                $result['FoundColumnset'] = true;
                $result['Columns'] = json_decode($jsonData);
                $result['HideInMenu'] = $SearchColumnSet->getHideInMenu();
                $result['Name'] = $SearchColumnSet->getName();
                $result['Id'] = $SearchColumnSet->getId();
            }
        }

        return new JsonResponse(array(
            'success' => true,
            'data' => $result
        ));
    }

    public function deleteColumnSetAction(Request $request)
    {
        $ids = $request->get('ids');
        $data = array("success" => false);
        if (!empty($ids)) {
            $in = "";
            $params = array();

            foreach ($ids as $key => $id) {
                $in = strlen($in) > 0 ? $in . ", ?$key" : "?$key";
                $params[$key] = $id;
            }

            $this->getDoctrine()->getManager()->createQueryBuilder()
                ->delete()
                ->from('IfrescoClientBundle:SearchColumnSet', 'c')
                ->where("c.id = ($in)")
                ->setParameters($params)->getQuery()->execute()
            ;

            $data["success"] = true;
        }

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getClickSearchAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) $this->forward('default', 'module');

        $result = array(
            'success' => true,
            'data' => array('foundSettings' => false, 'columnSets' => array(), 'columnSetId' => 0)
        );

        $em = $this->getDoctrine()->getManager();
        /**
         * @var Setting $quickSearchSetting
         */
        $quickSearchSetting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'ClickSearch'
        ));

        if ($quickSearchSetting != null) {
            $jsonData = json_decode($quickSearchSetting->getValueString());
            $result['data']['fields'] = $jsonData ? $jsonData : array();
            $result['data']['foundSettings'] = true;
        } else {
            $result['data']['fields'] = array();
        }

        /**
         * @var Setting $ClickSearchColumnSet
         */
        $ClickSearchColumnSet = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'ClickSearchColumnSet'
        ));

        if ($ClickSearchColumnSet) {
            $result['data']['columnSetId'] = $ClickSearchColumnSet->getValueString();
        }

        $columnSets = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->findAll();
        /**
         * @var SearchColumnSet $columnSet
         */
        foreach ($columnSets as $columnSet) {
            $result['data']['columnSets'][] = array(
                "id" => $columnSet->getId(),
                "name" => $columnSet->getName()
            );
        }

        return new JsonResponse($result);
    }

    public function saveClickSearchAction(Request $request)
    {
    	$user = $this->get('security.context')->getToken();
    	if (!$user->isAdmin()) $this->forward('default', 'module');
    	
        $returnArr = array("success" => false);
        $data = $request->get('data');

        if (!empty($data)) {
            $data = json_decode($data);
            $params = array(
                'columnset'    => $data->columnset,
                'fields'    => $data->fields
            );
        } else {
            $params = array(
                'columnset' => $request->get('columnset'),
                'fields' => $request->get('fields')
            );
        }

        try {
            if (empty($params["fields"])) {
                $params["fields"] = array();
            }

            $fields = array();
            if (count($params["fields"]) > 0) {
                foreach ($params["fields"] as $Entry) {
                    $split = explode("/", $Entry);
                    if (count($split) > 0) {
                        $fields[] = array(
                            "name" => $split[0],
                            "class" => $split[1],
                            "dataType" => $split[3],
                            "title" => $split[2],
                            "type" => $split[4]
                        );
                    }
                }
            }

            $em = $this->getDoctrine()->getManager();
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => 'ClickSearch'
            ));

            if (!$setting) {
                $setting = new Setting();
            }

            $setting->setKeyString("ClickSearch");
            $setting->setValueString(json_encode($fields));
            $em->persist($setting);
            $em->flush();

            if($params["columnset"]) {
                $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                    'key_string' => 'ClickSearchColumnSet'
                ));

                if (!$setting) {
                    $setting = new Setting();
                }

                $setting->setKeyString("ClickSearchColumnSet");
                $setting->setValueString($params["columnset"]);
                $em->persist($setting);
                $em->flush();
            }

            $returnArr["success"] = true;
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getQuickSearchAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $result = array(
            'success' => true,
            'data' => array(
                'foundSettings' => false,
                'luceneQuery' => '',
                'fields' => array()
            )
        );

        $em = $this->getDoctrine()->getManager();
        /**
         * @var Setting $quickSearchSetting
         */
        $quickSearchSetting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'QuickSearch'
        ));

        if ($quickSearchSetting != null) {
            $JsonData = json_decode($quickSearchSetting->getValueString());
            $result['data']['fields'] = $JsonData ? $JsonData : array();
            $result['data']['foundSettings'] = true;
        }

        /**
         * @var Setting $quickSearchLucene
         */
        $quickSearchLucene = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'QuickSearchLucene'
        ));

        if ($quickSearchLucene) {
            $lucene = $quickSearchLucene->getValueString();
            $result['data']['luceneQuery'] = isset($lucene) ? str_replace('"', '&quot;', $lucene) : '';
        }

        return new JsonResponse($result);
    }

    public function saveQuickSearchAction(Request $request)
    {
        $returnArr = array("success" => false);
        $data = $request->get('data');

        if (!empty($data)) {
            $data = json_decode($data);
            $params = array(
                'fields'    => $data->fields,
                'lucene_query' => isset($data->lucene_query) ? $data->lucene_query : ''
            );
        } else {
            $params = array(
                'edit'    => $request->get('edit'),
                'fields' => $request->get('fields'),
                'lucene_query' => $request->get('lucene_query')
            );
        }

        try {
            if (empty($params["fields"])) {
                $params["fields"] = array();
            }

            if (empty($params["lucene_query"])) {
                $params["lucene_query"] = "";
            }

            $fields = array();
            if (count($params["fields"]) > 0) {
                foreach ($params["fields"] as $Entry) {
                    $split = explode("/",$Entry);
                    if (count($split) > 0) {
                        $fields[] = array(
                            "name" => $split[0],
                            "class" => $split[1],
                            "dataType" => $split[3],
                            "title" => $split[2],
                            "type" => $split[4]
                        );
                    }
                }
            }

            $em = $this->getDoctrine()->getManager();
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => 'QuickSearch'
            ));


            if (!$setting) {
                $setting = new Setting();
            }

            $setting->setKeyString("QuickSearch");
            $setting->setValueString(json_encode($fields));
            $em->persist($setting);
            $em->flush();

            $settingLucene = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
                'key_string' => 'QuickSearchLucene'
            ));

            if (!$settingLucene) {
                $settingLucene = new Setting();
            }

            $settingLucene->setKeyString("QuickSearchLucene");
            $settingLucene->setValueString($params["lucene_query"]);
            $em->persist($settingLucene);
            $em->flush();


            $returnArr["success"] = true;
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getSearchTemplatesAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $templates = $em->getRepository('IfrescoClientBundle:SearchTemplate')->findAll();
        $templateArray = array(
            "success" => true,
            "templates" => array()
        );

        /**
         * @var SearchTemplate $template
         */
        foreach ($templates as $template) {

            $templateArray["templates"][] = array(
                "id" => $template->getId(),
                "name" => $template->getName(),
                "defaultview" => $template->getIsDefaultView(),
                "multiColumns" => $template->getIsMulticolumn()
            );
        }

        $response = new JsonResponse($templateArray);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getSearchTemplateAction($id)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $data = array(
            'FoundTemplate' => '',
            'Multicolumn' => false,
            'Fulltextchild' => true,
            'Fulltextchildoverwrite' => false,
            'Class' => '',
            'Name' => '',
            'LuceneQuery' => '',
            'CustomSearch' => '',
            'ColumnsetId' => '',
            'savedSearchId' => '',
        	'contentType' => '',
            'customQueryMode' => 'and',
            'savedSearches' => array(),
            'ColumnSets' => array()
        );

        $em = $this->getDoctrine()->getManager();
        /**
         * @var SearchTemplate $searchTemplate
         */
        $searchTemplate = $em->getRepository('IfrescoClientBundle:SearchTemplate')->find($id);
        // if (!$searchTemplate) throw new \Exception('such template does not exist');
        if ($searchTemplate) {
            $JsonData = $searchTemplate->getJsonData();
            if (!empty($JsonData)) {
                $data['FoundTemplate'] = true;
                $JsonData = json_decode($JsonData);
                $data['Column1'] = isset($JsonData->Column1) ? $JsonData->Column1 : array();
                $data['Column2'] = isset($JsonData->Column2) ? $JsonData->Column2 : array();
                $data['customFields'] = isset($JsonData->customFields) ? $JsonData->customFields : array();
                $data['Tabs'] = isset($JsonData->Tabs) ? $JsonData->Tabs : '';
                $data['CustomSearch'] = isset($JsonData->custom_search) ? $JsonData->custom_search : '';
                $data['customQueryMode'] = isset($JsonData->customQueryMode) ? $JsonData->customQueryMode : 'and';
                $data['LuceneQuery'] = isset($JsonData->lucene_query) ? str_replace('"', '&quot;', $JsonData->lucene_query) : '';
                $data['Multicolumn'] = $searchTemplate->getIsMulticolumn();
                $data['Fulltextchild'] = $searchTemplate->getIsFullTextChild();
                $data['Fulltextchildoverwrite'] = $searchTemplate->getIsFullTextChildOverwrite();
                $data['Showdoctype'] = json_decode($searchTemplate->getShowDoctype());
                $data['ColumnsetId'] = $searchTemplate->getColumnSetId();
                $data['savedSearchId'] = $searchTemplate->getSavedSearchId();
                $data['contentType'] = $searchTemplate->getContentType();
                $data['Name'] = $searchTemplate->getName();
                $data['Id'] = $searchTemplate->getId();
            }
        } else {
            $data['FoundTemplate'] = false;
            $data['Column1'] = array();
            $data['Column2'] = array();
            $data['customFields'] = array();
            $data['Tabs'] = '';
            $data['CustomSearch'] = '';
            $data['customQueryMode'] = 'and';
            $data['LuceneQuery'] =  '';
            $data['Multicolumn'] = 1;
            $data['Fulltextchild'] = 0;
            $data['Fulltextchildoverwrite'] = 0;
            $data['Showdoctype'] = '[]';
            $data['ColumnsetId'] = 0;
            $data['savedSearchId'] = 0;
            $data['contentType'] = '';
            $data['Name'] = '';
            $data['Id'] = '';
        }

        $savedSearches = $em->getRepository('IfrescoClientBundle:SavedSearch')->findBy(array(
            'is_privacy' => true,
            'template' => $id
        ));
        /**
         * @var SavedSearch $savedSearch
         */
        foreach ($savedSearches as $savedSearch) {
            $data['savedSearches'][] = array(
                'id' => $savedSearch->getId(),
                'name' => $savedSearch->getName()
            );
        }

        $columnSets = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->findAll();
        /**
         * @var SearchColumnSet $columnSet
         */
        foreach ($columnSets as $columnSet) {
            $data['ColumnSets'][] = array(
                'id' => $columnSet->getId(),
                'name' => $columnSet->getName()
            );
        }

        return new JsonResponse(array(
            'success' => true,
            'data' => $data
        ));
    }

    public function saveSearchTemplateAction(Request $request)
    {
        $returnArr = array("success" => false);
        $data = $request->get('data');
        if (!empty($data)) {
            $data = json_decode(($data));
            $params = array(
                'edit'    => $data->edit,
                'name'    => isset($data->name) ? $data->name : '',
                'showdoctype'    => $data->showDoctype,
                'multiColumns'    => isset($data->multiColumns) ? $data->multiColumns:'',
                'fulltextChild'    => isset($data->fulltextChild) ? $data->fulltextChild:'',
                'fulltextChildOverwrite'    => isset($data->fulltextChildOverwrite) ? $data->fulltextChildOverwrite : '',
                'columnset'    => $data->columnset,
                'savedsearch'    => $data->savedsearch,
            	'contenttype'    => $data->contenttype,
                'col1'    => isset($data->col1) ? $data->col1 : null,
                'col2'    => isset($data->col2) ? $data->col2 : null,
                'customs'    => isset($data->customs) ? $data->customs : array(),
                'lucene_query'    => isset($data->lucene_query) ? $data->lucene_query : null,
                'tabs'    => isset($data->tabs) ? $data->tabs : null
            );
        } else {
            $params = array(
                'edit'    => $request->get('edit'),
                'name'    => $request->get('name'),
                'showdoctype' => $request->get('showDoctype'),
                'multiColumns'    => $request->get('multiColumns'),
                'fulltextChild'    => $request->get('fulltextChild'),
                'fulltextChildOverwrite'    => $request->get('fulltextChildOverwrite'),
                'columnset' => $request->get('columnset'),
                'savedsearch' => $request->get('savedsearch'),
            	'contenttype' => $request->get('contenttype'),
                'col1'    => $request->get('col1'),
                'col2'    => $request->get('col2'),
                'customs'    => $request->get('customs'),
                'lucene_query'    => $request->get('lucene_query'),
                'tabs'    => $request->get('tabs'),
            );
        }

        try {
            if (empty($params["col1"])) {
                $params["col1"] = array();
            }

            if (empty($params["col2"])) {
                $params["col2"] = array();
            }

            $customFields = array();
            if (!empty($params["customs"]) && count($params["customs"]) > 0 ) {
                foreach ($params["customs"] as $customNum) {
                    $material = array(
                        'custom_field_lable'    => isset($data->{"custom_field_lable" . $customNum}) ? $data->{"custom_field_lable" . $customNum} : null,
                        'customFieldValues'    => isset($data->{"customFieldValues_" . $customNum}) ? $data->{"customFieldValues_" . $customNum} : '',
                        'customQueryMode'    => isset($data->{"customQueryMode" . $customNum}) ? $data->{"customQueryMode" . $customNum} : null
                    );

                    if(!empty($material['customFieldValues']) && !empty($material['custom_field_lable']) && !empty($material['customQueryMode'])) {
                        $newCustomFieldValues = array();
                        if (count($material['customFieldValues']) > 0) {
                            foreach ($material['customFieldValues'] as $Entry) {
                                $split = explode("/", $Entry);
                                if (count($split) > 0) {
                                    $name = $split[0];
                                    $class = isset($split[1]) ? $split[1] : '';
                                    $label = isset($split[2]) ? $split[2] : $name;
                                    $dataType = isset($split[3]) ? $split[3] : 'd:text';
                                    $type = isset($split[4]) ? $split[4] : '';

                                    $newCustomFieldValues[] = array(
                                        "name" => $name,
                                        "class" => $class,
                                        "dataType" => $dataType,
                                        "title" => $label,
                                        "type" => $type
                                    );
                                }
                            }
                        }
                        $material['customFieldValues'] = $newCustomFieldValues;
                        $customFields[$customNum] = $material;
                    }
                }
            }

            if (empty($params["tabs"])) {
                $params["tabs"] = array();
            }

            $newCol1 = array();
            if (count($params["col1"]) > 0) {
                foreach ($params["col1"] as $Entry) {
                    $split = explode("/", $Entry);
                    if (count($split) > 0) {
                        $name = $split[0];
                        $class = $split[1];
                        $label = $split[2];
                        $dataType = $split[3];
                        $type = $split[4];

                        $newCol1[] = array(
                            "name" => $name,
                            "class" => $class,
                            "dataType" => $dataType,
                            "title" => $label,
                            "type" => $type);
                    }
                }
            }

            $newCol2 = array();
            if (count($params["col2"]) > 0) {
                foreach ($params["col2"] as $Entry) {
                    $split = explode("/", $Entry);
                    if (count($split) > 0) {
                        $name = $split[0];
                        $class = $split[1];
                        $label = $split[2];
                        $dataType = $split[3];
                        $type = $split[4];

                        $newCol2[] = array(
                            "name" => $name,
                            "class" => $class,
                            "dataType" => $dataType,
                            "title" => $label,
                            "type" => $type
                        );
                    }
                }
            }

            $newTabs = array();
            // if (count($params["tabs"]) > 0) {
            //     if (isset($params["tabs"]->tabs)) {
            //         $tabItems = $params["tabs"]->tabs[0]->items;
            //         foreach ($tabItems as $Entry) {
            //             $split = explode("/", $Entry);
            //             if (count($split) > 0) {
            //                 $name = $split[0];
            //                 $class = $split[1];
            //                 $label = $split[2];
            //                 $dataType = $split[3];
            //                 $type = $split[4];

            //                 $newTabs[] = array(
            //                     "name" => $name,
            //                     "class" => $class,
            //                     "dataType" => $dataType,
            //                     "title" => $label,
            //                     "type" => $type
            //                 );
            //             }
            //         }
            //     } else {
            //         foreach ($params["tabs"] as $Entry) {
            //             $split = explode("/", $Entry);
            //             if (count($split) > 0) {
            //                 $name = $split[0];
            //                 $class = $split[1];
            //                 $label = $split[2];
            //                 $dataType = $split[3];
            //                 $type = $split[4];

            //                 $newTabs[] = array("name" => $name,
            //                     "class" => $class,
            //                     "dataType" => $dataType,
            //                     "title" => $label,
            //                     "type" => $type
            //                 );
            //             }
            //         }
            //     }
            // }

            if (empty($params['showdoctype'])) {
                $params['showdoctype'] = array();
            } else {
                if (is_array($params['showdoctype']) && count($params['showdoctype']) > 0) {
                    if (is_array($params['showdoctype'][0])) {
                        $params['showdoctype'] = $params['showdoctype'][0];
                    }
                }
            }

            $Columns = array(
                "Column1" => $newCol1,
                "Column2" => $newCol2,
                "customFields" => $customFields,
                "lucene_query" => $params["lucene_query"],
                "Tabs" => $params["tabs"]
            );

            $em = $this->getDoctrine()->getManager();
            $q = $em->getRepository('IfrescoClientBundle:SearchTemplate')->findOneBy(array(
                'is_default_view' => 1
            ));

            if ($params["edit"] == null || empty($params["edit"]) || $params["edit"] == "null") {
                $Template = new SearchTemplate();
                if (!$q) {
                    $Template->setIsDefaultView(true);
                } else {
                    $Template->setIsDefaultView(false);
                }
            } else {
                $Template = $em->getRepository('IfrescoClientBundle:SearchTemplate')->find($params["edit"]);
            }

            $Template->setName($params['name']);
            $Template->setColumnSetId($params['columnset']);
            $Template->setSavedSearchId($params['savedsearch']);
            $Template->setContentType($params['contenttype']);
            $Template->setShowDoctype(json_encode($params['showdoctype']));
            $Template->setIsMulticolumn(($params['multiColumns'] == "true" ? 1 : 0));
            $Template->setIsFullTextChild(($params['fulltextChild'] == "true" ? 1 : 0));
            $Template->setIsFullTextChildOverwrite(($params['fulltextChildOverwrite'] == "true" ? 1 : 0));
            $Template->setJsonData(json_encode($Columns));
            $em->persist($Template);
            $em->flush();

            $returnArr["editId"] = $Template->getId();
            $returnArr["success"] = true;
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function deleteSearchTemplatesAction(Request $request)
    {
        $ids = $request->get('ids');
        $data = array("success" => false);
        if (!empty($ids)) {
            $in = "";
            $params = array();

            foreach ($ids as $key => $id) {
                $in = strlen($in) > 0 ? $in . ", ?$key" : "?$key";
                $params[$key] = $id;
            }

            $em = $this->getDoctrine()->getManager();
            $em->createQueryBuilder()->delete()
                ->from('IfrescoClientBundle:SearchTemplate', 'c')
                ->where("c.id IN($in)")
                ->setParameters($params)->getQuery()->execute();

            $data["success"] = true;
        }

        return new JsonResponse($data);
    }

    public function getTemplateMetadataAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }
        $class = $request->get('class');
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $RestDictionary = new RESTDictionary($repository, $spacesStore, $session);
        $class = str_replace(":", "_", $class);
        $ClassProperties = $RestDictionary->GetClassProperties($class);
        $ClassAssociation = $RestDictionary->GetClassAssociations($class);

        $array = array(
            "Properties"=>array(),
            "Associations"=>array()
        );
        if ($ClassProperties != null) {
            $array["Properties"] = $ClassProperties;
        }

        if ($ClassAssociation != null) {
            $array["Associations"] = $ClassAssociation;
        }

        $response = new JsonResponse($array);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function getQueryFieldsAction(Request $request)
    {
        $queryStr = $request->get('queryStr');
        $dataSourceId = $request->get('datasource');

        /**
         * @var User $user
         * @var DataSource $dataSource
         */
        $user = $this->get('security.context')->getToken();
        if (!$user->isAdmin()) {
            $this->forward('default', 'module');
        }

        $em = $this->getDoctrine()->getManager();
        $dataSource = $em->getRepository('IfrescoClientBundle:DataSource')->find($dataSourceId);

        $pdo = $this->pdoConnect(
            $dataSource->getUsername(),
            $dataSource->getPassword(),
            $dataSource->getDatabaseName(),
            $dataSource->getHost(),
            $dataSource->getType()
        );

        $success = false;
        $colRec = array();
        if($pdo) {
            try {
                $result = $pdo->query('SELECT ' . $queryStr . ' LIMIT 1');
                if($result) {
                    foreach($result->fetchAll(\PDO::FETCH_ASSOC) as $table) {
                        $colRec = $table;
                    }
                }
                $success = true;
            } catch(\Exception $e) {
                $msg = $e->getMessage();
            }
        }

        $cols = array();
        if(count($colRec)) {
            foreach($colRec as $k=>$v) {
                $cols[] = $k;
            }
        }

        $response = new JsonResponse(array(
            'success' => $success,
            'cols' => $cols
        ));
        $response->headers->set('Content-Type','application/json; charset=utf-8');
        $response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
        $response->headers->set('Pragma','no-cache');
        return $response;
    }

    public function markColumnSetAsDefaultAction(Request $request)
    {
        $id = $request->get('id');
        $data = array("success" => false);
        if (!empty($id)) {
            $em = $this->getDoctrine()->getManager();
            $em->createQueryBuilder()
                ->update('IfrescoClientBundle:SearchColumnSet', 's')
                ->set('s.is_default_set', 0)
                ->where('s.is_default_set = :defaultset')
                ->setParameter('defaultset', 1)->getQuery()
                ->execute()
            ;

            $em->createQueryBuilder()
                ->update('IfrescoClientBundle:SearchColumnSet', 's')
                ->set('s.is_default_set', 1)
                ->where('s.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->execute()
            ;

            $data["success"] = true;
        }

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function markTemplateAsDefaultAction(Request $request)
    {
        $id = $request->get('id');
        $data = array("success" => false);
        if (!empty($id)) {
            $em = $this->getDoctrine()->getManager();
            $em->createQueryBuilder()
                ->update('IfrescoClientBundle:SearchTemplate', 's')
                ->set('s.is_default_view', 0)
                ->where('s.is_default_view = :defaultview')
                ->setParameter('defaultview', 1)
                ->getQuery()
                ->execute()
            ;

            $em->createQueryBuilder()
                ->update('IfrescoClientBundle:SearchTemplate', 's')
                ->set('s.is_default_view', 1)
                ->where('s.id = :id')
                ->setParameter('id', $id)->getQuery()->execute();

            $data["success"] = true;
        }

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function ocrStatusAction(Request $request)
    {
    	$data = array("success" => false);
    	 
    	try {
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$isAdmin = $user->isAdmin();
	    	if ($isAdmin == false)
	    		$this->forward('default', 'module');
	    
	    	$RESTocr = new RESTocr($repository, null, $session);
	    
	    	$conf = $RESTocr->fetchConfig();
	    
	    	if ($conf === null) {
	    		throw new \Exception($this->get('translator')->trans("AutoOCR is not enabled on Alfresco side. Enable it first."));
	    	}
	    
	    	$data["data"] = $conf;
	    	$data["status"] = $RESTocr->getStatus();
	    	$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["errorMsg"] = $e->getMessage();
    		$data["success"] = false;
    	}
    	
    	$response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function ocrConnectionAction(Request $request)
    {
    	$data = array("success" => false);
    	
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$isAdmin = $user->isAdmin();
    		if ($isAdmin == false)
    			$this->forward('default', 'module');
    		
	    	$RESTocr = new RESTocr($repository, null, $session);
	    	$conf = $RESTocr->fetchConfig();
	    
	    	if ($conf === null) {
	    		throw new \Exception($this->get('translator')->trans("AutoOCR is not enabled on Alfresco side. Enable it first."));
	    	}
	    	
	    	$data["data"] = $conf;
	    	$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["errorMsg"] = $e->getMessage();
    		$data["success"] = false;
    	}
    	
    	$response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    

    public function ocrTransformersAction(Request $request)
    {
    	$data = array("success" => false);
    	
    	try {
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$isAdmin = $user->isAdmin();
	    	if ($isAdmin == false)
	    		$this->forward('default', 'module');
	    
	    	$RESTocr = new RESTocr($repository, null, $session);
	    
	    	$conf = $RESTocr->fetchConfig();
	    
	    	if ($conf === null) {
	    		throw new \Exception($this->get('translator')->trans("AutoOCR is not enabled on Alfresco side. Enable it first."));
	    	}
	    
	    	$transformations = $RESTocr->fetchConfig()->transformations;
	    	//$settings = $RESTocr->fetchSettings()->settings;
	    
	    	if(!is_array($transformations))
	    		$transformations = array($transformations);
	    	
	    	$data["transformers"] = $transformations;
	    	//$data["settings"] = $settings;
	    	$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["errorMsg"] = $e->getMessage();
    		$data["success"] = false;
    	}
    
    	$response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function ocrRuntimeTransformersAction(Request $request)
    {
    	$data = array("success" => false);
    	 
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$isAdmin = $user->isAdmin();
    		if ($isAdmin == false)
    			$this->forward('default', 'module');
    	  
    		$RESTocr = new RESTocr($repository, null, $session);
    	  
    		$conf = $RESTocr->fetchConfig();
    	  
    		if ($conf === null) {
    			throw new \Exception($this->get('translator')->trans("AutoOCR is not enabled on Alfresco side. Enable it first."));
    		}
    	  
    		$transformations = array();
    		if (isset($conf->runtimeTransformations)) {
    			$transformations = $conf->runtimeTransformations;
    		}

    		if(!is_array($transformations))
    			$transformations = array($transformations);
    
    		$data["transformers"] = $transformations;
    		$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["errorMsg"] = $e->getMessage();
    		$data["success"] = false;
    	}
    
    	$response = new JsonResponse($data);
    	$response->headers->set('Content-Type', 'application/json; charset=utf-8');
    	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    	$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
    	$response->headers->set('Pragma', 'no-cache');
    	return $response;
    }
    
    public function ocrTransformerMimetypesAction(Request $request)
    {
    	$data = array("success" => false);
    
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$isAdmin = $user->isAdmin();
    		if ($isAdmin == false)
    			$this->forward('default', 'module');
    		 
    		$RESTocr = new RESTocr($repository, null, $session);
    		 
    		$conf = $RESTocr->fetchConfig();
    		 
    		if ($conf === null) {
    			throw new \Exception($this->get('translator')->trans("AutoOCR is not enabled on Alfresco side. Enable it first."));
    		}
    		 
    		$mimetypes = $RESTocr->getAvailableTransformations()->mimetypes;
    		
    		if(!is_array($mimetypes))
    			$mimetypes = array($mimetypes);
    
    		$data["mimetypes"] = $mimetypes;
    		$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["errorMsg"] = $e->getMessage();
    		$data["success"] = false;
    	}
    
    	$response = new JsonResponse($data);
    	$response->headers->set('Content-Type', 'application/json; charset=utf-8');
    	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    	$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
    	$response->headers->set('Pragma', 'no-cache');
    	return $response;
    }
    
    public function ocrTransformerSettingsAction(Request $request)
    {
    	$data = array("success" => false);
    	 
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$isAdmin = $user->isAdmin();
    		if ($isAdmin == false)
    			$this->forward('default', 'module');
    	  
    		$RESTocr = new RESTocr($repository, null, $session);
    	  
    		$conf = $RESTocr->fetchConfig();
    	  
    		if ($conf === null) {
    			throw new \Exception($this->get('translator')->trans("AutoOCR is not enabled on Alfresco side. Enable it first."));
    		}
    	  
    		$settings = $RESTocr->fetchSettings()->settings;

    		$data["settings"] = $settings;
    		$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["errorMsg"] = $e->getMessage();
    		$data["success"] = false;
    	}
    
    	$response = new JsonResponse($data);
    	$response->headers->set('Content-Type', 'application/json; charset=utf-8');
    	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    	$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
    	$response->headers->set('Pragma', 'no-cache');
    	return $response;
    }
    
    public function testOcrConnectionAction(Request $request)
    {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$isAdmin = $user->isAdmin();
    	if ($isAdmin == false)
    		$this->forward('default', 'module');
    
    	$dataGet = $request->get('data');
    
    	$data = array();
    	if (!empty($dataGet)) {
    		$data = json_decode($dataGet);
    	}
    
    	if ($dataGet == "{}") {
    		$data = array();
    	}
    
    	$RESTocr = new RESTocr($repository, null, $session);
    
    	$return = $RESTocr->testConnection($data);
    
    	$response = new JsonResponse($return);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function testOcrAPIKeyAction(Request $request)
    {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$isAdmin = $user->isAdmin();
    	if ($isAdmin == false)
    		$this->forward('default', 'module');
    
    	$dataGet = $request->get('data');
    
    	$data = array();
    	if (!empty($dataGet)) {
    		$data = json_decode($dataGet);
    	}
    
    	if ($dataGet == "{}") {
    		$data = array();
    	}
    
    	$RESTocr = new RESTocr($repository, null, $session);
    
    	$return = $RESTocr->testAPI($data);
    
    	$response = new JsonResponse($return);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function ocrJobsAction(Request $request)
    {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$isAdmin = $user->isAdmin();
    	if ($isAdmin == false)
    		$this->forward('default', 'module');
    
    	$jobType = $request->get('jobType', 'ALL');
    
    	$RESTocr = new RESTocr($repository, null, $session);
    
    	$return = $RESTocr->jobsList($jobType)->resultSet;
    
    	$response = new JsonResponse($return);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function saveOcrSettingsAction(Request $request) {
    
    	$returnArr = array("success"=>true);
    
    	try {
    
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$isAdmin = $user->isAdmin();
    		if ($isAdmin == false)
    			$this->forward('default', 'module');
    
    		$RESTocr = new RESTocr($repository, null, $session);
    
    		$conf = $RESTocr->fetchConfig();
    
    		$dataGet = $request->get('data');
    
    		$data = array();
    		if (!empty($dataGet)) {
    			$data = json_decode(($dataGet));
    		}
    
    		if ($dataGet == "{}") {
    			$data = array();
    		}
    
    		if(isset($data->enabled)) {
    			$conf->enabled = $data->enabled == 'true';
    		}
    
    		if(isset($data->endpoint)) {
    			$conf->endpoint = $data->endpoint;
    		}
    
    		if(isset($data->connectiontimeout)) {
    			$conf->connectiontimeout = (int) $data->connectiontimeout;
    		}
    
    		if(isset($data->username)) {
    			$conf->username = $data->username;
    		}
    
    		if(isset($data->password)) {
    			$conf->password = $data->password;
    		}
    
    		if(isset($data->timeout)) {
    			$conf->timeout = (int) $data->timeout;
    		}
    
    		if(isset($data->sleeptime)) {
    			$conf->sleeptime = (int) $data->sleeptime;
    		}
    
    		if(isset($data->sleeptime)) {
    			$conf->sleeptime = (int) $data->sleeptime;
    		}
    
    		if(isset($data->apiKey)) {
    			$conf->apiKey = (int) $data->apiKey;
    		}
    
    		if(isset($data->transformations)) {
    			$conf->transformations = $data->transformations;
    		}
    		
    		if(isset($data->runtimeTransformations)) {
    			$conf->runtimeTransformations = $data->runtimeTransformations;
    		}
    
    
    		$RESTocr->saveConfig($conf);
    	}
    	catch(\Exception $e) {
    		$returnArr['success'] = false;
    		$returnArr['message'] = $e->getMessage();
    	}

    	$response = new JsonResponse($returnArr);
    	$response->headers->set('Content-Type', 'application/json; charset=utf-8');
    	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    	$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
    	$response->headers->set('Pragma', 'no-cache');
    	return $response;
    }
    
    public function tagManagerAction(Request $request)
    {
    	$return = array("success"=>true);
    	
    	try {
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$isAdmin = $user->isAdmin();
	    	if ($isAdmin == false)
	    		$this->forward('default', 'module');
	    
	    	$filter = $request->get('filter', '');
	    	
			$RestTags = new RESTTags($repository, null, $session);
			$Tags = $RestTags->GetAllTags($filter,true);
			$items = array();
			if (isset($Tags->data->items))
				$items = $Tags->data->items;
		
			$return['items'] = $items;
			$return['success'] = true;
		}
		catch(\Exception $e) {
			$return['success'] = false;
			$return['message'] = $e->getMessage();
		}
    
    	$response = new JsonResponse($return);
    	$response->headers->set('Content-Type', 'application/json; charset=utf-8');
    	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    	$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
    	$response->headers->set('Pragma', 'no-cache');
    	return $response;
    }
    
    public function tagManagerEditAction(Request $request)
    {
    	$return = array("success"=>true);
    	 
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$isAdmin = $user->isAdmin();
    		if ($isAdmin == false)
    			$this->forward('default', 'module');
    	  
    		$name = $request->get('name');
    		$change = $request->get('change');
    		
    		$RestTags = new RESTTags($repository, null, $session);
    		$RestTags->EditTag($name, $change);

    		$return['success'] = true;
    	}
    	catch(\Exception $e) {
    		$return['success'] = false;
    		$return['message'] = $e->getMessage();
    	}
    
    	$response = new JsonResponse($return);
    	$response->headers->set('Content-Type', 'application/json; charset=utf-8');
    	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    	$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
    	$response->headers->set('Pragma', 'no-cache');
    	return $response;
    }
    
    public function tagManagerDeleteAction(Request $request)
    {
    	$return = array("success"=>true);
    
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$isAdmin = $user->isAdmin();
    		if ($isAdmin == false)
    			$this->forward('default', 'module');
    		 
    		$name = $request->get('name');
    
    		$RestTags = new RESTTags($repository, null, $session);
    		$RestTags->DeleteTag($name);
    
    		$return['success'] = true;
    	}
    	catch(\Exception $e) {
    		$return['success'] = false;
    		$return['message'] = $e->getMessage();
    	}
    
    	$response = new JsonResponse($return);
    	$response->headers->set('Content-Type', 'application/json; charset=utf-8');
    	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    	$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
    	$response->headers->set('Pragma', 'no-cache');
    	return $response;
    }
    
    public function getCurrencyFieldsAction()
    {
    	$user = $this->get('security.context')->getToken();
    	if (!$user->isAdmin()) $this->forward('default', 'module');
    
    	$result = array(
    			'success' => true,
    			'data' => array('foundSettings' => false, 'fields' => array())
    	);
    
    	$em = $this->getDoctrine()->getManager();

    	$quickSearchSetting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
    			'key_string' => 'CurrencyFields'
    	));
    
    	if ($quickSearchSetting != null) {
    		$jsonData = json_decode($quickSearchSetting->getValueString());
    		$result['data']['fields'] = $jsonData ? $jsonData : array();
    		$result['data']['foundSettings'] = true;
    	} else {
    		$result['data']['fields'] = array();
    	}

    	return new JsonResponse($result);
    }
    
    public function saveCurrencyFieldsAction(Request $request)
    {
    	$user = $this->get('security.context')->getToken();
    	if (!$user->isAdmin()) $this->forward('default', 'module');
    	 
    	$returnArr = array("success" => false);
    	$data = $request->get('data');
    
    	if (!empty($data)) {
    		$data = json_decode($data);
    		$params = array(
    				'fields' => $data->fields
    		);
    	} else {
    		$params = array(
    				'fields' => $request->get('fields')
    		);
    	}
    
    	try {
    		if (empty($params["fields"])) {
    			$params["fields"] = array();
    		}
    
    		$fields = $params["fields"];
    
    		$em = $this->getDoctrine()->getManager();
    		$setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
    				'key_string' => 'CurrencyFields'
    		));
    
    		if (!$setting) {
    			$setting = new Setting();
    		}
    
    		$setting->setKeyString("CurrencyFields");
    		$setting->setValueString(json_encode($fields));
    		$em->persist($setting);
    		$em->flush();
    
    		$returnArr["success"] = true;
    	} catch (\Exception $e) {
    		$returnArr["errorMsg"] = $e->getMessage();
    		$returnArr["success"] = false;
    	}
    
    	$response = new JsonResponse($returnArr);
    	return $response;
    }

    private function pdoConnect($username, $password, $databaseName, $host='localhost', $type='mysql')
    {
        try {
            $conn = new \PDO(
                "$type:host=$host;dbname=$databaseName;",
                $username,
                $password,
                array()
            );

            return $conn;
        } catch (\PDOException $e) {
            return false;
        }
    }

    private function arraySort($array, $on, $order=SORT_ASC) {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    private function getAllowedPrefs()
    {
        $allowed = array('cm');
        $em = $this->getDoctrine()->getManager();
        /**
         * @var Setting $setting
         */
        $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'allowedPrefs'
        ));

        if ($setting) {
            $allowed = json_decode($setting->getValueString(), true);

            if(count($allowed) == 0) {
                $allowed = array('cm');
            }
        }

        return $allowed;
    }

    private function filterProps($props, $allowed)
    {
        if($allowed) {
            $newProps = array();
            foreach ($props as $property) {
                if(preg_match('/^([^:]+):/', $property->name, $pref)) {
                    if(in_array($pref[1], $allowed)) {
                        $newProps[] = $property;
                    }
                }
            }

            $props = $newProps;
        }

        return $props;
    }

    private function sortByOld($field, &$arr, $sorting = SORT_ASC, $caseInsensitive = true)
    {
        if(is_array($arr) && (count($arr) > 0)){
            $strCmpFn = $caseInsensitive ? "strnatcasecmp" : "strnatcmp";

            if($sorting==SORT_ASC){
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return ' . $strCmpFn . '($a->' . $field . ', $b->' . $field . ');
                    }else if(is_array($a) && is_array($b)){
                        return ' . $strCmpFn . '($a["' . $field . '"], $b["' . $field . '"]);
                    }else return 0;
                ');
            } else {
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return ' . $strCmpFn . '($b->' . $field . ', $a->' . $field . ');
                    }else if(is_array($a) && is_array($b)){
                        return ' . $strCmpFn . '($b["' . $field . '"], $a["' . $field . '"]);
                    }else return 0;
                ');
            }

            usort($arr, $fn);
            return true;
        }

        return false;
    }

    private function readRecursiveCategory($breadCrumb, $defaultValues, $nocache=false) {
        $searchCat = str_replace(" ", "%20", $breadCrumb);
        $breadCrumb = urldecode($breadCrumb) . "/";

        if ($nocache == true) {
            $categories = $this->_restCategories->GetCategories($searchCat);
        } else {
            $categories = CategoryCache::getInstance($this->get('security.context')->getToken())->getCachedCategories($searchCat);
        }

        $array = array(
            "items" => array(),
            "found" => false
        );

        $iconClasses = array(
            "tag_green",
            "tag_orange",
            "tag_pink",
            "tag_purple",
            "tag_yellow"
        );

        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis", $breadCrumb, $match);
            $iconCls = $count > 4 ? 'tag_red' : $iconClasses[$count];

            foreach ($categories->items as $item) {
                $nodeId = str_replace("workspace://SpacesStore/", "", $item->nodeRef);
                $checked = false;
                if (in_array($item->nodeRef, $defaultValues)) {
                    $checked = true;
                    $array["found"] = true;
                }

                $arrVal = array(
                    "cls" => "folder",
                    "id" => str_replace(" ", "%20", $breadCrumb . $item->name),
                    "nodeId" => $nodeId,
                    "checked" => $checked,
                    "expanded" => $checked,
                    "leaf" => ($item->hasChildren == true ? false : true),
                    "iconCls" => 'category_' . $iconCls,
                    "text" => $item->name
                );

                if ($item->hasChildren == true) {
                    $children = $this->readRecursiveCategory($breadCrumb . $item->name, $defaultValues, $nocache);
                    $arrVal["children"] = $children["items"];
                    if ($children["found"] == true) {
                        $arrVal["expanded"] = true;
                        $array["found"] = true;
                    }
                }

                $array["items"][] = $arrVal;
            }
        }

        return $array;
    }

    protected function _export_content_model_tpls(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('c')
            ->from('IfrescoClientBundle:ContentModelTemplate', 'c')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_content_model_lookups(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('l')
            ->from('IfrescoClientBundle:Lookup', 'l')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_search_tpls(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:SearchTemplate', 's')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_quick_search(EntityManager $em) {
        $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'QuickSearch'
        ));

        return $setting ? $setting->getValueString() : '';
    }

    protected function _export_column_set(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:SearchColumnSet', 's')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_data_sources(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:DataSource', 's')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_system_settings(EntityManager $em) {
        $params = $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:Setting', 's')
            ->where('s.key_string in (
            \'CategoryCache\',
            \'Renderer\',
            \'DefaultTab\',
            \'DefaultNav\',
            \'NodeCache\',
            \'DateFormat\',
            \'TimeFormat\',
            \'ParentNodeMeta\',
            \'ParentNodeMetaLevel\',
            \'ParentMetaDocumentOnly\',
            \'CSVExport\',
            \'PDFExport\',
            \'openInAlfresco\',
            \'MetaOnTreeFolder\',
            \'TabTitleLength\',
            \'UserLookupLabel\',
            \'logoURL\',
            \'treeRootFolder\',
            \'thumbnailHover\',
            \'shareEnabled\',
            \'uploadAllowedTypes\'
            )')
            ->getQuery()
            ->getResult();
        $result =array();
        /**
         * @var Setting $option
         */
        foreach($params as $option) {
            $result[$option->getKeyString()] = $option->getValueString();
        }
        return $result;
    }

    protected function _export_property_filter(EntityManager $em) {
        $params = $em->getRepository('IfrescoClientBundle:Setting')->findBy(array(
            'key_string' => 'allowedPrefs'
        ));
        $result =array();
        /**
         * @var Setting $option
         */
        foreach($params as $option) {
            $result[$option->getKeyString()] = $option->getValueString();
        }
        return $result;
    }

    protected function _export_dropbox_settings(EntityManager $em) {
        $params = $em->getRepository('IfrescoClientBundle:Setting')->findBy(array(
            'key_string' => 'dropboxApiKey'
        ));
        $result =array();
        /**
         * @var Setting $option
         */
        foreach($params as $option) {
            $result[$option->getKeyString()] = $option->getValueString();
        }
        return $result;
    }

    protected function _export_export_fields(EntityManager $em) {
        $params = $em->getRepository('IfrescoClientBundle:Setting')->findBy(array(
            'key_string' => 'ExportFields'
        ));
        $result =array();
        /**
         * @var Setting $option
         */
        foreach($params as $option) {
            $result[$option->getKeyString()] = $option->getValueString();
        }
        return $result;
    }

    protected function _export_ocr_settings(EntityManager $em) {
        $params = $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:Setting', 's')
            ->where('s.key_string in (\'AutoOCREndpoint\', \'AutoOCRProfile\', \'OCRBehavior\', \'OCREnabled\', \'OCROnUpload\')')
            ->getQuery()
            ->getResult();
        $result =array();
        /**
         * @var Setting $option
         */
        foreach($params as $option) {
            $result[$option->getKeyString()] = $option->getValueString();
        }
        return $result;
    }

    protected function _export_email_settings(EntityManager $em) {
        $in = '(\'SMTP_HOST\', \'SMTP_PORT\', \'SMTP_AUTH\', \'SMTP_USERNAME\', \'SMTP_PASSWORD\', \'FROM_EMAIL\', \'FROM_NAME\')';
        $params = $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:Setting', 's')
            ->where('s.key_string in ' .$in)
            ->getQuery()
            ->getResult();
        $result =array();
        /**
         * @var Setting $option
         */
        foreach($params as $option) {
            $result[$option->getKeyString()] = $option->getValueString();
        }
        return $result;
    }

    protected function _export_aspects(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:AllowedAspect', 's')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_content_types(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:AllowedType', 's')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_namespace_mapping(EntityManager $em) {
        return $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:NamespaceMapping', 's')
            ->getQuery()
            ->getScalarResult();
    }

    protected function _export_online_editing(EntityManager $em) {
        $params = $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:Setting', 's')
            ->where('s.key_string in (\'OnlineEditing\', \'OnlineEditingZohoApiKey\', \'OnlineEditingZohoSkey\')')
            ->getQuery()
            ->getResult();
        $result =array();
        /**
         * @var Setting $option
         */
        foreach($params as $option) {
            $result[$option->getKeyString()] = $option->getValueString();
        }
        return $result;
    }

    protected function _import_content_model_tpls(EntityManager $em, $data) {
        $em->createQueryBuilder()->delete()->from('IfrescoClientBundle:ContentModelTemplate', 'c')->getQuery()->execute();

        foreach($data as $item) {
            $content = new ContentModelTemplate();
            $content->setClass($item['c_class']);
            $content->setIsMulticolumn($item['c_is_multicolumn']);
            $content->setAspectView($item['c_aspect_view']);
            $content->setJsonData($item['c_json_data']);
            $em->persist($content);
        }
        $em->flush();

        return true;
    }

    protected function _import_content_model_lookups(EntityManager $em, $data) {
        $em->createQueryBuilder()->delete()->from('IfrescoClientBundle:Lookup', 'l')->getQuery()->execute();

        foreach($data as $item) {
            $content = new Lookup();
            $content->setField($item['l_field']);
            $content->setType($item['l_type']);
            $content->setFieldData($item['l_field_data']);
            $content->setIsSingle($item['l_is_single']);
            $content->setUseCache($item['l_use_cache']);
            $content->setParams($item['l_params']);
            $content->setApplyTo($item['l_apply_to']);
            $em->persist($content);
        }
        $em->flush();

        return true;
    }

    protected function _import_search_tpls(EntityManager $em, $data) {
        $toLeave = array();

        foreach($data as $item) {
            $content = $em->getRepository('IfrescoClientBundle:SearchTemplate')->find($item['s_id']);
            if(!$content)
                $content = new SearchTemplate();

            $content->setName($item['s_name']);
            $content->setIsDefaultView($item['s_is_default_view']);
            $content->setIsMulticolumn($item['s_is_multicolumn']);
            $content->setColumnSetId($item['s_column_set_id']);
            $content->setShowDoctype($item['s_show_doctype']);
            $content->setJsonData($item['s_json_data']);
            $content->setIsFullTextChild($item['s_is_full_text_child']);
            $content->setIsFullTextChildOverwrite($item['s_is_full_text_child_overwrite']);
            $content->setSavedSearchId($item['s_saved_search_id']);
            $em->persist($content);
            $em->flush();
            $toLeave[] = $content->getId();
        }

        if(count($toLeave)) {
            $query = $em->createQueryBuilder()
                ->delete()
                ->from('IfrescoClientBundle:SearchTemplate', 's')
                ->where('s.id NOT IN (' . implode(',', $toLeave) . ')');
            $query->getQuery()->execute();
        }

        return true;
    }

    protected function _import_quick_search(EntityManager $em, $data) {
        $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => 'QuickSearch'));
        if(!$setting){
            $setting = new Setting();
            $setting->setKeyString('QuickSearch');
        }
        $setting->setValueString($data);
        $em->persist($setting);
        $em->flush();

        return true;
    }

    protected function _import_column_set(EntityManager $em, $data) {
        $toLeave = array();

        foreach($data as $item) {
            $content = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->find($item['s_id']);
            if(!$content) {
                $content = new SearchColumnSet();
            }

            $content->setIsDefaultSet($item['s_is_default_set']);
            if (isset($item['s_hideInMenu']))
            	$content->setHideInMenu($item['s_hideInMenu']);
            else 
            	$content->setHideInMenu(false);
            $content->setName($item['s_name']);
            $content->setJsonFields($item['s_json_fields']);
            $em->persist($content);
            $em->flush();
            $toLeave[] = $content->getId();
        }

        if(count($toLeave)) {
            $em->createQueryBuilder()
                ->delete()
                ->from('IfrescoClientBundle:SearchColumnSet', 's')
                ->where('s.id NOT IN (' . implode(',', $toLeave) . ')')
                ->getQuery()
                ->execute();
        }

        return true;
    }

    protected function _import_data_sources(EntityManager $em, $data) {
        $toLeave = array();

        foreach($data as $item) {
            $content = $em->getRepository('IfrescoClientBundle:DataSource')->find($item['s_id']);
            if(!$content) {
                $content = new DataSource();
            }

            $content->setName($item['s_name']);
            $content->setType($item['s_type']);
            $content->setHost($item['s_host']);
            $content->setUsername($item['s_username']);
            $content->setDatabaseName($item['s_database_name']);
            $content->setPassword($item['s_password']);
            $content->setPort($item['s_port']);
            $em->persist($content);
            $em->flush();
            $toLeave[] = $content->getId();
        }

        if(count($toLeave)) {
            $em->createQueryBuilder()
                ->delete()
                ->from('IfrescoClientBundle:DataSource', 's')
                ->where('s.id NOT IN (' . implode(',', $toLeave) . ')')
                ->getQuery()
                ->execute();
        }

        return true;
    }

    protected function _import_system_settings(EntityManager $em, $data) {
        foreach($data as $key => $val) {
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => $key));
            if(!$setting){
                $setting = new Setting();
                $setting->setKeyString($key);
            }
            $setting->setValueString($val);
            $em->persist($setting);
        }
        $em->flush();

        return true;
    }

    protected function _import_property_filter(EntityManager $em, $data) {
        foreach($data as $key => $val) {
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => $key));
            if(!$setting){
                $setting = new Setting();
                $setting->setKeyString($key);
            }
            $setting->setValueString($val);
            $em->persist($setting);
        }
        $em->flush();

        return true;
    }

    protected function _import_dropbox_settings(EntityManager $em, $data) {
        foreach($data as $key => $val) {
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => $key));
            if(!$setting){
                $setting = new Setting();
                $setting->setKeyString($key);
            }
            $setting->setValueString($val);
            $em->persist($setting);
        }
        $em->flush();

        return true;
    }

    protected function _import_export_fields(EntityManager $em, $data) {
        foreach($data as $key => $val) {
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => $key));
            if(!$setting){
                $setting = new Setting();
                $setting->setKeyString($key);
            }
            $setting->setValueString($val);
            $em->persist($setting);
        }
        $em->flush();

        return true;
    }

    protected function _import_ocr_settings(EntityManager $em, $data) {
        foreach($data as $key => $val) {
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => $key));
            if(!$setting){
                $setting = new Setting();
                $setting->setKeyString($key);
            }
            $setting->setValueString($val);
            $em->persist($setting);
        }
        $em->flush();

        return true;
    }

    protected function _import_email_settings(EntityManager $em, $data) {
        foreach($data as $key=>$val) {
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => $key));
            if(!$setting){
                $setting = new Setting();
                $setting->setKeyString($key);
            }
            $setting->setValueString($val);
            $em->persist($setting);
        }
        $em->flush();

        return true;
    }

    protected function _import_aspects(EntityManager $em, $data) {
        $em->createQueryBuilder()
            ->delete()
            ->from('IfrescoClientBundle:AllowedAspect', 's')
            ->getQuery()
            ->execute();

        foreach($data as $item) {
            $content = new AllowedAspect();
            $content->setName($item['s_name']);
            $em->persist($content);
        }
        $em->flush();

        return true;
    }

    protected function _import_content_types(EntityManager $em, $data) {
        $em->createQueryBuilder()
            ->delete()
            ->from('IfrescoClientBundle:AllowedType', 's')
            ->getQuery()
            ->execute();

        foreach($data as $item) {
            $content = new AllowedType();
            $content->setName($item['s_name']);
            $em->persist($content);
        }
        $em->flush();

        return true;
    }

    protected function _import_namespace_mapping(EntityManager $em, $data) {
        $em->createQueryBuilder()
            ->delete()
            ->from('IfrescoClientBundle:NamespaceMapping', 's')
            ->getQuery()
            ->execute();

        foreach($data as $item) {
            $content = new Namespacemapping();
            $content->setNamespace($item['s_namespace']);
            $content->setPrefix($item['s_prefix']);
            $em->persist($content);
            $em->flush();
        }

        return true;
    }

    protected function _import_online_editing(EntityManager $em, $data) {
        foreach($data as $key => $val) {
            $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => $key));
            if(!$setting){
                $setting = new Setting();
                $setting->setKeyString($key);
            }
            $setting->setValueString($val);
            $em->persist($setting);
        }
        $em->flush();

        return true;
    }

    private function addJob(EntityManager $em, $type, $status, $data="") {
        $Job = new CurrentJob();
        $Job->setType($type);
        $Job->setStatus($status);
        $Job->setJsonData(json_encode($data));
        $em->persist($Job);
        $em->flush();
        return $Job->getId();
    }
}
