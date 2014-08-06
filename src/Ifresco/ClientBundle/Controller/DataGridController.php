<?php

namespace Ifresco\ClientBundle\Controller;
use Doctrine\ORM\NoResultException;
use Exception;
use Ifresco\ClientBundle\Component\Alfresco\ContentData;
use Ifresco\ClientBundle\Component\Alfresco\Lib\ISO9075\ISO9075Mapper;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTContent;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDictionary;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTifrescoScripts;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Entity\SearchTemplate;
use Ifresco\ClientBundle\Entity\SearchColumnSet;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ifresco\ClientBundle\Component\Alfresco\Lib\PDFMerge;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTTransport;

class DataGridController extends Controller
{
    private $repository = null;
    private $session = null;
    private $spacesStore = null;
    private $companyHome = null;
    private $ifrescoScripts = null;
    private $start = null;
    private $limit = null;
    private $page = null;
    private $sort = null;
    private $sorting = null;
    private $addThumbs = false;
    private $metaRenderer = null;
    private $setNodeOnParse = false;

    public function indexAction(Request $request)
    {
        $view_vars = array(
            'isClipBoard' => '',
            'addContainer' => '',
            'nextContainer' => '',
            'DetailUrl' => '',
            'CompanyHomeUrl' => '',
            'ShareUrl' => '',
            'ShareSpaceUrl' => '',
            'DefaultSort' => '',
            'DefaultSortDir' => '',
            'fields' => '',
            'columns' => '',
        	'columnSetName' => '',
            'containerName' => '',
            'zipArchiveExists' => class_exists('\ZipArchive'),
            'DefaultTab' => '',
            'DateFormat' => '',
            'TimeFormat' => '',
            'Columnsets' => '',
        	'folder_path' => '',
        	'name' => ''
        );


        $em = $this->getDoctrine()->getManager();
        
        $nodeId = $request->get('nodeId');
        $clipboard = $request->get('clipboard');
        if ($clipboard == true)
            $view_vars['isClipBoard'] = true;
        else
            $view_vars['isClipBoard'] = false;

        $containerName = $request->get('containerName');

        $addContainer = $request->get('addContainer');
        if (!empty($addContainer)) {
            $addContainer = substr($addContainer, 0, 5);
        }
        $view_vars['addContainer'] = $addContainer;

        if (empty($containerName)) {
            $view_vars['nextContainer'] = "container" . rand(0, 100);
        } else {
            $view_vars['nextContainer'] = $containerName;
        }

        $columnSetId = $request->get('columnSetId');
        $siteName = $request->get('siteName','');

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();

        $spacesStore = new SpacesStore($session);

        if ($nodeId == "root")
        	$node = $spacesStore->companyHome;
        else
        	$node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
        
        $view_vars['name'] = $node->cm_name;

        if (!empty($siteName))
        	$view_vars['DetailUrl'] = $node->getSiteDetailUrl($siteName);
        else
        	$view_vars['DetailUrl'] = $node->getShareSpaceUrl();
        
        $view_vars['CompanyHomeUrl'] = $spacesStore->companyHome->getSpaceUrl();
        
        
        
        

        $ColumnFieldsArray = array();
        $ColumnArray = array();

        $RepoUrl = $this->container->getParameter('alfresco_repository_url');
        $RepoUrl = preg_replace("/(https:\/\/.*?)\/.*/is", "$1", $RepoUrl);
        $RepoUrl = preg_replace("/(http:\/\/.*?)\/.*/is", "$1", $RepoUrl);

        $view_vars['QuickSharePath']= $this->get('router')->generate('ifresco_client_viewer_share_preview', array(), true) . '/';
        $view_vars['ShareFolder']= $RepoUrl.'/share/page/repository?path=';
        if (!empty($siteName)) {
        	$siteNameUrl = trim($siteName);
        	$view_vars['ShareUrl']= $RepoUrl.'/share/page/site/'.$siteNameUrl.'/document-details?nodeRef=workspace://SpacesStore/';
        	$view_vars['ShareSpaceUrl'] = $RepoUrl.'/share/page/site/'.$siteNameUrl.'/documentlibrary?path=';
        	$view_vars['ShareSpaceDetail'] = $RepoUrl.'/share/page/site/'.$siteNameUrl.'/folder-details?nodeRef=workspace://SpacesStore/';
        }
        else {
        	$view_vars['ShareUrl']= $RepoUrl.'/share/page/document-details?nodeRef=workspace://SpacesStore/';
        	$view_vars['ShareSpaceUrl'] = $RepoUrl.'/share/page/repository?path=';
        	$view_vars['ShareSpaceDetail'] = $RepoUrl.'/share/page/folder-details?nodeRef=workspace://SpacesStore/';
        }
        
        if (!empty($nodeId) && $node)
        	$view_vars["folder_path"] = str_replace('/Company Home', '', $node->getFolderPath(true, true));


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()->select('c')->from('IfrescoClientBundle:SearchColumnSet', 'c');
        /**
         * @var SearchColumnSet $columnSet
         */
        $columnSets = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->findAllVisible();
        $view_vars['columnSets'] = array();
        foreach ($columnSets as $columnSet) {
            $view_vars['columnSets'][] = array(
                'id' => $columnSet->getId(),
                'name' => $columnSet->getName()
            );
        }
        try {
            $view_vars['Columnsets'] = $query->getQuery()->getResult();
        } catch (NoResultException $e) {
            $view_vars['Columnsets'] = null;
        }
        
        $useDefault = true;
        if ($columnSetId > 0) {
        	$query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:SearchColumnSet', 's')
        	->where('s.id = :id')
        	->setParameter('id', $columnSetId);
        	
        	$searchColumnSet = $query->getQuery()->getOneOrNullResult();
        }
        else {
        	$query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:SearchColumnSet', 's')
        	->where('s.is_default_set = :default')
        	->setParameter('default', true);
        	
        	$searchColumnSet = $query->getQuery()->getOneOrNullResult();
        }
        	

        $MetadataController = $this->get("ifresco_client.metadatacontroller");
            
        if ($searchColumnSet != null) {
            	$view_vars['columnSetName'] = $searchColumnSet->getName();
                $jsonData = $searchColumnSet->getJsonFields();
                $DefaultSort = "";
                $DefaultSortDir = "";
                if (!empty($jsonData)) {
                    $jsonData = json_decode($jsonData);

                    $useDefault = false;
                    $ColumnFieldsArray["alfresco_url"] = array("type"=>"string");
                    $ColumnFieldsArray["nodeRef"] = array("type"=>"string");
                    $ColumnFieldsArray["nodeId"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_mimetype"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_type"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_name"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_name_blank"] = array("type"=>"string");

                    $ColumnFieldsArray["alfresco_perm_edit"] = array("type"=>"boolean");
                    $ColumnFieldsArray["alfresco_perm_delete"] = array("type"=>"boolean");
                    $ColumnFieldsArray["alfresco_perm_cancel_checkout"] = array("type"=>"boolean");
                    $ColumnFieldsArray["alfresco_perm_create"] = array("type"=>"boolean");
                    $ColumnFieldsArray["alfresco_perm_permissions"] = array("type"=>"boolean");

                    $ColumnFieldsArray["alfresco_isWorkingCopy"] = array("type"=>"boolean");
                    $ColumnFieldsArray["alfresco_isCheckedOut"] = array("type"=>"boolean");
                    $ColumnFieldsArray["alfresco_workingCopyId"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_originalId"] = array("type"=>"string");

                    $ColumnFieldsArray["alfresco_thumbnail"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_thumbnail_medium"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_thumbname"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_thumbnail_width"] = array("type"=>"string");
                    $ColumnFieldsArray["alfresco_thumbnail_height"] = array("type"=>"string");

                    foreach ($jsonData as $key => $column) {
                        $strKey = str_replace(":","_",$column->name);
                        $type = str_replace("d:","",$column->dataType);
                        $ColumnFieldsArray[$strKey] = array("type"=>"string");

                        $ColumnArray[$strKey] = array(
                            "header"=>$column->title,
                            "width"=>80,
                            "flex"=>1,
                            "sortable"=>"true",
                            "hideable"=>"true",
                            "groupable"=>"true"
                        );

                        switch($strKey) {
                            case 'cm_name':
                                $ColumnArray[$strKey]['groupable'] = false;
                                break;
                        }

						
                        if ($column->sort == true) {
                            $DefaultSort = $strKey;
                            if ($column->asc == true)
                                $DefaultSortDir = "ASC";
                            else
                                $DefaultSortDir = "DESC";
                        }
                        switch ($type) {
                        	case "long":
							case "int":
							case "double":
							case "float":
	
								$currencyField = $MetadataController->getCurrencyField($column->name,$em);
								if ($currencyField != null) {
									/*if ($currencyField->precision > 0) {
										$prec = $currencyField->decimal;
										for ($i = 0; $i < $currencyField->precision; $i++)
											$prec .= "0";
									}
									
									//$ColumnFieldsArray[$strKey]["type"] = "numbercolumn";
									//$ColumnFieldsArray[$strKey]["format"] = '$0'.$currencyField->thousands.'000'.$prec;
									//$ColumnArray[$strKey]["renderer"] = "Ifresco.util.GridRenderer.currencyRenderer";
									$ColumnFieldsArray[$strKey]["type"] = "number";
									$ColumnArray[$strKey]["xtype"] = "numbercolumn";
									$ColumnArray[$strKey]["format"] = $currencyField->currencySymbol.' 0'.$currencyField->thousands.'000'.$prec;*/
									
									$ColumnFieldsArray[$strKey]["type"] = "number";
									$ColumnArray[$strKey]["xtype"] = "currencycolumn";
									$ColumnArray[$strKey]["thousands"] = $currencyField->thousands;
									$ColumnArray[$strKey]["decimal"] = $currencyField->decimal;
									$ColumnArray[$strKey]["currencySymbol"] = $currencyField->currencySymbol.' ';
									$ColumnArray[$strKey]["precision"] = (int)$currencyField->precision;
								}
                        		break;
                            case "date":
                                //$ColumnFieldsArray[$strKey]["type"] = "date";
                                //$ColumnFieldsArray[$strKey]["dateFormat"] = $user->getDateFormat();
                            	$ColumnFieldsArray[$strKey]["type"] = "datecolumn";
                            	$ColumnFieldsArray[$strKey]["format"] = $user->getDateFormat();

                                //$ColumnArray[$strKey]["renderer"] = "Ext.util.Format.dateRenderer('".$user->getDateFormat()."')";
                                break;
                            case "datetime":
                                $ColumnFieldsArray[$strKey]["type"] = "datecolumn";
                                $ColumnFieldsArray[$strKey]["format"] = $user->getDateFormat()." ".$user->getTimeFormat();

                                /*$ColumnArray[$strKey]["renderer"] = "Ext.util.Format.dateRenderer('"
                                    . $user->getDateFormat() . " " . $user->getTimeFormat()."')";*/ 
                                break;
                            default:
                                break;
                        }

                        if ($column->hide == true)
                            $ColumnArray[$strKey]["hidden"] = "true";
                    }
                }
        }

        if ($useDefault == true) {
            $DefaultSort = "";
            $DefaultSortDir = "";

            $ColumnFieldsArray = array("alfresco_url"=>array(
                "type"=>"string"
            ),
                "nodeId"=>array(
                    "type"=>"string"
                ),
                "nodeRef"=>array(
                    "type"=>"string"
                ),
                "alfresco_mimetype"=>array(
                    "type"=>"string"
                ),
                "alfresco_type"=>array(
                    "type"=>"string"
                ),
                "alfresco_name"=>array("type"=>"string"),
                "alfresco_name_blank"=>array("type"=>"string"),

                "alfresco_perm_edit"=>array("type"=>"boolean"),
                "alfresco_perm_delete"=>array("type"=>"boolean"),
                "alfresco_perm_cancel_checkout"=>array("type"=>"boolean"),
                "alfresco_perm_create"=>array("type"=>"boolean"),
                "alfresco_perm_permissions"=>array("type"=>"boolean"),

                "alfresco_isWorkingCopy"=>array("type"=>"boolean"),
                "alfresco_isCheckedOut"=>array("type"=>"boolean"),
                "alfresco_workingCopyId"=>array("type"=>"string"),
                "alfresco_originalId"=>array("type"=>"string"),

                "alfresco_thumbnail" => array("type"=>"string"),
                "alfresco_thumbnail_medium" => array("type"=>"string"),
                "alfresco_thumbname" => array("type"=>"string"),
                "alfresco_thumbnail_width" => array("type"=>"string"),
                "alfresco_thumbnail_height" => array("type"=>"string"),

                "cm_name"=>array(
                    "type"=>"string"
                ),
                "cm_creator"=>array(
                    "type"=>"string"
                ),
                "cm_created"=>array(
                    "type"=>"datecolumn",
                    "format"=>$user->getDateFormat() . " " . $user->getTimeFormat()
                ),
                "cm_modified"=>array(
                    "type"=>"datecolumn",
                    "format"=>$user->getDateFormat() . " " . $user->getTimeFormat()
                )

            );

            $ColumnArray = array("cm_name"=>array(
                "header"=>"Name",
                "width"=>80,
                "flex"=>1,
                "sortable"=>true,
                "hideable"=>false,
                "groupable"=>false
            ),
                "cm_creator"=>array(
                    "header"=>"Creator",
                    "width"=>80,
                    "sortable"=>true,
                    "hideable"=>true,
                    "flex"=>1,
                    "groupable"=>true
                ),
                "cm_created"=>array(
                    "header"=>"Created",
                    "width"=>80,
                    "sortable"=>true,
                    "hideable"=>true,
                    "flex"=>1,
                    "renderer"=>"Ext.util.Format.dateRenderer('".$user->getDateFormat()." ".$user->getTimeFormat()."')",
                    "groupable"=>true
                ),
                "cm_modified"=>array(
                    "header"=>"Modified",
                    "width"=>80,
                    "sortable"=>true,
                    "hideable"=>true,
                    "flex"=>1,
                    "renderer"=>"Ext.util.Format.dateRenderer('".$user->getDateFormat()." ".$user->getTimeFormat()."')",
                    "groupable"=>true
                ),
                "alfresco_mimetype"=>array(
                    "header"=>"Mimetype",
                    "width"=>80,
                    "sortable"=>true,
                    "hideable"=>false,
                    "flex"=>1,
                    "groupable"=>true
                ),
            );
        }

        $view_vars['DefaultSort'] = $DefaultSort;
        $view_vars['DefaultSortDir'] = $DefaultSortDir;
        $view_vars['fields'] = $this->parseFields($ColumnFieldsArray);
        $view_vars['fieldsOld'] = $this->parseFieldsOld($ColumnFieldsArray);
        $view_vars['columns'] = $this->parseColumns($ColumnArray);
        $view_vars['columnsOld'] = $this->parseColumnsOld($ColumnArray);

        if (!empty($containerName))
            $view_vars['containerName'] = $containerName;
        else
            $view_vars['containerName'] = "";

        // Settings
        $getSettings = array(
            "DefaultTab",
            "OnlineEditing",
            "OCREnabled"
        );
        $view_vars['DefaultTab'] = 0;
        foreach ($getSettings as $SettingValue) {

            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:Setting', 's')
                ->where('s.key_string = :key_string')
                ->setParameter('key_string', $SettingValue);

            $Setting = $query->getQuery()->getOneOrNullResult();


            if ($Setting != null) {
                switch ($SettingValue) {
                    case "DefaultTab":
                        $mapOnValues = array(
                            "ifrescoViewPreviewTab" => "preview",
                            "ifrescoViewVersionsTab" => "versions",
                            "ifrescoViewMetadataTab" => "metadata",
                            "ifrescoViewParentMetadataTab" => "parentmetadata"
                        );

                        $ValueString = $Setting->getValueString();
                        if(Registry::getSetting("ParentNodeMeta") != "true" && $ValueString == "parentmetadata") {
                            $ValueString = "metadata";
                        }
                        $MapKey = array_search($ValueString, $mapOnValues);
                        if (empty($MapKey))
                            $MapKey = 0;
                        $Value = $MapKey;
                        break;
                    default:
                        $Value = $Setting->getValueString();
                        break;
                }
                $view_vars[$SettingValue] = $Value;

            }
        }

        $view_vars['DateFormat'] = $user->getDateFormat();
        $view_vars['TimeFormat'] = $user->getTimeFormat();

        return new JsonResponse($view_vars);
    }

    public function gridDataAction(Request $request)
    {
        $response = new JsonResponse($this->parseRequestData($request));

        $response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control','post-check=0, pre-check=0', false);
        $response->headers->set('Pragma','no-cache');

        return $response;
    }

    public function parseFields($fieldArray) {
        $fields = array();
        if (count($fieldArray) > 0) {

            foreach ($fieldArray as $key => $valArray) {
                $fields[] = array_merge($valArray, array('name' => $key));
            }
        }
        return $fields;
    }

    public function parseFieldsOld($fieldArray) {
        $content = "";
        if (count($fieldArray) > 0) {

            foreach ($fieldArray as $key => $valArray) {
                $valueString = "";
                foreach ($valArray as $valKey => $value) {
                    if (!empty($valueString)) {
                        $valueString .= ",";
                    }
                    $valueString .= "$valKey: '$value'";
                }

                if (!empty($valueString)) {
                    if (!empty($content)) {
                        $content .= ",";
                    }
                    $content .= '{name:\''.$key.'\','.$valueString.'}';
                }
            }
        }
        return $content;
    }

    public function parseColumns($columnArray) {
        $columns = array();
        if (count($columnArray) > 0) {
            foreach ($columnArray as $key => $valArray) {
                $columns[] = array_merge($valArray, array('dataIndex' => $key));
            }
        }

        return $columns;
    }

    public function parseColumnsOld($columnArray) {
        $content = "";
        if (count($columnArray) > 0) {

            foreach ($columnArray as $key => $valArray) {
                $valueString = "";
                foreach ($valArray as $valKey => $value) {
                    if (!empty($valueString))
                        $valueString .= ",";

                    if (is_bool($value) || is_int($value) || $valKey == "renderer") {
                        if ($value == true && is_bool($value))
                            $value = "true";
                        else if ($value == false && is_bool($value))
                            $value = "false";
                        $valueString .= "$valKey: $value";
                    }
                    else
                        $valueString .= "$valKey: '$value'";

                }
                if (!empty($valueString)) {
                    if (!empty($content))
                        $content .= ",";
                    $content .= '{'.$valueString.',
                dataIndex:\''.$key.'\'}';
                }
            }
        }

        return $content;
    }

    private function parseRequestData(Request $request, $limit="")
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $this->repository = $user->getRepository();
        $this->session = $user->getSession();
        $ticket = $user->getTicket();

        $this->spacesStore = new SpacesStore($this->session);

        $this->companyHome = $this->spacesStore->companyHome;

        $this->ifrescoScripts = new RESTifrescoScripts($this->repository,$this->spacesStore,$this->session);

        $this->start = $request->get('start');
        if (empty($this->start)) {
            $this->start = 0;
        }

        if (!is_numeric($limit)) {
            $this->limit = $request->get('limit');
            if (empty($this->limit)){
                $this->limit = 30;
            }
        }
        else {
            $this->limit = $limit;
        }

        $this->page = $request->get('page');
        if (empty($this->page)) {
            $this->page = 1;
        }

        $array = array("data"=>array());

        try {
            $json_sort = json_decode($request->get('sort'));
            if ($json_sort) {
                $this->sort = $json_sort[0]->property;
                $this->sorting = $json_sort[0]->direction;
            } else {
                $this->sort = $request->get('sort');
                $this->sorting = $request->get('dir');
            }

            $nodeId = $request->get('nodeId');
            $category = $request->get('categories');
            $categoryNodeId = $request->get('categoryNodeId');
            $subCategories = $request->get('subCategories');
            $fromTree = $request->get('fromTree');
            if (empty($subCategories) || !isset($subCategories)) {
                $subCategories = false;
            } else {
                $subCategories = (string)$subCategories;
            }

            $tag = $request->get('tag');
            $searchTerm = $request->get('searchTerm');
            $searchTerm = urldecode($searchTerm);

            $clickSearch = $request->get('clickSearch');
            $clickSearchValue = $request->get('clickSearchValue');
            $clickSearchValue = urldecode($clickSearchValue);

            $columnSetId = $request->get('columnSetId');

            $thumbs = $request->get('thumbs');
            if ($thumbs == true) {
                $this->addThumbs = true;
            }

            $reload = $request->get('reload');
            if ($reload == true) {
                NodeCache::getInstance()->clear();
            }

            $advancedSearchFields = $request->get('advancedSearchFields');
            $advancedSearchOptions = $request->get('advancedSearchOptions');

            $clipboard = $request->get('clipboard');
            $clipboardItems = $request->get('clipboarditems');

            $em = $this->getDoctrine()->getManager();
            if ($columnSetId == 0 || empty($columnSetId)) {
            	$query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:SearchColumnSet', 's')
            	->where('s.is_default_set = :default')
            	->setParameter('default', true);
            	
            	$searchColumnSet = $query->getQuery()->getOneOrNullResult();
            	if ($searchColumnSet != null)
            		$columnSetId = $searchColumnSet->getId();
            }
            
            if ($columnSetId > 0) {
                $this->metaRenderer = MetaRenderer::getInstance($user);
                $this->metaRenderer->scanRenderers();
            }

            if (!empty($nodeId)) {
                $array = $this->documentList($nodeId, $columnSetId);
            } elseif (!empty($category)) {
                $array = $this->categoryList($category, $categoryNodeId, $subCategories, $fromTree, $columnSetId);
            } elseif (!empty($tag)) {
                $array = $this->tagList($tag, $columnSetId);
            } elseif (!empty($searchTerm)) {
                $array = $this->quickSearchList($searchTerm, $columnSetId);
            } elseif (!empty($clickSearch) && !empty($clickSearchValue)) {
                $array = $this->clickSearchList($clickSearchValue, $clickSearch, $columnSetId);
            } elseif (!empty($advancedSearchFields) && !empty($advancedSearchOptions)) {
                $array = $this->advancedSearchList($advancedSearchFields, $advancedSearchOptions, $columnSetId);
            }elseif ($clipboard == true && !empty($clipboardItems)) {
                $clipboardItems = json_decode($clipboardItems);
                if (!is_array($clipboardItems)) {
                    throw new Exception();
                }

                $array = $this->clipBoardList($clipboardItems, $columnSetId);
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }

        return $array;
    }

    private function documentList($nodeId, $columnSetId = 0)
    {
        $array = array(
            "totalCount" => 0,
            "breadcrumb" => "",
            "data" => array(),
            "perms" => array(),
            "folder_path" => ''
        );

        if ($nodeId == "root") {
            $nodeId = $this->companyHome->getId();
        }


        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $realPath = "";
        $mainNode = NodeCache::getInstance()->getNode($this->session, $this->spacesStore, $nodeId);

        if ($mainNode != null) {
            $realPath = $mainNode->getFolderPath(true,true);
            $realPath = str_replace("/Company Home", "", $realPath);

            $explode = $mainNode->getRealPathRefs(true);
            $explode = array_reverse($explode);

            $permissions = $mainNode->getPermissions();
            $perms = array();
            $perms["alfresco_perm_edit"] = $permissions ? $permissions->userAccess->edit : false;
            $perms["alfresco_perm_delete"] = $permissions ? $permissions->userAccess->delete : false;
            $perms["alfresco_perm_cancel_checkout"] = $permissions ? $permissions->userAccess->{"cancel-checkout"} : false;
            $perms["alfresco_perm_create"] = $permissions ? $permissions->userAccess->create : false;
            $perms["alfresco_perm_permissions"] = $permissions ? $permissions->userAccess->permissions : false;

            $array['perms'] = $perms;

            $folderPathArray = array();
            $pathString = "";
            $parentRef = $this->spacesStore->companyHome->getId();
            $siteItem = null;
            $isSite = false;
            $siteId = null;
            $siteName = null;
            $siteDocLib = null;
            $index = 0;
            for ($i = 0; $i < count($explode); $i++) {
                $path = trim($explode[$i]);
                if (!empty($path)){
                    $pathStr = $path;

                    if (!empty($pathString)){
                        $pathString .= "/";
                    }
                    

                    $pathString .= "$pathStr";
                    
                    if ($path == $parentRef){
                        continue;
                    }

                    $nodePath = NodeCache::getInstance()->getNode($session, $spacesStore, $path);
                    
                    if ($nodePath != null) {
                    	if ($isSite == true && $nodePath->cm_name == "documentLibrary") {
                    		$siteDocLib = $nodePath->id;
                    		$folderPathArray[$siteItem]["id"] = $siteDocLib;
                    		$folderPathArray[$siteItem]["siteDocLib"] = $siteDocLib;
                    		
                    		$pathString = str_replace("/".$pathStr,"",$pathString);
                    		continue;
                    	}
                    	else {
	                        $folderPathArray[$index] = array(
	                            "id" => $nodePath->id,
	                            "text" => $nodePath->cm_name,
	                            "icon" => $nodePath->getIconUrl(),
	                        	"clickable" => ($nodePath->getType() == "{http://www.alfresco.org/model/site/1.0}sites" ? false : true),
	                        	"isSite" => $isSite,
	                        	"siteId" => $siteId,
	                        	"siteDocLib" => $siteDocLib,
	                        	"siteName" => $siteName
	                        );
	                        
                    	}                        
                        
                        if ($nodePath->getType() == "{http://www.alfresco.org/model/site/1.0}site") {
                        	$isSite = true;
                        	$siteItem = $index;
                        	$siteId = $nodePath->id;
                        	$siteName = $nodePath->cm_title;
                        	$folderPathArray[$index]["siteId"] = $siteId;
                        	$folderPathArray[$index]["isSite"] = true;
                        	$folderPathArray[$index]["siteName"] = $siteName;
                        	
                        }
                        $index++;
                    }
                }
            }


            $company = array(
                array(
                    "id" => $this->spacesStore->companyHome->id,
                    "text" => $this->get('translator')->trans("Repository"),
                    "icon" => $this->spacesStore->companyHome->getIconUrl(),
                	"clickable" => true,
                	"isSite" => false,
                	"siteId" => null,
	                "siteDocLib" => null,
                	"siteName" => null
                )
            );
            $array["breadcrumb"] = array_merge($company, $folderPathArray);
            $array["folder_path"] = str_replace('/Company Home', '', $mainNode->getFolderPath(true, false));
        }

        $pos = $this->page == 0 ? 1 : $this->page;

        if (preg_match("/^alfresco_.*/is",$this->sort)) {
            $this->sort = "";
        }

        if (!empty($this->sort)) {
            $sortField = $this->sort;
            $sortField = preg_replace("/^(.*?)_(.*)/is","$1:$2", $sortField);
            $sortAsc = "true";
            if ($this->sorting != "ASC"){
                $sortAsc = "false";
            }

            $nodes = $this->ifrescoScripts->GetDocLib($realPath, $this->limit, $pos, $sortField, $sortAsc);
        } else {
            $nodes = $this->ifrescoScripts->GetDocLib($realPath, $this->limit, $pos);
        }

        if ($nodes != null) {
            $array["totalCount"] = $nodes->totalRecords;
            $items = $nodes->items;
            foreach ($items as $node) {
                $type = $node->getType();

                if ($type != "{http://www.alfresco.org/model/site/1.0}sites" && $type != "{http://www.alfresco.org/model/site/1.0}site") {
                    $array["data"][] = $this->parseResult($node, $columnSetId);
                }
            }
        }

        return $array;
    }

    private function categoryList($category, $categoryNodeId, $subCategories, $fromTree = "false", $columnSetId = 0)
    {
        $array = array(
        	"isCategoryList" => true,
            "totalCount" => 0,
            "breadcrumb" => "",
            "data" => array(),
            "folder_path" => ''
        );

        if ($category == "root" || empty($category)) {
            $nodes = array();
        } else {
            $splitCategories = preg_split("#/#", $category);
            if (preg_match("#/#eis", $category) || $fromTree == "true") {
                $newCategory = "";
                foreach ($splitCategories as $cat) {
                    $cat = ISO9075Mapper::map($cat);
                    if (!empty($cat)) {
                        if (!empty($newCategory))
                            $newCategory .= "/";
                        $newCategory .= "cm:" . $cat;
                    }
                }
                $category = $newCategory;
                $searchFor = $subCategories == "true" ? "/member" : "member";

                if (!empty($this->sort)) {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        "PATH:\"/cm:generalclassifiable/" . $category . "/" . $searchFor . "\"",
                        $this->limit,
                        $this->start,
                        $this->sort,
                        $this->sorting
                    );
                } else {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        "PATH:\"/cm:generalclassifiable/" . $category . "/" . $searchFor . "\"",
                        $this->limit,
                        $this->start
                    );
                }
            }
            else {
                if (!empty($this->sort)) {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        "PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND -TYPE:\"cm:category\"",
                        $this->limit,
                        $this->start,
                        $this->sort,
                        $this->sorting
                    );
                } else {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        "PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND -TYPE:\"cm:category\"",
                        $this->limit,
                        $this->start
                    );
                }
            }

