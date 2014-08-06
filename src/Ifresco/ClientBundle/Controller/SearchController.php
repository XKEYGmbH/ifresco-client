<?php

namespace Ifresco\ClientBundle\Controller;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Ifresco\ClientBundle\Component\Alfresco\Lib\CategoryCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\FunctionCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDictionary;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTPerson;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\SugarCRM\SugarWrapper;
use Ifresco\ClientBundle\Entity\Lookup;
use Ifresco\ClientBundle\Entity\SavedSearch;
use Ifresco\ClientBundle\Entity\SearchTemplate;
use Ifresco\ClientBundle\Entity\Setting;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTAspects;

class SearchController extends Controller
{
    private $importedClasses = array();
    private $restPerson;
    private $spacesStore;
    private $session;

    public function getTemplatesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $templates = $em->getRepository('IfrescoClientBundle:SearchTemplate')->findAll();
        $templateArray = array("templates" => array());

        /**
         * @var SearchTemplate $template
         */
        foreach ($templates as $template) {
            $templateArray["templates"][] = array(
                "id" => $template->getId(),
                "name" => $template->getName(),
                "columnSetId" => $template->getColumnSetId(),
                "isDefaultView" => $template->getIsDefaultView()
            );
        }

        $response = new JsonResponse($templateArray);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getSearchTemplateDataAction(Request $request)
    {
        /**
         * @var User $user
         * @var SearchTemplate $searchTemplate
         */
        $template = $request->get('id');
        $cc = $request->get('cc');
        $nodeId = null;
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $this->session = $user->getSession();
        $this->spacesStore = new SpacesStore($this->session);
        $restDict = new RESTDictionary($repository, $this->spacesStore, $this->session);
        $this->restPerson = new RESTPerson($repository, $this->spacesStore, $this->session);
        $searchTemplate = null;
        $metaDataResult = array();

        if ($cc == "true") {
            CategoryCache::getInstance($user)->clean();
        }

        $em = $this->getDoctrine()->getManager();
        if (!$template || empty($template) || !is_numeric($template)) {
            $searchTemplate = $em->getRepository('IfrescoClientBundle:SearchTemplate')->findOneBy(array(
                'is_default_view' => 1
            ));

            if (!$searchTemplate) {
                $searchTemplate = new SearchTemplate();
                $searchTemplate->setIsDefaultView(false);
                $searchTemplate->setColumnSetId(0);
                $searchTemplate->setShowDoctype(array());
                $searchTemplate->setIsMulticolumn(false);
                $searchTemplate->setJsondata(
                    '{"Column1":[{"name":"cm:name","class":"cm:content","dataType":"d:text","title":"' .
                    $this->get('translator')->trans("Name") . '","type":"property"}],"Column2":[],"Tabs":[]}'
                );
            }
        } else {
            $searchTemplate = $em->getRepository('IfrescoClientBundle:SearchTemplate')->find($template);
        }

        if ($searchTemplate) {
            $jsonData = $searchTemplate->getJsonData();
            
            if (!empty($jsonData)) {
                $jsonData = json_decode($jsonData);
                $tabs = $jsonData->Tabs;
                $usedFields = array();

                $metaDataResult = array(
                    "success" => false,
                    "data" => array(
                        "fields" => array(),
                        "tabs" => array(),
                        "config" => array(
                            "templateId" => $searchTemplate->getId(),
                            "savedSearchId" => $searchTemplate->getSavedSearchId(),
                        	"contentType" => $searchTemplate->getContentType()
                        )
                    )
                );

                $metaDataArray = array();
                $columns = array();
                $tempColumns = array(1 => $jsonData->Column1, 2 => $jsonData->Column2);

                foreach ($tempColumns as $columnNumber => $column) {
                    foreach ($column as $value) {
                    	if ($value->type == "property") {
	                        $columns[] = array(
	                            'class' => $value->class,
	                            'dataType' => $value->dataType,
	                            'label' => $value->title,
	                            'name' => $value->name,
	                            'column' => $columnNumber
	                        );
                    	}
                    }
                }


                foreach ($columns as $column) {
                    $name = $column['name'];
                    if (in_array($name, $usedFields)) {
                        continue;
                    }

                    $class = $column['class'];
                    $label = $column['label'];
                    
                    if("custom-field" != $class) {
                        $this->importClassForm($class, $restDict);
                        $field = $this->renderField($name, $column['dataType'], $label, $class);
                        $field['0']['column'] = $column['column'];
                        $metaDataArray = array_merge($metaDataArray, $field);
                        $usedFields[] = $name;
                    } else {
                        $num = str_replace('custom-field-control', '', $name);
                        $data = $jsonData->customFields[$num];
                        $label = $data->custom_field_lable;

                        $mdStr = array();
                        if(count($data->customFieldValues)) {
//                            $mdStr = array();
                            foreach($data->customFieldValues as $cValue) {
                                $mdStr[] = $cValue->name;
                            }
                            sort($mdStr);
                            $mdStr = md5(implode('', $mdStr));
                        }

                        $newRendered = $this->renderField($mdStr, 'd:text', $label, $class);
                        $newRendered[0]['name'] = $newRendered[0]['id'] = $name;
                        $newRendered['0']['column'] = $column['column'];
                        $metaDataArray = array_merge($metaDataArray, $newRendered);
                    }
                }

                $luceneField = $this->renderField('lucene_query', 'hidden', 'lucene_query', 'property');
                $metaDataArray = array_merge($metaDataArray, $luceneField);

                $customSearchField = $this->renderField('custom_search', 'hidden', 'custom_search', 'property');
                $metaDataArray = array_merge($metaDataArray, $customSearchField);

                $customQueryModeField = $this->renderField('customQueryMode', 'hidden', 'customQueryMode', 'property');
                $metaDataArray = array_merge($metaDataArray, $customQueryModeField);

                $tabArray = array();

                if (count($tabs) > 0) {
                    $realTabs = $tabs;
                    if (count($realTabs) > 0) {

                        foreach ($realTabs as $tabValues) {
                            $title = $tabValues->title;
                            $items = $tabValues->items;
                            $itemsArray = array();

                            foreach ($items as $itemValue) {
                                $split = explode("/", $itemValue);
                                if (count($split) > 0) {

                                    $name = $split[0];
                                    if (in_array($name, $usedFields)) {
                                        continue;
                                    }

                                    $class = $split[1];
                                    $dataType = $split[3];
                                    $label = $split[2];

                                    if("custom-field" != $class) {
                                        $this->importClassForm($class, $restDict);
                                        $itemsArray = array_merge(
                                            $itemsArray,
                                            $this->renderField($name, $dataType, $label, $class)
                                        );
                                        $usedFields[] = $name;
                                    } else {
                                        $num = str_replace('custom-field-control', '', $name);
                                        $data = $jsonData->customFields[$num];
                                        $label = $data->custom_field_lable;
                                        $itemsArray = array_merge(
                                            $itemsArray,
                                            $this->renderField($name, 'd:text', $label, $class)
                                        );
                                    }
                                }
                            }

                            if (count($itemsArray) > 0) {
                                $tabArray[] = array(
                                    "title" => $title,
                                    "fields" => $itemsArray
                                );
                            }
                        }
                    }
                }

                $showDoctypeMaster = $searchTemplate->getShowDoctype();

                if (!empty($showDoctypeMaster)) {
                    $jsonDoc = json_decode($showDoctypeMaster);

                    if (is_array($jsonDoc)) {
                        foreach ($jsonDoc as $showDoctype) {
  							if (is_array($showDoctype) || empty($showDoctype) || $showDoctype == "[]")
  								continue;
  							
                            $class = str_replace(":", "_", $showDoctype);
                            $classDetails = $restDict->GetClassDefinitions($class);
                            $this->importClassForm($class, $restDict);

                            if ($classDetails != null) {

                                $properties = $classDetails->properties;
                                $associations = $classDetails->associations;
                                $title = $classDetails->title;

                                $itemsArray = array();
                                if (count($properties) > 0) {

                                    foreach ($properties as $key => $value) {
                                        $propName = str_replace(":", "_", $key);
                                        $property = $restDict->GetClassProperty($class, $propName);
                                        if ($property != null) {
                                            $name = $property->name;

                                            if (in_array($name, $usedFields)) {
                                                continue;
                                            }

                                            $dataType = $property->dataType;
                                            $label = isset($property->title) ? $property->title : '';

                                            $itemsArray = array_merge(
                                                $itemsArray,
                                                $this->renderField($name, $dataType, $label, $class)
                                            );
                                            $usedFields[] = $name;
                                        }
                                    }
                                }

                                if (count($associations) > 0) {
                                    foreach ($associations as $key => $value) {
                                        $assocName = str_replace(":", "_", $key);
                                        $assoc = $restDict->GetClassAssociation($class, $assocName);
                                        if ($assoc != null) {
                                            $name = $assoc->name;

                                            if (in_array($name, $usedFields) || count($assoc->target) < 1) {
                                                continue;
                                            }

                                            $usedFields[] = $name;
                                        }
                                    }
                                }

                                if (count($itemsArray) > 0) {
                                    $tabArray[] = array(
                                        "title" => $title,
                                        "fields" => $itemsArray
                                    );
                                }
                            }
                        }
                    }
                }

                if (count($tabArray) > 0) {
                    $metaDataResult["data"]["tabs"] = $tabArray;
                }

                $metaDataResult["data"]["fields"] = $metaDataArray;
                $metaDataResult["success"] = true;
                

            }
        }

        $response = new JsonResponse($metaDataResult);
        $response->headers->set('Content-Type','application/json; charset=utf-8');
        $response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
        $response->headers->set('Pragma','no-cache');
        return $response;
    }

    public function saveSearchAction(Request $request)
    {
        $json = array();
        $json['jsonrpc'] = "2.0";

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $searchName = $request->get('searchName');
        $searchPrivacy = $request->get('searchPrivacy') === 'true';
        $template = $request->get('template');
        $data = $request->get('data');

        $message = $this->get('translator')->trans("Something went wrong. Please contact the Administrator!");
        $em = $this->getDoctrine()->getManager();
        try {
            if (empty($data)) {
                throw new \Exception("no data given!");
            }

            if ($template == null) {
                $template = 0;
            }

            if (empty($searchName)) {
                $message = $this->get('translator')->trans("Please provide a name for this search!");
                throw new \Exception("");
            }

            $saveSearch = new SavedSearch();
            $saveSearch->setName($searchName);
            $saveSearch->setIsPrivacy($searchPrivacy);
            $saveSearch->setData($data);
            $saveSearch->setUser($user->getUsername());
            $saveSearch->setTemplate($template);
            $em->persist($saveSearch);
            $em->flush();

            $json["success"] = true;
            $json["message"] = $this->get('translator')->trans("Successfully saved the search '%1%'" , array("%1%" => $searchName));
        } catch (\Exception $e) {
            $json["success"] = false;
            $json["message"] = $message;
        }

        $response = new JsonResponse($json);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    public function getSavedSearchAction(Request $request)
    {
        /**
         * @var User $user
         * @var SavedSearch $search
         */
        $user = $this->get('security.context')->getToken();
        $em = $this->getDoctrine()->getManager();

        try {
            $savedSearches = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:SavedSearch', 's')
                ->where('s.user = :username')
                ->orWhere('s.is_privacy = 1')
                ->setParameter('username', $user->getUsername())
                ->getQuery()
                ->getResult()
            ;
        } catch (NoResultException $e) {
            $savedSearches = null;
        }
        $array = array();

        if ($savedSearches != null) {
            foreach ($savedSearches as $search) {
            	$data = $search->getData();
                $array[] = array(
                    "cls" => "file",
                    "id" => $search->getId(),
                    "checked" => false,
                    "leaf" => true,
                    "data" => json_decode($search->getData()),
                    "privacy" => $search->getIsPrivacy(),
                    "isSelf" => $search->getUser() == $user->getUsername(),
                    "template" => $search->getTemplate(),
                    "name" => $search->getName()
                );
            }
        }

        return new JsonResponse($array);
    }

    public function deleteSavedSearchAction(Request $request)
    {
        $json = array();
        $json['jsonrpc'] = "2.0";

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $data = $request->get('data');

        $message = $this->get('translator')->trans("Something went wrong. Please contact the Administrator!");
        $em = $this->getDoctrine()->getManager();
        try {
            if (empty($data)) {
                $message = $this->get('translator')->trans("Please seletect at least one to delete!");
                throw new \Exception("");
            }
            $data = json_decode($data);
            if (count($data) > 0) {
                foreach ($data as $entry) {
                    $em->createQueryBuilder()->delete()->from('IfrescoClientBundle:SavedSearch', 's')
                        ->where('s.id = :id')
                        ->andWhere('s.user = :username')
                        ->setParameter('username', $user->getUsername())
                        ->setParameter('id', $entry)
                        ->getQuery()
                        ->execute()
                    ;
                }
                $json["success"] = "true";
                $json["message"] = $this->get('translator')->trans("Successfully deleted the selected searches" , array());
            } else {
                $message = $this->get('translator')->trans("Please seletect at least one to delete!");
                throw new \Exception("");
            }

        } catch (\Exception $e) {
            $json["success"] = "false";
            $json["message"] = $message;
            $json["error"] = $e->getMessage();
        }

        $response = new JsonResponse($json);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    private function getLookupForField($name,$dataKeyName,$label) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$spacesStore = new SpacesStore($session);
    	
    	$restAspects = new RESTAspects($repository, $spacesStore, $session);
    	
    	$restDict = new RESTDictionary($repository, $spacesStore, $session);
    	$restPerson = new RESTPerson($repository, $spacesStore, $session);
    	
    	$em = $this->getDoctrine()->getManager();
    	
    	$query = $em->createQueryBuilder()->select('l')->from('IfrescoClientBundle:Lookup', 'l')
    	->where(
    			'l.field = :field AND (l.apply_to = 0) AND '.
    			'(l.type = :type1 OR l.type = :type2 OR l.type = :type3 OR l.type = :type4 OR l.type = :type5)'
    	)
    	->setParameter('field', $name)
    	->setParameter('type1', "category")
    	->setParameter('type2', "user")
    	->setParameter('type3', "datasource")
    	->setParameter('type4', "datasourcerel")
    	->setParameter('type5', "sugar");
    	$lookupSearch = $query->getQuery()->getOneOrNullResult();
    	$foundLookup = false;
    	
    	if ($lookupSearch != null) {
    		$LookupType = $lookupSearch->getType();
    		$single = $lookupSearch->getIsSingle();
    		$params = $lookupSearch->getParams();
    	
    		if ($LookupType == "category") {
    			$catId = $lookupSearch->getFieldData();
    			$categoryNode = $this->session->getNode($this->spacesStore, $catId);
    			$categoryPathRefs = array_reverse($categoryNode->getRealPathRefs());
    	
    			$categoryPath = '';
    			for($refCounter = 1; $refCounter < count($categoryPathRefs); $refCounter++) {
    				$categoryPath .= NodeCache::getInstance()
    				->getNode($this->session, $this->spacesStore, $categoryPathRefs[$refCounter])
    				->cm_name . '/'
    						;
    			}
    	
    			$categoryPath = substr($categoryPath, 0, strlen($categoryPath) - 1);
    			$categories = CategoryCache::getInstance($this->get('security.context')->getToken())
    			->getCachedCategories($categoryPath);
    	
    			if (count($categories->items) > 0) {
    				$foundLookup = true;
    				$choices = array();
    	
    				foreach ($categories->items as $listValue) {
    					$catName = preg_replace("/\{.*?\}/is", "", $listValue->name);
    					if (!empty($choicesList)) {
    						$choicesList .= ",";
    					}
    					$choices[] = $catName;
    				}
    	
    				sort($choices);
    				$metaDataArray[] = array(
    						"name" => $dataKeyName,
    						"fieldLabel" => $label,
    						"store" => $choices,
    						"type" => $single ? "combo" : "superboxselect"
    				);
    			}
    		} else if ($LookupType == "user") {
    			$users = NodeCache::getInstance()->getRestPeoples($this->restPerson);
    			$setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
    					'key_string' => 'UserLookupLabel'
    			));
    			$userLabel = "%firstName% %lastName% (%email%)";
    			if ($setting != null) {
    				$userLabel = $setting->getValueString();
    			}
    	
    			$choices = array();
    			if ($users != null) {
    				$users = $users->people;
    				if (count($users) > 0) {
    					$foundLookup = true;
    					foreach ($users as $listValue) {
    						$userLabelTemp = $userLabel;
    	
    						$userLabelTemp = str_replace("%firstName%", $listValue->firstName, $userLabelTemp);
    						$userLabelTemp = str_replace("%firstname%", $listValue->firstName, $userLabelTemp);
    						$userLabelTemp = str_replace("%lastName%", $listValue->lastName, $userLabelTemp);
    						$userLabelTemp = str_replace("%lastname%", $listValue->lastName, $userLabelTemp);
    						$userLabelTemp = str_replace("%email%", $listValue->email, $userLabelTemp);
    						$userLabelTemp = str_replace("%userName%", $listValue->userName, $userLabelTemp);
    						$userLabelTemp = str_replace("%username%", $listValue->userName, $userLabelTemp);
    						$name = trim($userLabelTemp);
    						$name = str_replace("  ", " ", $name);
    						if (!empty($choicesList)) {
    							$choicesList .= ",";
    						}
    						$choices[] = $name;
    					}
    	
    					sort($choices);
    	
    					$metaDataArray[] = array(
    							"name" => $dataKeyName,
    							"fieldLabel" => $label,
    							"store" => $choices,
    							"type" => $single ? "combo" : "superboxselect"
    					);
    				}
    			}
    		} else if ($LookupType == "sugar") {
    			$fieldData = $lookupSearch->getFieldData();
    			list($source, $table, $column) = explode('/', $fieldData);
    			$query = $em->createQueryBuilder()->select('d')->from('IfrescoClientBundle:DataSource', 'd')
    			->where('d.id = :id')
    			->setParameter('id', $source);
    			$dataSources = $query->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
    	
    			$url = $dataSources['host'];
    			$url = rtrim($url, "/");
    			$url .= "/service/v3/rest.php";
    			$username = $dataSources['username'];
    			$password = $dataSources['password'];
    	
    			$wrapper = new SugarWrapper($url, $username, $password);
    			$error = $wrapper->get_error();
    	
    			if(is_bool($error) && $error == true) {
    				$metaDataArray[] = array(
    						"name" => $dataKeyName,
    						"fieldLabel" => $label,
    						"type" => 'sugar',
    						"url" => $this->get('router')->generate('ifresco_client_metadata_sugar_lookup_rows_get') .
    						'?source=' . $lookupSearch->getId(),
    				);
    				$foundLookup = true;
    			}
    		} else if ($LookupType == "datasource" || $LookupType == "datasourcerel") {
    			$fieldData = $lookupSearch->getFieldData();
    			if($LookupType == "datasourcerel") {
    				$source = $fieldData;
    				$table = $column = '';
    			} else {
    				list($source, $table, $column) = explode('/', $fieldData);
    			}
    	
    			$query = $em->createQueryBuilder()->select('d')->from('IfrescoClientBundle:DataSource', 'd')
    			->where('d.id = :id')
    			->setParameter('id', $source);
    			$dataSources = $query->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
    			$pdo = $this->pdoConnect(
    					$dataSources['username'],
    					$dataSources['password'],
    					$dataSources['database_name'],
    					$dataSources['host'],
    					$dataSources['type']
    			);
    	
    			if($pdo) {
    				try {
    					$params = json_decode($params);
    					if(!$lookupSearch->getUseCache()) {
    						$store = array();
    						$params = json_decode($params);
    	
    						if ($LookupType == "datasourcerel") {
    							$columns_result = FunctionCache::getInstance()->selectDefinedQuery(
    									$pdo,
    									"SELECT t2.{$params->t2->col} " .
    									"FROM {$params->t1->table} t1 ".
    									"INNER JOIN {$params->t2->table} t2 ON t1.{$params->t1->colRel} = t2.{$params->t2->colRel}"
    							);
    						} elseif ($column == 'sql' && $table == 'sql') {
    							$columns_result = FunctionCache::getInstance()->selectDefinedQuery($pdo, 'SELECT ' . $params->sql);
    						} else {
    							$columns_result = FunctionCache::getInstance()->selectFromTable($pdo, $column, $table);
    						}
    	
    						if($columns_result!=false) {
    							foreach($columns_result as $val) {
    								$store[] = utf8_encode($val[0]);
    							}
    						}
    					} else {
    						$store = array(
    								'fields'  =>  array('name'),
    								'proxy'  =>  array(
    										'type'  =>  'ajax',
    										'url'  =>  $this->get('router')->generate('ifresco_client_metadata_data_source_lookup_rows_get') .
    										'?source=' . $lookupSearch->getId()
    								)
    						);
    					}
    	
    					$metaDataArray[] = array(
    							"name" => $dataKeyName,
    							"id" => $id,
    							"fieldLabel" => $label,
    							"store" => $store,
    							"queryMode" => !$lookupSearch->getUseCache() ? 'local' : 'remote',
    							"type" => $single ? "combo" : "superboxselect"
    					);
    	
    					$foundLookup = true;
    				} catch(\Exception $e) {}
    			}
    		}
    		
    		if ($foundLookup) {
    			return $metaDataArray;
    		}
    		else
    			return false;
    	}
    	else
    		return false;
    }

    private function renderField($name, $dataType, $label, $class)
    {
        /**
         * @var Lookup $lookupSearch
         * @var Setting $setting
         */

        $dataKeyName = str_replace(":", "_", $name);
        $id = str_replace(":", "_", $name);
        $dataType = str_replace("d:", "", $dataType);

        $metaDataArray = array();
        $em = $this->getDoctrine()->getManager();

        switch ($dataType) {
            case "hidden":
                $metaDataArray[] = array(
                    "name" => $name,
                    "type" => "hidden"
                );
                break;
            case "content":
                $metaDataArray[] = array(
                    "name" => "cm_content",
                    "type" => "content",
                    "fieldLabel" => $this->get('translator')->trans("Content")
                );
                break;
            case "mltext":
            case "text":
                $fieldProp = isset($this->importedClasses[$class][$name]) ? $this->importedClasses[$class][$name] : null;
                $constraints = isset($fieldProp) ? (isset($fieldProp->constraints) ? $fieldProp->constraints : null) : null;
                $findConst = false;
                if (isset($constraints) && count($constraints) > 0) {
                    foreach ($constraints as $value) {
                        if ($value->type == 'LIST') {
                            $list = $value->parameters->allowedValues;
                            if (count($list) > 0) {
                                $choices = array();
                                $findConst = true;

                                foreach ($list as $listKey => $listValue) {
                                    if (!empty($choicesList)) {
                                        $choicesList .= ",";
                                    }
                                    $values = explode("|", $listValue);
                                    if (count($values) > 0) {
                                        $listValue = $values[0];
                                    }
                                    $choices[$listKey] = $listValue;
                                }

                                $temp = array(
                                    "name" => $dataKeyName,
                                    "fieldLabel" => $label,
                                    "store" => $choices,
                                    "type" => isset($fieldProp) && $fieldProp->repeating ? "superboxselect" : "combo"
                                );

                                $metaDataArray[] = $temp;
                            }
                        }
                    }
                }

                if (!$findConst) {
                	$foundLookup = $this->getLookupForField($name,$dataKeyName,$label);
                    if ($foundLookup == false) {
                        $metaDataArray[] = array(
                            "name" => $dataKeyName,
                            "fieldLabel" => $label,
                            "type" =>  $fieldProp && $fieldProp->repeating ? "textarea" : "text"
                        );
                    }
                    else {
                    	$metaDataArray = array_merge($metaDataArray,$foundLookup);
                    }
                }
                break;
            /*case "long":
                break;
            case "double":
                break;
            case "float":
                break;
            case "int":
                $metaDataArray[] = array(
                    "name" => $dataKeyName,
                    "fieldLabel" => $label,
                    "type" => "int"
                );
                break;*/
            case "long":
            case "double":
            case "float":
            case "int":
            	$foundLookup = $this->getLookupForField($name,$dataKeyName,$label);
            	if ($foundLookup == false) {
	            	$metaDataArray[] = array(
	            			"name" => $dataKeyName,
	            			"fieldLabel" => $label,
	            			"type" => $dataType
	            	);
            	}
            	else {
            		$metaDataArray = array_merge($metaDataArray,$foundLookup);
            	}
                	break;
            case "date":
                $metaDataArray[] = array(
                    "name" => $dataKeyName,
                    "fieldLabel" => $label,
                    "type" => "date"
                );
                break;
            case "datetime":
                $metaDataArray[] = array(
                    "name" => $dataKeyName,
                    "fieldLabel" => $label,
                    "type" => "datetime"
                );
                break;
            case "boolean":
                $metaDataArray[] = array(
                    "name" => $dataKeyName,
                    "fieldLabel" => $label,
                    "type" => "boolean"
                );
                break;
            case "category":
                $metaDataArray[] = array(
                    "name" => $dataKeyName,
                    "url" => $this->get('router')->generate('ifresco_client_get_tree_categories'),//TODO : check route
                    "fieldLabel" => $label,
                    "type" => "category"
                );
                break;
            default:
                break;
        }

        return $metaDataArray;
    }

    /**
     * @param string $class
     * @param RESTDictionary $restDict
     */
    private function importClassForm($class, $restDict)
    {
        if (!empty($class)) {
            if (!array_key_exists($class, $this->importedClasses)) {
                $this->importedClasses[$class] = array();
                $form = $restDict->GetClassForm($class);
                if (!empty($form->data)) {
                    $data = $form->data->definition->fields;

                    foreach ($data as $field) {
                        $fieldName = $field->name;
                        $this->importedClasses[$class][$fieldName] = $field;
                    }
                }
            }
        }
    }

    private function pdoConnect($username, $password, $databaseName, $host='localhost', $type='mysql') {
        try {
            $conn = new \PDO(
                "$type:host=$host;dbname=$databaseName;",
                $username,
                $password,
                array()
            );
            return $conn;

        } catch (\PDOException  $e) {
            return false;
        }
    }
}