            $array["totalCount"] = (string)$this->session->getLastQueryCount();
        }

        if ($nodes != null && count($nodes) > 0) {
            foreach ($nodes as $child) {
                if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites" && (!isset($child->child)
                        || $child->child->type != "{http://www.alfresco.org/model/site/1.0}site")
                ) {
                    $array["data"][] = $this->parseResult($child, $columnSetId);
                }
            }
        }

        return $array;
    }

    private function quickSearchList($searchTerm, $columnSetId = 0)
    {
        $array = array(
            "totalCount" => 0,
            "breadcrumb" => "",
            "data" => array(),
            "folder_path" => ''
        );

        if (empty($searchTerm)) {
            $documents = array();
        } else {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:Setting', 's')
                ->where('s.key_string = :key_string')
                ->setParameter('key_string', 'QuickSearch');

            $quickSearchSetting = $query->getQuery()->getOneOrNullResult();

            $fields = array();
            if ($quickSearchSetting != null) {
                $jsonData = $quickSearchSetting->getValueString();
                $jsonData = json_decode($jsonData);

                $fields = $jsonData;
            }

            $options = array(
                "searchTerm" => "",
                "results" => "all",
                "searchBy" => "OR",
                "locations" => array(),
                "categories" => array(),
                "tags" => ""
            );

            $searchFields = array();

            if (count($fields) > 0) {
                foreach ($fields as $field) {
                	if ($field->name == "cm:content") {
                		$options["searchTerm"] = $searchTerm;
                	}
                	else
                    	$searchFields[$field->name] = $searchTerm;
                }
            }
            else
            	$options["searchTerm"] = $searchTerm;
            	
            $options = json_encode($options);

            $queryLucene = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:Setting', 's')
                ->where('s.key_string = :key_string')
                ->setParameter('key_string', 'QuickSearchLucene');
            $quickSearchLucene = $queryLucene->getQuery()->getOneOrNullResult();

            if ($quickSearchLucene != null) {
                $lucene = $quickSearchLucene->getValueString();
                $searchFields["lucene_query"] = $lucene;
            }

            $searchFields = json_encode($searchFields);
            return $this->advancedSearchList($searchFields, $options, $columnSetId);
        }

        if (count($documents) > 0) {
            for ($i = 0; $i < count($documents); $i++) {
                $node = $documents[$i];
                if ($node->getType() != "{http://www.alfresco.org/model/site/1.0}sites"
                    && $node->getType() != "{http://www.alfresco.org/model/site/1.0}site"
                ) {
                    $array["data"][] = $this->parseResult($node, $columnSetId);
                }
            }
        }
        return $array;
    }

    private function clickSearchList($searchTerm, $searchField, $columnSetId = 0)
    {
        $array = array(
            "totalCount" => 0,
            "breadcrumb" => "",
            "data" => array(),
            "folder_path" => ''
        );

        if (!empty($searchTerm)) {
            $options = json_encode(array(
                "searchTerm" => "",
                "results" => "all",
                "searchBy" => "OR",
                "locations" => array(),
                "categories" => array(),
                "tags" => ""
            ));

            $searchFields = json_encode(array("{$searchField}" => $searchTerm));

            return $this->advancedSearchList($searchFields, $options, $columnSetId);
        }

        return $array;
    }

    private function advancedSearchList($searchFields, $options, $columnSetId) {
    	$em = $this->getDoctrine()->getManager();
    	$user = $this->get('security.context')->getToken();
    	
        $searchTerm = "";

        $array = array(
            "isSearch" => true,
            "totalCount" => 0,
            "breadcrumb" => "",
            "data" => array(),
            "folder_path" => ''
        );
        $fullTextChild = false;
        $fullTextChildOverwrite = false;
        $pathArray = array();
        $searchFields = json_decode($searchFields);

        $customSearch = isset($searchFields->custom_search) ? $searchFields->custom_search : '0';
        if (isset($searchFields->custom_search)){ 
            $searchFields->custom_search = null;
        }

        $luceneQuery = isset($searchFields->lucene_query) ? str_replace("&quot;", '"', urldecode($searchFields->lucene_query)) : '';
        if(isset($searchFields->lucene_query)) {
            $searchFields->lucene_query = null;
        }
        
        $fieldValueExists = false;
        $searchTwice = false;

        if( (int) $customSearch > 0 ) {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:SearchTemplate', 's')
                ->where('s.id = :id')
                ->setParameter('id', $customSearch);

            /**
             * @var SearchTemplate $searchTemplate
             */
            $searchTemplate = $query->getQuery()->getOneOrNullResult();

            if($searchTemplate != null) {
                $jsonData = $searchTemplate->getJsonData();

                if (!empty($jsonData)) {
                    $jsonData = json_decode($jsonData);

                    if(isset($jsonData->customFields) && count($jsonData->customFields) > 0) {
                        foreach ($jsonData->customFields as $customNum => $customData) {

                            $customSearchValue = isset($searchFields->{'custom-field-control' . $customNum})
                                ? $searchFields->{'custom-field-control'.$customNum}
                                : ''
                            ;
                            $searchFields->{'custom-field-control'.$customNum} = null;

                            if(trim($customSearchValue) !='') {
                                $fieldValueExists = true;
                                $customSearchValue = str_replace("+", "%2B", $customSearchValue);
                                $customSearchValue = str_replace("\"", '\"', urldecode($customSearchValue));
                                //$customSearchValue = utf8_encode(str_replace("\"", '\"', urldecode($customSearchValue)));

                                $customFieldValues = $customData->customFieldValues;
                                $customQueryMode = $customData->customQueryMode;

                                $customFields = array();
                                if(count($customFieldValues) > 0) {
                                    foreach($customFieldValues as $nextField){
                                        $customFields[] = $nextField->name;
                                    }
                                }
                                $customQueryMode = $customQueryMode == 'and' ? ' AND ' : ' OR ';
                                $customSearches = array();

                                if(count($customFields) > 0)
                                    foreach($customFields as $customField) {
                                        $prop = $customField;
                                        $prop = str_replace("#from", "", $prop);
                                        $prop = str_replace("#to", "", $prop);
                                        $prop = str_replace("#list", "", $prop);
                                        $prop = str_replace(":", "\\:", $prop);
                                        $prop = str_replace("_", "\\:", $prop);

                                        $customSearches[] = " @$prop:\"$customSearchValue\"";
                                    }

                                $searchTerm .= '(' . implode($customQueryMode, $customSearches) . ') ';
                            }
                        }
                    }
                }

                $fullTextChild = $searchTemplate->getIsFullTextChild();
                $fullTextChildOverwrite = $searchTemplate->getIsFullTextChildOverwrite();
            }
        }

        $options = json_decode($options);
        $searchBy = !isset($options->searchBy) || empty($options->searchBy) ? "AND" : $options->searchBy;
        $contentType = (isset($options->contentType) ? $options->contentType : null);

        if (!(count($searchFields) == 0 && empty($options->searchTerm))) {
            $dateCombos = array();

            foreach ($searchFields as $key => $value) {
                if (empty($value)) continue;

                if(!is_bool($value)) {
                    $value = str_replace("+", "%2B", $value);
                    //$value = utf8_encode(str_replace("\"", '\"', urldecode($value)));
                    $value = str_replace("\"", '\"', urldecode($value));
                }

                if ($key == "cm_content" && empty($options->searchTerm) || $key == "cm:content" && empty($options->searchTerm))  {
                    $options->searchTerm = $value;
                    continue;
                } else if ($key == "cm_content" && !empty($options->searchTerm) || $key == "cm:content" && !empty($options->searchTerm)){
                    continue;
                }

                $prop = $key;
                $prop = str_replace("#from", "", $prop);
                $prop = str_replace("#to", "", $prop);
                $prop = str_replace("#list", "", $prop);
                $prop = str_replace(":", "\\:", $prop);
                $prop = str_replace("_", "\\:", $prop);

                
                if (preg_match("/#from/is", $key)) {
                	$date = \DateTime::createFromFormat($user->getDateFormat(), $value);
                	$value = $date->format('Y-m-d')."T";
                    $dateCombos[$prop]["from"] = $value;
                }
                if (preg_match("/#to/is", $key)) {
                	$date = \DateTime::createFromFormat($user->getDateFormat(), $value);
                	$value = $date->format('Y-m-d')."T";
                    $dateCombos[$prop]["to"] = $value;
                }
                
            }

            $alreadyChecked = array();

            foreach ($searchFields as $key => $value) {
                if(!is_bool($value)) {
                    $value = str_replace("+", "%2B", $value);
                    if($value) {
                        //$value = utf8_encode(str_replace("\"",'\"',urldecode($value)));
                        $value = str_replace("\"",'\"',urldecode($value));
                    }
                }

                if ($key == "cm_content" || $key == "cm:content" || $key == "customQueryMode")  {
                    continue;
                }

                if (!empty($value) && $value != null){
                    $fieldValueExists = true;
                }

                $prop = $key;
                $prop = str_replace("#from", "", $prop);
                $prop = str_replace("#to", "", $prop);
                $prop = str_replace("#list", "", $prop);
                $prop = str_replace(":", "\\:", $prop);
                $prop = str_replace("_", "\\:", $prop);


                if (empty($value) || in_array($prop,$alreadyChecked)) {
                    continue;
                }

                if (preg_match("/^.*?\:.*\-checkbox$/eis", $prop) && $value == "true" || preg_match("/^.*?\:.*\-checkbox$/eis", $prop) && $value == true){
                    continue;
                }

                $create = false;
                if (preg_match("/#from/is", $key) || preg_match("/#to/is", $key)) {
                    if (!empty($dateCombos[$prop]["from"]) && !empty($dateCombos[$prop]["to"])) {
                    	if (!empty($searchTerm))
                    		$searchTerm .= " AND ";
                        $searchTerm .= "@$prop:[" . $dateCombos[$prop]["from"] . " TO " . $dateCombos[$prop]["to"] . "] ";
                        $create = false;
                        $alreadyChecked[] = $prop;
                        unset($dateCombos[$prop]);
        
                    } else {
                        $create = true;
                    }
                } else if (preg_match("/#list/eis", $key) && preg_match("/,/eis", $value)) {
                    $create = false;
                    $explode = explode(",", $value);
                    foreach ($explode as $exp) {
                    	if (!empty($searchTerm))
                    		$searchTerm .= " AND ";
                        $searchTerm .= "@$prop:\"$exp\" ";
                    }
                }
                else {
                    $create = true;
                }

                if ($create == true) {
                    if (!preg_match("/#to/eis", $key) && !preg_match("/#from/eis", $key)) {
                        if (preg_match("/.*?AND.*?/eis", $value)) {
                            $explode = explode(" AND ", $value);
                            if (count($explode) > 0) {
                                if (!empty($searchTerm))
                                    $searchTerm .= $searchBy . " ";
                                $searchTerm .= "(";
                                for ($i = 0; $i < count($explode); $i++) {
                                    $explodeVal = $explode[$i];
                                    if ($i > 0)
                                    	$searchTerm .= " AND ";
                                    $searchTerm .= "@$prop:\"$explodeVal\"";
                                }
                                $searchTerm .= ") ";
                            }
                        } else if (preg_match("/.*?OR.*?/eis", $value)) {
                            $explode = explode(" OR ", $value);
                            if (count($explode) > 0) {
                                if (!empty($searchTerm))
                                    $searchTerm .= $searchBy . " ";
                                $searchTerm .= "(";
                                for ($i = 0; $i < count($explode); $i++) {
                                    $explodeVal = $explode[$i];
                                    if ($i > 0)
                                    	$searchTerm .= " OR ";
                                    $searchTerm .= "@$prop:\"$explodeVal\"";
                                }
                                $searchTerm .= ") ";
                            }
                        } else if (preg_match("/[0-9]+ TO [0-9]+/eis", $value)) {
                            $explode = explode(" TO ", $value);
                            if (count($explode) == 2) {
                            	if (!empty($searchTerm))
                            		$searchTerm .= $searchBy . " ";
                            	
                            	$from = $explode[0];
                            	$to = $explode[1];
                            	$searchTerm .= "@$prop:[$from TO $to] ";
                            }
                        } else if (preg_match("/[0-9]+ BIS [0-9]+/eis", $value)) {
                            $explode = explode(" BIS ", $value);
                            if (count($explode) == 2) {
                            	if (!empty($searchTerm))
                            		$searchTerm .= $searchBy . " ";
                            	
                            	$from = $explode[0];
                            	$to = $explode[1];
                            	$searchTerm .= "@$prop:[$from TO $to] ";
                            }
                        } else {
                            if (!empty($searchTerm))
                                $searchTerm .= $searchBy . " ";
                        	

                            if(is_bool($value) && $value)
                                $searchTerm .= "@$prop:true ";
                            else
                                $searchTerm .= "@$prop:\"$value\" ";
                        }
                    }
                }
            }
            $pathSearch = false;

            if (count($options->categories) > 0) {
                $categoryTerm = "";

                foreach ($options->categories as $category) {
                    $categoryPath = $category->qpath;

                    if (!empty($categoryTerm))
                        $categoryTerm .= " OR ";
                    $categoryTerm .= 'PATH:"' . $categoryPath.'/member" ';
                }
                $pathSearch = true;
                if (count($options->categories) == 1)
                    $searchTerm .= "+$categoryTerm ";
                else {
                    if (!empty($searchTerm)) {
                        $searchTerm .= "AND (" . $categoryTerm . ") ";
                    } else {
                        $searchTerm .= "(" . $categoryTerm . ") ";
                    }
                }
            }

            if (count($options->locations) > 0) {
                $locationTerm = "";
                foreach ($options->locations as $location) {
                    $locationId = $location->nodeId;
                    $locationPath = $location->qpath;
                    if (!empty($locationTerm)){
                        $locationTerm .= " OR ";
                    }
                    $locationTerm .= 'PATH:"' . $locationPath . '/*" '; // lucene hack
                }
                $pathSearch = true;
                if (count($options->locations) == 1) {
                    $searchTerm .= "+$locationTerm ";
                } else {
                    if (!empty($searchTerm)) {
                        $searchTerm .= "AND (" . $locationTerm . ") ";
                    } else {
                        $searchTerm .= "(" . $locationTerm . ") ";
                    }
                }
            }

            if (count($options->tags) > 0) {
                if (!empty($options->tags)) {
                    $tagTerm = "";
                    $tagArray = array();
                    if (preg_match("/,/eis", $options->tags)) {
                        $tagArray = preg_split("#,#", $options->tags);
                    } else {
                        $tagArray[] = $options->tags;
                    }

                    for ($i = 0; $i < count($tagArray); $i++) {
                        $tagName = ISO9075Mapper::map($tagArray[$i]);
                        if (empty($tagName)) {
                            continue;
                        }

                        if (!empty($tagTerm)){
                            $tagTerm .= " OR ";
                        }

                        $tagTerm .= 'PATH:"/cm:taggable//cm:' . $tagName . '/member" ';
                    }

                    $pathSearch = true;
                    /*if (count($tagArray) == 1) {
                        $searchTerm .= "+$tagTerm ";
                    } else {*/
                        if (!empty($searchTerm)) {
                            $searchTerm .= "AND (".$tagTerm.") ";
                        } else {
                            $searchTerm .= "(".$tagTerm.") ";
                        }
                    //}
                }
            }

            if (isset($options->searchTerm) && strlen($options->searchTerm) > 0) {
                if ($fullTextChild != true || $fieldValueExists==false) {
                    if (!empty($searchTerm)) {
                        $searchTerm .= $searchBy." ";
                    }

                    $options->searchTerm = str_ireplace(
                        array(' and ', ' or ', ' not '),
                        array(' AND ', ' OR ', ' NOT '),
                        $options->searchTerm
                    );

                    if($this->getRequest()->getLocale() == 'de') {
                        $options->searchTerm = str_ireplace(
                            array(' und ', ' oder ', ' nicht '),
                            array(' AND ', ' OR ', ' NOT '),
                            $options->searchTerm
                        );
                    }
                    
                    $logger = $this->get('logger');
                    $logger->info('SEARCHING!!!');
                    $logger->info('Original query - ' . $options->searchTerm);

                    if(preg_match_all('/("[^"]+"|[\p{L}\p{N}-\+]+)/u', $options->searchTerm, $parts)) {
                        if(count($parts[1]) > 1) {
                            $newConditions = array();
                            for($i = 0; $i < count($parts[1]); $i++) {
                                $newConditions[] = $parts[1][$i];

                                if(isset($parts[1][$i+1])) {
                                    if(in_array($parts[1][$i+1], array('AND', 'OR', 'NOT'))) {
                                        $newConditions[] = $parts[1][$i+1];
                                        $i++;
                                    }
                                    else {
                                        $newConditions[] = 'AND';
                                    }
                                }
                            }
                            $options->searchTerm = implode(' ', $newConditions);
                        }
                    }

                    $logger->info('Final value - ' . $options->searchTerm);
                    $logger->info('-------------------------------------');

                    if (!preg_match("/\[.*\]/eis", $options->searchTerm)) {
                        $searchTerm .= 'TEXT:(' . $options->searchTerm . ')';
                    } else {
                        $searchTerm .= 'TEXT:"' . $options->searchTerm . '"';
                    }
                } else {
                    $searchTwice = true;
                }
            }

            if (count($dateCombos) > 0) {
                foreach ($dateCombos as $key => $value) {
                    if (!empty($values["from"]) && empty($values["to"])) {
                        if (!empty($searchTerm)) {
                            $searchTerm .= " AND ";
                        }

                        $searchTerm .= "@$key:[" . $values["from"] . " TO NOW]";
                    }
                }
            }
            if (!$pathSearch) {
                //$searchTerm .=" -TYPE:\"cm:category\" -TYPE:\"cm:thumbnail\"";
            }
            
            

            $luceneQuery = str_replace('cm_', 'cm\:', $luceneQuery);

            if($luceneQuery) {
                $searchTerm = "(" . $searchTerm . ") " . $luceneQuery;
            }

            if (!$pathSearch) {
                //$searchTerm .=" AND NOT ASPECT:\"sys:hidden\"";
            }

            if ($contentType != null) {
            	$searchTerm .=" AND TYPE:\"$contentType\"";
            }
            
            $MaxSearchResults = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            		'key_string' => 'MaxSearchResults'
            ));
            if ($MaxSearchResults == null) {
            	$MaxSearchResults = 250;
            }
            else
            	$MaxSearchResults = $MaxSearchResults->getValueString();
            
            $SearchPaging = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            		'key_string' => 'SearchPaging'
            ));
            
            $isServerSidePaging = true;
            
            if ($SearchPaging == null) {
            	$isServerSidePaging = true;
            }
            else {
            	if ($SearchPaging->getValueString() != "ServerSide") {
            		$isServerSidePaging = false;
            	}
            }

            if ($searchTwice == true) {
                //$nodes = $this->session->query($this->spacesStore, $searchTerm);
                $nodes = $this->ifrescoScripts->Search("",$searchTerm,"",false);
            } else {
                /*if (!empty($this->sort)) {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        $searchTerm,
                        $this->limit,
                        $this->start,
                        $this->sort,
                        $this->sorting
                    );
                } else {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        $searchTerm,
                        $this->limit,
                        $this->start
                    );
                }*/
            	
            	$sortField = "";
            	$sortDesc = false;
            	if (!empty($this->sort)) {
            		$sortField = $this->sort;
            		$sortField = str_replace("_",":",$sortField);
            	
            		if ($this->sorting != "ASC")
            			$sortDesc = true;
            	}
            	 
            	if ($isServerSidePaging) {
            		$nodes = $this->ifrescoScripts->Search("",$searchTerm,$sortField,$sortDesc,$MaxSearchResults,$this->start,$this->limit);
            	}
            	else {
            		$nodes = $this->ifrescoScripts->Search("",$searchTerm,$sortField,$sortDesc,$MaxSearchResults);
            	}
            }

            //$array["totalCount"] = (string)$this->session->getLastQueryCount();
            if (!isset($nodes->totalCount)) {
            	echo $nodes->url;
            	print_r($nodes);
            	die("ERROR");
        	}
            $array["totalCount"] = (string)$nodes->totalCount;
            //if ($this->get('kernel')->getEnvironment() == "dev") {
                $array["searchTerm"] = $searchTerm;
            //}

            if ($searchTwice == true && $array["totalCount"] > 0) {
                if ($this->get('kernel')->getEnvironment() == "dev") {
                    $array["searchResultsFirstCount"] = $array["totalCount"];
                    $array["searchResultsFirstCount"] = count($nodes);
                    $array["searchBuildPath"] = array();
                }
                $searchTerm = "";

                if (isset($nodes) && count($nodes) > 0) {
                    foreach ($nodes as $child) {
                        $path = $child->getRealPath();
                        if (!empty($searchTerm))
                            $searchTerm .= " OR ";

                        if ($this->get('kernel')->getEnvironment() == "dev") {
                            $array["searchBuildPath"][] = array($path,$child->cm_name);
                        }

                        $searchTerm .= 'PATH:"' . $path . '//*"';

                        if ($fullTextChildOverwrite == true) {
                            $properties = $child->getProperties();
                            foreach ($properties as $propKey => $propValue) {
                                if (preg_match("#\{http://www.alfresco.org/model/content/1.0\}#eis", $propKey) ||
                                    preg_match("#\{http://www.alfresco.org/model/system/1.0\}#eis", $propKey) ||
                                    preg_match("#\{http://www.alfresco.org/model/application/1.0\}#eis", $propKey))
                                    unset($properties[$propKey]);
                            }
                            $pathArray[$path] = $properties;
                        }
                    }
                }

                if (!preg_match("/\[.*\]/eis", $options->searchTerm)) {
                    $searchTerm = 'TEXT:(' . $options->searchTerm . ') AND (' . $searchTerm . ')';
                } else {
                    $searchTerm = 'TEXT:"' . $options->searchTerm . '" AND (' . $searchTerm . ')';
                }

                if (!empty($this->_sort)) {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        $searchTerm,
                        $this->limit,
                        $this->start,
                        $this->sort,
                        $this->sorting
                    );
                } else {
                    $nodes = $this->session->filteredQuery(
                        $this->spacesStore,
                        $searchTerm,
                        $this->limit,
                        $this->start
                    );
                }

                $array["totalCount"] = (string)$this->session->getLastQueryCount();
                if ($this->get('kernel')->getEnvironment() == "dev") {
                    $array["searchTermTwice"] = $searchTerm;
                }
            }
        }

        if (isset($nodes) && count($nodes) > 0) {
            /*foreach ($nodes as $child) {
                if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites"
                    && (!isset($child->child->type) || $child->child->type != "{http://www.alfresco.org/model/site/1.0}site")
                ) {
                    if ($fullTextChildOverwrite == true) {
                        $checkPath = $child->getRealPath(false);
                        $count = 0;
                        $found = true;
                        while (!array_key_exists($checkPath, $pathArray)) {
                            $checkPath = preg_replace("/(.*)\/.*\//is","$1", $checkPath);
                            if ($count >= 10) {
                                $found = false;
                                break;
                            }
                            $count++;
                        }

                        if ($found) {
                            $properties = array_merge($child->getProperties(), $pathArray[$checkPath]);
                            $child->setProperties($properties);
                        }
                    }
                    $array["data"][] = $this->parseResult($child, $columnSetId);
                }
            }*/
        	$items = $nodes->items;
        	foreach ($items as $child) {
        		$type = $child->getType();
        		if ($type != "{http://www.alfresco.org/model/site/1.0}sites" || $type != "{http://www.alfresco.org/model/site/1.0}site")
        		{
        			if ($fullTextChildOverwrite == true) {
        				$checkPath = $child->getRealPath(false);
        				$count = 0;
        				$found = true;
        				while (!array_key_exists($checkPath, $pathArray)) {
        					$checkPath = preg_replace("/(.*)\/.*\//is","$1", $checkPath);
        					if ($count >= 10) {
        						$found = false;
        						break;
        					}
        					$count++;
        				}
        	
        				if ($found) {
        					$properties = array_merge($child->getProperties(), $pathArray[$checkPath]);
        					$child->setProperties($properties);
        				}
        			}
        			$array["data"][] = $this->parseResult($child, $columnSetId);
        		}
        	}
        }
        return $array;
    }

    private function tagList($tag, $columnSetId = 0) {
        $array = array(
        	"isTagList" => true,
            "totalCount" => 0,
            "breadcrumb" => "",
            "data" => array(),
            "folder_path" => ''
        );

        if (empty($tag)) {
            $nodes = array();
        } else {
            $tag = ISO9075Mapper::map($tag);
            $tag = preg_replace("#.*/(.*?)#is", "$1", $tag);
            $tag = str_replace(" ", "_x0020_", $tag);
            $tag = str_replace(",", "_x002c_", $tag);
            $tag = str_replace("%20", "_x0020_", $tag);

            if (!empty($this->sort)){
                $nodes = $this->session->filteredQuery(
                    $this->spacesStore,
                    "PATH:\"/cm:taggable//cm:" . $tag . "//member\"",
                    $this->limit,
                    $this->start,
                    $this->sort,
                    $this->sorting
                );
            } else {
                $nodes = $this->session->filteredQuery(
                    $this->spacesStore,
                    "PATH:\"/cm:taggable//cm:" . $tag . "//member\"",
                    $this->limit,
                    $this->start
                );
            }

            $array["totalCount"] = (string)$this->session->getLastQueryCount();
        }

        if ($nodes != null && count($nodes) > 0) {
            foreach ($nodes as $child) {
                if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites"
                    && (!isset($child->child) || $child->child->type != "{http://www.alfresco.org/model/site/1.0}site")
                ) {
                    $array["data"][] = $this->parseResult($child, $columnSetId);
                }
            }
        }

        return $array;
    }

    private function clipBoardList($items, $columnSetId = 0) {
        $array = array(
            "totalCount" => 0,
            "breadcrumb" => "",
            "data" => array(),
            "folder_path" => ''
        );

        if (count($items) == 0) {
            $nodes = array();
        } else {
            $searchStr = "";
            $arrayWorkedOn = array();
            foreach ($items as $item) {
                if (in_array($item, $arrayWorkedOn)) {
                    continue;
                }
                if (!empty($searchStr)) {
                    $searchStr .= " OR ";
                }

                $searchStr .= 'ID:"workspace://SpacesStore/' . $item . '"';
            }

            if (!empty($this->sort)) {
                $nodes = $this->session->filteredQuery(
                    $this->spacesStore,
                    $searchStr,
                    $this->limit,
                    $this->start,
                    $this->sort,
                    $this->sorting
                );
            } else {
                $nodes = $this->session->filteredQuery(
                    $this->spacesStore,
                    $searchStr,
                    $this->limit,
                    $this->start
                );
            }

            $array["totalCount"] = (string)$this->session->getLastQueryCount();
        }


        if ($nodes != null && count($nodes) > 0) {
            foreach ($nodes as $child) {
                $ch = $child->getChildren();
                $ch = each($ch);

                if ($child->getType() != "{http://www.alfresco.org/model/site/1.0}sites" &&
                    (!$ch || $ch[1]->getType() != "{http://www.alfresco.org/model/site/1.0}site")) {
                    $array["data"][] = $this->parseResult($child, $columnSetId);
                }
            }
        }

        return $array;
    }

    public function parseNodeResult($node, $columnSetId=0) {
    	return $this->parseResult($node, $columnSetId=0);
    }
    
    public function columnPreperation($node,$jsonData,$user,$repository,$spacesStore,$session) {
    	$restDictionary = new RESTDictionary($repository,$spacesStore,$session);
    	$NamespaceMap = NamespaceMap::getInstance();
    	$resultArr = array();
    	foreach ($jsonData as $column) {
    		$internalName = $column->name;
    		$Label = $column->title;
    		$strKey = str_replace(":","_",$column->name);
    		$type = str_replace("d:","",$column->dataType);
    	
    		switch ($strKey) {
    			case "cm_type":
    				$typeTitle = "";
    				$type = $node->getType();
    				$shortType = $NamespaceMap->getShortName($type, "_");
    				try {
    					$typeInfo = $restDictionary->GetClassDefinitions($shortType);
    					$typeName = $typeInfo->name;
    					$typeTitle = $typeInfo->title;
    					if (empty($typeTitle))
    						$typeTitle = $typeName;
    				}
    				catch (Exception $e) {
    				}
    				$resultArr[$strKey] = $typeTitle;
    				break;
    			case "cm_name":
    				if ($node->getType() == "{http://www.alfresco.org/model/content/1.0}folder") {
    					$resultArr[$strKey] = '<img src="'.$node->getIconUrl().'" border="0" align="absmiddle" width=16> <b>'.$node->cm_name.'</b>';
    				} else {
    					$resultArr[$strKey] = '<img src="' . $node->getIconUrl() . '" border="0" align="absmiddle" width=16> '
    							. ($node->qshare_sharedId ?
    									'<img src="'.$SharedPng.'" border="0" align="absmiddle"> '
    									: '')
    									. $node->cm_name;
    				}
    				break;
    			default:
    				$propVal = "";
    				switch ($type) {
    					case "date":
    						$DateValue = $node->{$strKey};
    	
    						if (empty($DateValue))
    							$DateValue = "";
    						else
    							$DateValue = date($user->getDateFormat(), strtotime($node->{$strKey}));
    	
    						$propVal = $DateValue;
    	
    						break;
    					case "float":
    						if (!empty($node->{$strKey}))
    							$propVal = (float)$node->{$strKey};
    						else
    							$propVal = "";
    						break;
    					case "double":
    						if (!empty($node->{$strKey}))
    							$propVal = (double)$node->{$strKey};
    						else
    							$propVal = "";
    						break;
    					case "datetime":
    						$propVal = date($user->getDateFormat()." ".$user->getTimeFormat(),strtotime($node->{$strKey}));
    						break;
    					default:
    						$propVal = $node->{$strKey};
    						break;
    				}
    	
    				if ($this->metaRenderer != null) {
    					if (($valueRender = $this->metaRenderer->getPropertyRenderer($internalName)) != null
    					|| ($valueRender = $this->metaRenderer->getDataRenderer($type)) != null
    					|| ($valueRender = $this->metaRenderer->getClickSearchRenderer($internalName, $Label)) != null
    					) {
    						$propVal = $valueRender->render($propVal);
    					}
    				}
    	
    				$resultArr[$strKey] = $propVal;
    				break;
    		}
    	}
    	return $resultArr;
    }
    
    private function parseResult($node, $columnSetId=0) {
        $resultArr = array();
        $useDefault = true;

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();

        $RestNode = $node->getRestNodeItem();
        $permissions = $node->getPermissions();
        $isWorkingCopy = $node->isWorkingCopy();
        $isCheckedOut = $node->isCheckedOut();
        $workingCopyId = str_replace("workspace://SpacesStore/", "", $node->getWorkingCopy());
        $originalId = str_replace("workspace://SpacesStore/", "", $node->getCheckedoutOriginal());

        $realNodeId = $node->getId();
        if ($node->getType() == "{http://www.alfresco.org/model/application/1.0}filelink") {
            if (!empty($node->cm_destination)) {
                $tempNode = $node;
                $nodeRealId = str_replace("workspace://SpacesStore/","",$node->cm_destination);
                $node = NodeCache::getInstance()->getNode($this->session, $this->spacesStore, $nodeRealId);
                $node->cm_name = $tempNode->cm_name;
            }
        }

        $nodeId = $node->getId();

        $SharedPng = $this->container->get('templating.helper.assets')->getUrl("bundles/ifrescoclient/images/filetypes/shared.png");
        $searchColumnSet = null;
        $em = $this->getDoctrine()->getManager();
        if ($columnSetId > 0) {
        	$query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:SearchColumnSet', 's')
        	->where('s.id = :id')
        	->setParameter('id', $columnSetId);
        	 
        	$searchColumnSet = $query->getQuery()->getOneOrNullResult();
        }

        if ($searchColumnSet != null) {
                $jsonData = $searchColumnSet->getJsonFields();

                if (!empty($jsonData)) {
                    $jsonData = json_decode($jsonData);
                    $useDefault = false;
                    $contentData = $node->cm_content;
                    
                    $url = $node->getContentUrl();
                    $mimetype = $RestNode->item->mimetype;
                    
                    /*$url = "";
                    $mimetype = "";
                    if ($contentData != null) {
                        $url = $contentData->getUrl();
                        $mimetype = $contentData->getMimetype();
                    }*/
                    

                    $resultArr["alfresco_url"] = $url;
                    if ($this->setNodeOnParse == true) {
                        $resultArr["node"] = $node;
                    }

                    $resultArr["nodeRef"] = $realNodeId;
                    $resultArr["alfresco_mimetype"] = $mimetype;
                    $resultArr["alfresco_type"] = $node->getType();
                    $resultArr["alfresco_name"] = '<img src="' . $node->getIconUrl()
                        . '" border="0" align="absmiddle" width=16> '
                        . ($node->qshare_sharedId ? '<img src="'.$SharedPng.'" border="0" align="absmiddle"> ' : '')
                        . $node->cm_name
                    ;
                    $resultArr["alfresco_name_blank"] = $node->cm_name;
                    $resultArr["alfresco_perm_edit"] = $permissions ? $permissions->userAccess->edit : false;
                    $resultArr["alfresco_perm_delete"] = $permissions ? $permissions->userAccess->delete : false;
                    $resultArr["alfresco_perm_cancel_checkout"] = $permissions ? $permissions->userAccess->{"cancel-checkout"} : false;
                    $resultArr["alfresco_perm_create"] = $permissions ? $permissions->userAccess->create : false;
                    $resultArr["alfresco_perm_permissions"] = $permissions ? $permissions->userAccess->permissions : false;

                    $resultArr["alfresco_isWorkingCopy"] = $isWorkingCopy;
                    $resultArr["alfresco_isCheckedOut"] = $isCheckedOut;
                    $resultArr["alfresco_workingCopyId"] = $workingCopyId;
                    $resultArr["alfresco_originalId"] = $originalId;

                    /*$restDictionary = new RESTDictionary($this->repository, $this->spacesStore, $this->session);
                    $NamespaceMap = NamespaceMap::getInstance();

                    foreach ($jsonData as $column) {
                        $internalName = $column->name;
                        $Label = $column->title;
                        $strKey = str_replace(":","_",$column->name);
                        $type = str_replace("d:","",$column->dataType);

                        switch ($strKey) {
                            case "cm_type":
                                $typeTitle = "";
                                $type = $node->getType();
                                $shortType = $NamespaceMap->getShortName($type, "_");
                                try {
                                    $typeInfo = $restDictionary->GetClassDefinitions($shortType);
                                    $typeName = $typeInfo->name;
                                    $typeTitle = $typeInfo->title;
                                    if (empty($typeTitle))
                                        $typeTitle = $typeName;
                                }
                                catch (Exception $e) {
                                }
                                $resultArr[$strKey] = $typeTitle;
                                break;
                            case "cm_name":
                                if ($node->getType() == "{http://www.alfresco.org/model/content/1.0}folder") {
                                    $resultArr[$strKey] = '<img src="'.$node->getIconUrl().'" border="0" align="absmiddle" width=16> <b>'.$node->cm_name.'</b>';
                                } else {
                                    $resultArr[$strKey] = '<img src="' . $node->getIconUrl() . '" border="0" align="absmiddle" width=16> '
                                        . ($node->qshare_sharedId ?
                                            '<img src="'.$SharedPng.'" border="0" align="absmiddle"> '
                                            : '')
                                        . $node->cm_name;
                                }
                                break;
                            default:
                                $propVal = "";
                                switch ($type) {
                                    case "date":
                                        $DateValue = $node->{$strKey};

                                        if (empty($DateValue))
                                            $DateValue = "";
                                        else
                                            $DateValue = date($user->getDateFormat(), strtotime($node->{$strKey}));

                                        $propVal = $DateValue;

                                        break;
                                    case "float":
                                        if (!empty($node->{$strKey}))
                                            $propVal = (float)$node->{$strKey};
                                        else
                                            $propVal = "";
                                        break;
                                    case "double":
                                        if (!empty($node->{$strKey}))
                                            $propVal = (double)$node->{$strKey};
                                        else
                                            $propVal = "";
                                        break;
                                    case "datetime":
                                        $propVal = date($user->getDateFormat()." ".$user->getTimeFormat(),strtotime($node->{$strKey}));
                                        break;
                                    default:
                                        $propVal = $node->{$strKey};
                                        break;
                                }

                                if ($this->metaRenderer != null) {
                                    if (($valueRender = $this->metaRenderer->getPropertyRenderer($internalName)) != null
                                        || ($valueRender = $this->metaRenderer->getDataRenderer($type)) != null
                                        || ($valueRender = $this->metaRenderer->getClickSearchRenderer($internalName, $Label)) != null
                                    ) {
                                        $propVal = $valueRender->render($propVal);
                                    }
                                }

                                $resultArr[$strKey] = $propVal;
                                break;
                        }
                    }*/
                    $resultArr = array_merge($resultArr, $this->columnPreperation($node, $jsonData, $user, $this->repository, $this->spacesStore,$this->session));
                }
        }
        

        if ($useDefault == true) {

            $contentData = $node->cm_content;
            $url = $node->getContentUrl();
            $mimetype = $RestNode->item->mimetype;
            /*$url = "";
            $mimetype = "";
            if ($contentData != null) {
                $url = $contentData->getUrl();
                $mimetype = $contentData->getMimetype();
            }*/

            if ($node->getType() == "{http://www.alfresco.org/model/content/1.0}folder") {
                $name = '<img src="'.$node->getIconUrl().'" border="0" align="absmiddle" width=16> <b>'.$node->cm_name.'</b>';
            } else {
                $name = '<img src="'.$node->getIconUrl().'" border="0" align="absmiddle" width=16> '
                    . ($node->qshare_sharedId
                        ? '<img src="'.$SharedPng.'" border="0" align="absmiddle"> '
                        :'')
                    . $node->cm_name;
            }

            $resultArr = array(
                "nodeRef" => $realNodeId,
                "cm_name" => $name,
                "cm_creator" => $node->cm_creator,
                "cm_created" => date($user->getDateFormat() . " " . $user->getTimeFormat(),strtotime($node->cm_created)),
                "cm_modified" => date($user->getDateFormat() . " " . $user->getTimeFormat(),strtotime($node->cm_modified)),
                "alfresco_url" => $url,
                "alfresco_mimetype" => $mimetype,
                "alfresco_type" => $node->getType(),
                "alfresco_name" => '<img src="' . $node->getIconUrl() . '" border="0" align="absmiddle" width=16> '
                                . ($node->qshare_sharedId
                                    ? '<img src="'.$SharedPng.'" border="0" align="absmiddle"> '
                                    :'')
                                . $node->cm_name,
                "alfresco_name_blank" => $node->cm_name,
                "alfresco_perm_edit" => isset($permissions->userAccess->edit) ? $permissions->userAccess->edit : '',
                "alfresco_perm_delete" => isset($permissions->userAccess->delete) ? $permissions->userAccess->delete : '',
                "alfresco_perm_cancel_checkout" => isset($permissions->userAccess->{"cancel-checkout"}) ? $permissions->userAccess->{"cancel-checkout"} : '',
                "alfresco_perm_create" => isset($permissions->userAccess->{"cancel-checkout"}) ? $permissions->userAccess->{"cancel-checkout"} : '',
                "alfresco_perm_permissions" => isset($permissions->userAccess->permissions) ? $permissions->userAccess->permissions : '',
                "alfresco_isWorkingCopy" => $isWorkingCopy,
                "alfresco_isCheckedOut" => $isCheckedOut,
                "alfresco_workingCopyId" => $workingCopyId,
                "alfresco_originalId" => $originalId
            );

            if ($this->setNodeOnParse == true) {
                $resultArr["node"] = $node;
            }
        }

        $resultArr["alfresco_thumbnail"] = $this->generateUrl('ifresco_client_node_actions_image_preview', array(), true) . "?nodeId={$nodeId}";
        $resultArr["alfresco_thumbnail_medium"] = $this->generateUrl('ifresco_client_node_actions_image_preview', array(), true) . "?nodeId={$nodeId}&type=doclib";

        //$resultArr["alfresco_node_path"] = str_replace('/Company Home', '', $node->getFolderPath(true, true));
        $resultArr["alfresco_node_path"] = str_replace('/Company Home', '', $RestNode->item->location->path."/".$node->cm_name);
        $resultArr["nodeId"] = $nodeId;
        $resultArr["alfresco_is_inlineeditable"] = $node->hasAspect('app_inlineeditable');
        $resultArr["alfresco_sharedId"] = $node->qshare_sharedId;

        if ($this->addThumbs == true) {
            $resultArr["alfresco_thumbnail"] = $node->getImagePreviewUrl(true);

            $imageUrlOrg = $this->generateUrl('ifresco_client_node_actions_image_preview', array(), true)."?nodeId={$nodeId}";

            list($width, $height) = $this->imageDimensions($nodeId);

            $resultArr["alfresco_thumbnail"] = $imageUrlOrg;
            $resultArr["alfresco_thumbname"] = $node->cm_name;
            $resultArr["alfresco_thumbnail_width"] = $width;
            $resultArr["alfresco_thumbnail_height"] = $height;

        }

        return $resultArr;
    }

    private function imageDimensions($nodeId)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        try {
            $node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);

            if ($node != null) {
                $content = $node->cm_content;
                if ($content instanceof ContentData) {
                    $restContent = new RESTContent($repository, $spacesStore, $session);

                    $image = imagecreatefromstring($restContent->GetWebImagePreview($nodeId));
                    $width = imagesx($image);
                    $height = imagesy($image);

                    return array($width, $height);
                }
                else {
                    $imagePath = __DIR__ . "/../Resources/public/images/node-actions/ifresco-empty-preview.png";
                    $fileContent = file_get_contents($imagePath);

                    $image = imagecreatefromstring($fileContent);
                    $width = imagesx($image);
                    $height = imagesy($image);

                    return array($width, $height);
                }
            }

            throw new Exception();
        }
        catch (Exception $e) {
            return false;
        }
    }
    
    public function exportDownloadPDFResultSetAction(Request $request) {
    	$fileName = $request->get('fileName');
    	$fileName = str_replace("./","",$fileName);
    	$fileName = str_replace("../","",$fileName);
    
    	$response = new Response();
    
    	try {
    		$file = $this->get('kernel')->getCacheDir()."/".$fileName;
    		if (!file_exists($file))
    			throw new \Exception("file does not exists");
    
    		$response->headers->set('Content-Type','application/pdf; charset=utf-8');
    		//$this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    		//$this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    		//$this->getResponse()->setHttpHeader('Pragma','no-cache');
    		$response->headers->set('Content-Disposition','attachment; filename=Merge.pdf',false);
    		$content = file_get_contents($file);
    		$response->setContent($content);
    		//@unlink($file);
    		return $response;
    	}
    	catch (\Exception $e) {
    		$response->headers->set('Content-Type','text/html; charset=utf-8');
    		$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    		$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    		$response->headers->set('Pragma','no-cache');
    		$response->setContent($e->getMessage());
    		return $response;
    	}
    
    	return null;
    }
    
    public function exportDownloadCSVResultSetAction(Request $request) {
    	$fileName = $request->get('fileName');
    	$fileName = str_replace("./","",$fileName);
    	$fileName = str_replace("../","",$fileName);
    
    	$response = new Response();
    
    	try {
    		$file = $this->get('kernel')->getCacheDir()."/".$fileName;
    		if (!file_exists($file))
    			throw new \Exception("file does not exists");
    
    		$response->headers->set('Content-Type','text/csv; charset=utf-8');
    		$response->headers->set('Content-Disposition','attachment; filename=CSV-Export.csv',false);
    		$content = file_get_contents($file);
    		$response->setContent($content);
    		//@unlink($file);
    		return $response;
    	}
    	catch (\Exception $e) {
    		$response->headers->set('Content-Type','text/html; charset=utf-8');
    		$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    		$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    		$response->headers->set('Pragma','no-cache');
    		$response->setContent($e->getMessage());
    		return $response;
    	}
    
    	return null;
    }
    
    public function exportBackgroundPDFResultSetAction(Request $request) {
    	$Response = array("success"=>false);
    
    	$this->setNodeOnParse = true;
    	$gridData = $request->get('data', "[]");
    	$gridData = json_decode($gridData);
    	//FB::Log($gridData);
    
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$ticket = $user->getTicket();
    
    		$spacesStore = new SpacesStore($session);

    		if (count($gridData) > 0) {
    
    			$PDFMerge = new PDFMerge();
    			foreach ($gridData as $data) {
    				if (isset($data->node) && $data->alfresco_mimetype == "application/pdf") {
    					//$nodeId = $data["nodeId"];
    					//$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    					//$Node = $data->node;
    					$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $data->node);
    					if ($Node->getType() != "{http://www.alfresco.org/model/content/1.0}folder") {
    						$name = $Node->cm_name;
    						$content = $Node->cm_content;
    						if ($content instanceof ContentData) {
    							$mime = $content->getMimetype();
    							if ($mime == "application/pdf") {
    								if ($content != null) {
    									$tempFile = $this->get('kernel')->getCacheDir()."/".md5($name).".pdf";
    									$content->readContentToFile($tempFile);
    								}
    
    								$PDFMerge->addFile($tempFile);
    			
    							}
    
    						}
    					}
    				}
    				else if (isset($data->nodeId)) {
    					$nodeId = $data->nodeId;
    					$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
						foreach ($Node->getChildren() as $ChildNode) {
							//$ChildNode = $child->getNode();
							try {
		    					if ($ChildNode->getType() != "{http://www.alfresco.org/model/content/1.0}folder") {
		    						$name = $ChildNode->cm_name;
		    						$content = $ChildNode->cm_content;
		    						if ($content instanceof ContentData) {
		    							$mime = $content->getMimetype();
		    							
		    							if ($mime == "application/pdf") {
		    								if ($content != null) {
		    	
		    									$tempFile = $this->get('kernel')->getCacheDir()."/".md5($name).".pdf";
		    									$content->readContentToFile($tempFile);
		    									
		    								}
		    					
		    								$PDFMerge->addFile($tempFile);

	    									
		    							}
		    					
		    						}
		    					}
							}
							catch (\Exception $e) {

							}
						}
    				}
    				unset($content);
    			}
    
    			$PDFMerge->merge();
    
    			$outFile = "Merge".date("d-m-Y-H-i").".pdf";
    			$PDFMerge->Output($this->get('kernel')->getCacheDir()."/".$outFile);
    			$PDFMerge->UnlinkFiles();
    			
    		}
    		else
    			throw new \Exception("nothing to write");
    
    		$Response["success"] = true;
    		$Response["fileName"] = $outFile;
    	}
    	catch (\Exception $e) {
    		$Response["message"] = $e->getMessage();
    		$Response["success"] = false;
    	}
    
    	$response = new JsonResponse($Response);
    	return $response;
    }
    
    public function testPDFAction() {
    	echo "<pre>";
    	$Pdf = \ZendPdf\PdfDocument::load("/tmp/test/Testseite.pdf");
    	//file_put_contents("/tmp/test/Testseite.txt", print_r($Pdf,true));
    	$fpdf = new \Ifresco\ClientBundle\Component\Alfresco\Lib\FPDFI\FPDI('P','mm','A4');
    	$fpdf->SetDisplayMode("default","single");
    	$fpdf->setSourceFile("/tmp/test/Testseite3.pdf");
    	$tplidx = $fpdf->importPage(1);

		$fpdf->addPage();
		$fpdf->useTemplate($tplidx);

    	file_put_contents("/tmp/test/Fpdf.txt", print_r($fpdf,true));
    	$tcpdf = \TCPDF_IMPORT::importPDF("/tmp/test/Testseite3.pdf");
    	file_put_contents("/tmp/test/tcpdf.txt", print_r($tcpdf,true));
		$data = file_get_contents("/tmp/test/data.txt");
		$un = gzuncompress($data);
		file_put_contents("/tmp/test/data.docx", $un);
   		die("hallo");
    }
    
    // TODO validate if still used otherwise remove
    /*public function exportPDFResultSetAction(Request $request) {
    	$this->setNodeOnParse = true;
    	$gridData = $this->parseRequestData($request,0);
    
    	try {
    		$user = $this->getUser();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$ticket = $user->getTicket();
    
    		$spacesStore = new SpacesStore($session);
    
    		if (count($gridData["data"]) > 0) {
    
    			$PDFMerge = new PDFMerge();
    
    
    			foreach ($gridData["data"] as $data) {
    
    				if (isset($data["node"]) && $data["alfresco_mimetype"] == "application/pdf") {
    					//$nodeId = $data["nodeId"];
    					//$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    					$Node = $data["node"];
    					if ($Node->getType() != "{http://www.alfresco.org/model/content/1.0}folder") {
    						$name = $Node->cm_name;
    						$content = $Node->cm_content;
    						if ($content instanceof ContentData) {
    							$mime = $content->getMimetype();
    							if ($mime == "application/pdf") {
    								if ($content != null) {
    									$tempFile = sfConfig::get('sf_cache_dir')."/".md5($name).".pdf";
    									$content->readContentToFile($tempFile);
    								}
    
    								$PDFMerge->addFile($tempFile);
    							}
    
    						}
    					}
    				}
    
    			}
    
    			$PDFMerge->merge();
    
    			$PDFMerge->Output("Merge.pdf","D");
    			$PDFMerge->UnlinkFiles();
    
    			die();
    		}
    	}
    	catch (Exception $e) {
    		$this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    		$this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    		$this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    		$this->getResponse()->setHttpHeader('Pragma','no-cache');
    		$return = json_encode(array("success"=>false));
    		return $this->renderText($return);
    	}
    
    
    	return null;
    }*/
    
    public function exportResultSetAction(Request $request) {
    	$Response = array("success"=>false);
    	
    	//$gridData = $request->get('data', "[]");
    	//$gridData = json_decode($gridData);
    	
    	
    	//$gridDataResponse = $this->get("ifresco_client_grid.results")->gridDataAction($request);
    	
    	$gridDataResponse = $this->gridDataAction($request);
    	$gridDataResponse = json_decode($gridDataResponse->getContent());
    	$gridData = $gridDataResponse->data;

    	try {
    
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$spacesStore = new SpacesStore($session);
	    
	    	$RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
	    	$Props = $RestDictionary->GetAllProperties();
	    
	    	$propsArr = array();
	    
	    	foreach($Props as $prop) {
	    		if(isset($prop->title))
	    			$propsArr[str_replace(':', '_', $prop->name)] = utf8_decode($prop->title);
	    	}
	    
	    	$user = $this->get('security.context')->getToken();
	    
	    	$skip = array("alfresco_type",
	    			"nodeRef",
	    			"alfresco_name",
	    			"alfresco_name_blank",
	    			"alfresco_isWorkingCopy",
	    			"alfresco_isCheckedOut",
	    			"alfresco_workingCopyId",
	    			"alfresco_originalId",
	    			"alfresco_perm_edit",
	    			"alfresco_perm_delete",
	    			"alfresco_perm_cancel_checkout",
	    			"alfresco_perm_create",
	    			"alfresco_perm_permissions",
	    			"alfresco_thumbnail",
	    			"alfresco_thumbnail_medium",
	    			"alfresco_is_inlineeditable",
	    			"alfresco_sharedId",
	    			"nodeId"
	    	);
	    
	    	$csvList = "";
	    	$csvColumns = array();
	    	$csvColumnsFound = false;
	    	$list = array();
	    	if (count($gridData) > 0) {
	    		foreach ($gridData as $data) {

	    			$csvFields = array();
	    			$tempList = "";
	    			$found = true;
	    			foreach ($data as $key => $value) {
	    				if (in_array($key,$skip))
	    					continue;
	    
	    				if ($key == "alfresco_url") {
	    					if (empty($value)) {
	    						$found = false;
	    					}
	    				}
	    
	    				if ($csvColumnsFound == false)
	    					$csvColumns[$key] = isset($propsArr[$key]) ? $propsArr[$key]. '('.$key.')' : $key;
	    
	    				$newValue = trim(strip_tags($value));
	    				$csvFields[$key] = utf8_decode($newValue);
	    			}
	    			
	    			if ($csvColumnsFound==false) {
	    				//ksort($csvColumns);
	    				$csvList .= join(";",$csvColumns)."\n";
	    				$csvColumnsFound = true;
	    				$list[] = $csvColumns;
	    			}

	    			if ($data->alfresco_type == "{http://www.alfresco.org/model/content/1.0}folder") {
	    				if (!empty($data->nodeId)) {
	    					$list = $this->csvRecursiveFolder($data->nodeId,$request,$skip,$list);
	    				}
	    				$found = false;
	    			}
	    
	    			
	    
	    			if ($found) {
	    				//sort($csvFields);
	    				$list[] = $csvFields;
	    				$csvList .= join(";",$csvFields)."\n";
	    				
	    			}
	    
	    			if (!empty($tempList))
	    				$csvList .= $tempList;
	    		}
	    	}

	    	
	    	
	    	$outFile = "CSV-Export".date("d-m-Y-H-i").".csv";
	    	$TmpFile = $this->get('kernel')->getCacheDir()."/".$outFile;
    	
    		/*file_put_contents($TmpFile, $csvList);*/
	    	
	    	$fp = fopen($TmpFile, 'w');
	    	
	    	foreach ($list as $fields) {
	    		fputcsv($fp, $fields, ";");
	    	}
	    	
	    	fclose($fp);
    		
    		/*print_R(file_get_contents($TmpFile));
    		echo $TmpFile;
    		die();

    		file_put_contents($TmpFile, "\xEF\xBB\xBF".$csvList);*/
    	
    		$Response["success"] = true;
    		$Response["fileName"] = $outFile;
    	}
    	catch (\Exception $e) {
    		$Response["message"] = $e->getMessage();
    		$Response["success"] = false;
    	}
    

    	$response = new JsonResponse($Response);
    	return $response;
    
    	/*$response = new Response("\xEF\xBB\xBF".$csvList);
    
    	$response->headers->set('Content-Type','text/csv; charset=utf-8');
    	$response->headers->set('Content-Disposition','attachment; filename=Export_Result_'.$user->getUsername().'_'.date('Y-m-d-H-i-s').'.csv',false);
    
    	return $response;*/
    }
    
    private function csvRecursiveFolder($nodeId, Request $request,$skip,$list) {
    	$columnsetid = $request->get('columnSetId');

    	$csvList = "";
    	$gridData = $this->documentList($nodeId,$columnsetid);
    	if (count($gridData["data"]) > 0) {
    		foreach ($gridData["data"] as $data) {
    			$csvFields = array();
    			$tempList = "";
    			$found = true;
    			foreach ($data as $key => $value) {
    				if (in_array($key,$skip))
    					continue;
    
    				if ($key == "alfresco_url") {
    					if (empty($value)) {
    						$found = false;
    					}
    				}
    
    				$newValue = trim(strip_tags($value));
    				$csvFields[$key] = utf8_decode($newValue);
    			}
    			
    			$data = (object)$data;

    			if ($data->alfresco_type == "{http://www.alfresco.org/model/content/1.0}folder") {
    				if (!empty($data->nodeId)) {
    					$list = $this->csvRecursiveFolder($data->nodeId,$request,$skip,$list);
    				}
    				$found = false;
    			}
    
    			if ($found) {
    				$list[] = $csvFields;
    				$csvList .= join(";",$csvFields)."\n";
    			}
    
    			if (!empty($tempList))
    				$csvList .= $tempList;
    		}
    	}
    
    
    	return $list;
    }
    
    public function getColumnsAction($id)
    {
    	$columns = array();
    	$fields = array();

    	$em = $this->getDoctrine()->getManager();
    	
    	$user = $this->get('security.context')->getToken();
    	$session = $user->getSession();
    	/**
    	 * @var SearchColumnSet $SearchColumnSet
    	*/
    	$SearchColumnSet = $em->getRepository('IfrescoClientBundle:SearchColumnSet')->find($id);
    	$ColumnFieldsArray = array();
    	$ColumnArray = array();
    	$Name = "";
    	if ($SearchColumnSet) {
    		$Name = $SearchColumnSet->getName();
    		$jsonData = $SearchColumnSet->getJsonFields();

    			$DefaultSort = "";
    			$DefaultSortDir = "";
    			if (!empty($jsonData)) {
    				$jsonData = json_decode($jsonData);
    		
    				$useDefault = false;
    				$ColumnFieldsArray["alfresco_url"] = array("type"=>"string");
    				$ColumnFieldsArray["nodeRef"] = array("type"=>"string");
    				$ColumnFieldsArray["nodeId"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_mimetype"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_type"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_name"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_name_blank"] = array("type"=>"string");
    		
    				$ColumnFieldsArray["alfresco_perm_edit"] = array("type"=>"boolean");
    				$ColumnFieldsArray["alfresco_perm_delete"] = array("type"=>"boolean");
    				$ColumnFieldsArray["alfresco_perm_cancel_checkout"] = array("type"=>"boolean");
    				$ColumnFieldsArray["alfresco_perm_create"] = array("type"=>"boolean");
    				$ColumnFieldsArray["alfresco_perm_permissions"] = array("type"=>"boolean");
    		
    				$ColumnFieldsArray["alfresco_isWorkingCopy"] = array("type"=>"boolean");
    				$ColumnFieldsArray["alfresco_isCheckedOut"] = array("type"=>"boolean");
    				$ColumnFieldsArray["alfresco_workingCopyId"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_originalId"] = array("type"=>"string");
    		
    				$ColumnFieldsArray["alfresco_thumbnail"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_thumbnail_medium"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_thumbname"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_thumbnail_width"] = array("type"=>"string");
    				$ColumnFieldsArray["alfresco_thumbnail_height"] = array("type"=>"string");
    		
    				foreach ($jsonData as $key => $column) {
    					$strKey = str_replace(":","_",$column->name);
    					$type = str_replace("d:","",$column->dataType);
    					$ColumnFieldsArray[$strKey] = array("type"=>"string");
    		
    					$ColumnArray[$strKey] = array(
    							"header"=>$column->title,
    							"width"=>80,
    							"flex"=>1,
    							"sortable"=>"true",
    							"hideable"=>"true",
    							"groupable"=>"true"
    					);
    		
    					switch($strKey) {
    						case 'cm_name':
    							$ColumnArray[$strKey]['groupable'] = false;
    							break;
    					}
    		
    		
    					if ($column->sort == true) {
    						$DefaultSort = $strKey;
    						if ($column->asc == true)
    							$DefaultSortDir = "ASC";
    						else
    							$DefaultSortDir = "DESC";
    					}
    					switch ($type) {
    						case "date":
    							$ColumnFieldsArray[$strKey]["type"] = "date";
    							$ColumnFieldsArray[$strKey]["dateFormat"] = $user->getDateFormat();
    		
    							$ColumnArray[$strKey]["renderer"] = "Ext.util.Format.dateRenderer('".$user->getDateFormat()."')";
    							break;
    						case "datetime":
    							$ColumnFieldsArray[$strKey]["type"] = "date";
    							$ColumnFieldsArray[$strKey]["dateFormat"] = $user->getDateFormat()." ".$user->getTimeFormat();
    		
    							$ColumnArray[$strKey]["renderer"] = "Ext.util.Format.dateRenderer('"
    									. $user->getDateFormat() . " " . $user->getTimeFormat()."')";
    							break;
    						default:
    							break;
    					}
    		
    					if ($column->hide == true)
    						$ColumnArray[$strKey]["hidden"] = "true";
    				}
    			}
			$fields = $this->parseFields($ColumnFieldsArray);
    		$columns = $this->parseColumns($ColumnArray);
    	}
    
    	return new JsonResponse(array(
    			'success' => true,
    			'name'=> $Name,
    			'columns' => $columns,
    			'fields' => $fields
    	));
    }
    
    public function pasteClipboardAction(Request $request) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    	$spacesStore = new SpacesStore($session);
    
    	$items = $request->get('clipboardItems');
    	$actionType = $request->get('actionType');
    	$destNodeId = $request->get('destNodeId');
    	if (!empty($items) && !empty($actionType) && !empty($destNodeId)) {
    
    		$items = json_decode($items);
    		foreach ($items as $key=>$value) {
    			$items[$key] = "workspace://SpacesStore/".$value;
    		}
    		$return = array("success"=>"false");
    
    		$RestTransport = new RESTTransport($repository,$spacesStore,$session);
    
    		if ($actionType == "cut")
    			$return = $RestTransport->MoveTo($destNodeId,$items);
    		else if ($actionType == "link") {
    			$return = new \stdClass;
    			$return->totalResults = count($items);
    			$return->successCount = 0;
    			$return->failureCount = 0;
    			try {
    				$DestNode = NodeCache::getInstance()->getNode($session, $spacesStore, $destNodeId);
    				if ($DestNode == null)
    					throw new \Exception();
    
    
    				foreach ($items as $item) {
    					try {
    						$itemNodeId = str_replace("workspace://SpacesStore/","",$item);
    						//{http://www.alfresco.org/model/application/1.0}filelink
    						$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $itemNodeId);
    						if ($Node == null || trim($Node->cm_name) == '')
    							throw new \Exception();
    						$FileName = $Node->cm_name.".url";
    
    						$contentNode = $DestNode->createChild("app_filelink", "cm_contains", "cm_".$FileName);
    						$contentNode->cm_name = "Link to ".$FileName;
    						$contentNode->cm_title = "Link to ".$Node->cm_name;
    						$contentNode->cm_destination = $Node->__toString();
    
    						$contentData = $contentNode->setContent("cm_content", "application/link", "UTF-8");
    						$contentData->setContent($item);
    
    						$return->successCount++;
    					}
    					catch (\Exception $e) {
    						$return->failureCount++;
    					}
    				}
    
    				$session->save();
    
    				$return->overallSuccess = true;
    				$return->success = true;
    			}
    			catch (\Exception $e) {
    				$return->overallSuccess = false;
    				$return->success = false;
    				$return->failureCount = $return->totalResults;
    			}
    
    		}
    		else
    			$return = $RestTransport->CopyTo($destNodeId,$items);
    
    		if (!empty($return->overallSuccess))
    			$return->success = $return->overallSuccess;
    
    		return new JsonResponse($return);
    	}
    }
}
