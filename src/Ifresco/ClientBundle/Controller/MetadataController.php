<?php

namespace Ifresco\ClientBundle\Controller;

use Doctrine\ORM\Query;
use Ifresco\ClientBundle\Component\Alfresco\Lib\CategoryCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\FunctionCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\sfAlfrescoWidgetContentAssociation;
use Ifresco\ClientBundle\Component\Alfresco\Lib\sfAlfrescoWidgetUserAssociation;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTAspects;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDictionary;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTPerson;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTTags;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\SugarCRM\SugarWrapper;
use Ifresco\ClientBundle\Component\Alfresco\VersionStore;
use Ifresco\ClientBundle\Entity\AllowedAspect;
use Ifresco\ClientBundle\Entity\ContentModelTemplate;
use Ifresco\ClientBundle\Entity\Lookup;
use Ifresco\ClientBundle\Entity\Setting;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTNode;

class MetadataController extends Controller {
	private $responseParams = array('fieldTypeSeparator' => false);

	public function getViewDataAction(Request $request) {
		/**
		 * @var User $user
		 * @var Setting $setting
		 */
		$nodeId = $request->get('nodeId');
		$ofParent = $request->get('ofParent');

		$user = $this->get('security.context')->getToken();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);

		$versionNode = false;
		if (preg_match("#workspace://version2store/(.*)#eis", $nodeId, $match)) {
			$spacesStore = new VersionStore($session);
			$nodeId = $match[1];
			$versionNode = true;
		}

		$node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);

		if ($ofParent == "true") {

			$em = $this->getDoctrine()->getManager();
			/**
			 * @var Setting $setting
			 */
			$setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => 'ParentNodeMetaLevel'));

			//TODO: not exactly understoond. If there is no setting - an error will happen.
			$parentLevel = 1;
			if ($setting != null) {
				$parentLevel = (int) $setting->getValueString();
			}

			$parentNodeId = $this->getParentNodeId($node, $parentLevel);

			if ($parentNodeId) {
				//return $this->forward('IfrescoClientBundle:Metadata:getViewData', array('nodeId' => $parentNodeId));

				$nodeId = $parentNodeId;
				$node = NodeCache::getInstance()->getNode($session, $spacesStore, $parentNodeId);
			}
		}

		if ($node != null) {

			$notAllowedList = array("mimetype", "encoding", "sys:node-dbid", "sys:store-identifier", "sys:node-uuid", "sys:store-protocol", "cm:initialVersion", "size", "cm:workingCopyMode", "cm:versionLabel", "rn:rendition", "cm:autoVersionOnUpdateProps", "fm:discussion", "cm:template", "ver2:*",
					"cm:contains", "app:icon", "rule:ruleFolder");

			$displayInInfoTab = array("cm:modifier", "cm:creator", "cm:created", "cm:modified");

			$this->responseParams['isWorkingCopy'] = $node->isWorkingCopy();

			$this->responseParams['isCheckedOut'] = $node->isCheckedOut();
			$this->responseParams['checkedOutBy'] = $node->checkedOutBy();
			if ($this->responseParams['checkedOutBy'] == $user->getUsername()) {
				$this->responseParams['checkedOutBy'] = $this->get('translator')->trans("you");
			}

			if ($node->isWorkingCopy()) {
				$this->responseParams['checkoutRefNode'] = $node->getCheckedoutOriginal();
			} elseif ($node->isCheckedOut()) {
				$this->responseParams['checkoutRefNode'] = $node->getWorkingCopy();
			}

			if (isset($this->responseParams['checkoutRefNode']) && $this->responseParams['checkoutRefNode'] != null && $versionNode == false) {
				$this->responseParams['checkoutRefNode'] = $session->getNode($spacesStore, str_replace("workspace://SpacesStore/", "", $this->responseParams['checkoutRefNode']));
				$this->responseParams['checkoutRefNodeImage'] = $this->responseParams['checkoutRefNode']->getIconUrl();
				$this->responseParams['checkoutRefNodeName'] = $this->responseParams['checkoutRefNode']->cm_name;
			}

			$this->responseParams['MetaData'] = $this->generateMetaCode($node, true, true, $notAllowedList, $displayInInfoTab);
			$this->responseParams['nodeURL'] = 'http://www.bna.com';

			if ($node->getType() == '{http://www.alfresco.org/model/content/1.0}folder') {
				$this->responseParams['nodeURL'] = $this->get('router')->generate('ifresco_client_index', array(), true) . '#folder/workspace://SpacesStore/' . $nodeId;
			} else {
				$this->responseParams['nodeURL'] = $this->get('router')->generate('ifresco_client_index', array(), true) . '#document/workspace://SpacesStore/' . $nodeId;
			}

			$this->responseParams['MetaFields'] = $this->responseParams['MetaData']["metaData"]["fields"];
			$this->responseParams['MetaFieldData'] = $this->responseParams['MetaData']["data"];

			$this->responseParams['fieldTypeSeparator'] = false;
			//echo "<pre>";
			//print_R($this->responseParams["MetaData"]["tabs"]);
			//die();
			if ((isset($this->responseParams['MetaFields']["Column1"]) && count($this->responseParams['MetaFields']["Column1"])) > 0 || (isset($this->responseParams['MetaFields']["Column2"]) && count($this->responseParams['MetaFields']["Column2"]) > 0)) {
				$this->responseParams['Column1'] = $this->responseParams['MetaFields']["Column1"];
				$this->responseParams['Column2'] = $this->responseParams['MetaFields']["Column2"];
				$this->responseParams['Tabs'] = $this->responseParams["MetaData"]["tabs"];
			} else {
				$this->responseParams['Column1'] = $this->responseParams['MetaFields'];
				$this->responseParams['Column2'] = array();
				$this->responseParams['Tabs'] = array();
			}

			$this->responseParams['nodeId'] = $nodeId;
			$this->responseParams['folderPathArray'] = array();
			$this->responseParams['path'] = array();

			if ($versionNode == false) {
				$explode = $node->getRealPathRefs(false);
				$explode = array_reverse($explode);

				$parentRef = $spacesStore->companyHome->getId();

				for ($i = 0; $i < count($explode); $i++) {
					$path = trim($explode[$i]);

					if (!empty($path)) {
						if ($path == $parentRef) {
							continue;
						}

						$nodePath = NodeCache::getInstance()->getNode($session, $spacesStore, $path);
						if ($nodePath != null) {
							$this->responseParams['path'][] = array('title'=>$nodePath->cm_name,'nodeId'=>$nodePath->id,'icon'=>$nodePath->getIconUrl());
							$nameOut = " {$nodePath->cm_name}";
							$tmp = $this->responseParams['folderPathArray'];
							$tmp[$nameOut] = "javascript:openFolder('{$nodePath->id}','<img src={$nodePath->getIconUrl()} border=0 align=absmiddle> {$nameOut}');";
							$this->responseParams['folderPathArray'] = $tmp;
						}
					}
				}

				$company = array("{$this->get('translator')->trans("Repository")}" => "javascript:openFolder('{$spacesStore->companyHome->id}','<img src={$spacesStore->companyHome->getIconUrl()} border=0 align=absmiddle> {$spacesStore->companyHome->cm_name}');");
				$this->responseParams['folderPathArray'] = array_merge($company, $this->responseParams['folderPathArray']);
				
				$this->responseParams['path'] = array_merge(array(array('title'=>$this->get('translator')->trans("Repository"),'nodeId'=>$spacesStore->companyHome->id,'icon'=>$spacesStore->companyHome->getIconUrl())), $this->responseParams['path']);
				
			} else {
				$this->responseParams['folderPathArray'] = array();
			}
		}

		return new JsonResponse($this->responseParams);
	}
	

	public function getDataSugarLookupRowsAction(Request $request) {
		$source = $request->get('source');
    	$queryStr = $request->get('query');
    	$firstParam = ($request->get('firstParam'));
    	$data = array();
    
    	if($queryStr !== false)
    		try {
    
    		$em = $this->get('doctrine')->getEntityManager();
    
    		$query = $em->createQueryBuilder()->select('l')
    		->from('IfrescoClientBundle:Lookup', 'l')
    		->where('l.id = :identifier')
    		->setParameter('identifier', $source);
    
    		$LookupSearch = $query->getQuery()->getOneOrNullResult();
    
    		if ($LookupSearch != null) {
    
    			$fieldData = $LookupSearch->getFielddata();
    			$params = $LookupSearch->getParams();
    			
    			$LookupType = $LookupSearch->getType();
    
    			list($source, $table, $column) = explode('/', $fieldData);
    
    			$query = $em->createQueryBuilder()->select('d')->from('IfrescoClientBundle:DataSource', 'd')->where('d.id = :id')->setParameter('id', $source);
				$dataSources = $query->getQuery()->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    
    			
    			$url = $Datasources['host'];
    			$url = rtrim($url,"/");
    			$url .= "/service/v3/rest.php";
    			$username = $dataSources['username'];
    			$password = $dataSources['password'];
    			 
    			$Wrapper = new SugarWrapper($url, $username, $password);
    			$error = $Wrapper->get_error();
    			 
    			if(is_bool($error) && $error == true) {
    				$sugarFields = array('id',$column);
    			
    				if ($params!=false) {
    					$params = json_decode($params);
	    				$relatedField = $params->relatedfield;
	    				$relatedColumn = $params->relatedcolumn;
	    				$sugarFields[] = $relatedColumn;
    				}
    				$options = array();
    				//if (emtpy($queryStr))
    				//	$options['limit'] = 50;
    				//else 
    					$options['limit'] = 50;
    					
    				if (!empty($queryStr))
    					$options['where'] = " ".strtolower($table).".$column LIKE '$queryStr%' ";
    				$EntityRecord = $Wrapper->get($table,$sugarFields,$options);

    				$columns_result = $EntityRecord;
    				/*if (count($EntityRecord) > 0) {
    					foreach ($EntityRecord as $Record) {
    						$res = array();
    						foreach ($Record as $key=>$value) {
    					}
    				}*/;

    				$relMap = isset($params->relatedfield)?array($params->relatedcolumn=>$params->relatedfield):array();
    				foreach($columns_result as $val) {

    					$mapData = array();
    					foreach($relMap as $mCol=>$relField) {
    						if(isset($val[$mCol])) {
    							//$mapData[str_replace(':', '_', $relField)] = ($val[$mCol]);
    							$mapData[$relField] = ($val[$mCol]);
    						}
    					}
    
    					$data[] = array('name' => ($val[$column]), 'mapData' => $mapData);
    				}
    			}
    
    		}
    	}
    	catch (\Exception $e) {
    		echo $e->getMessage(); exit;
    		$data = array();
    	}
		return new JsonResponse($data);
	}

	public function getDataSourceLookupRowsAction(Request $request) {
		$source = $request->get('source');
		$queryStr = $request->get('query');
		$firstParam = ($request->get('firstParam'));
		$data = array();

		$em = $this->get('doctrine')->getEntityManager();

		if ($queryStr !== false) {
			try {
				$query = $em->createQueryBuilder()->select('l')->from('IfrescoClientBundle:Lookup', 'l')->where('l.id = :identifier')->setParameter('identifier', $source);

				$LookupSearch = $query->getQuery()->getOneOrNullResult();

				if ($LookupSearch != null) {

					$fieldData = $LookupSearch->getFielddata();
					$params = $LookupSearch->getParams();
					$params = json_decode($params);
					$LookupType = $LookupSearch->getType();

					if ($LookupType == "datasourcerel") {
						$source = $fieldData;
						$table = $column = '';
					} else {
						list($source, $table, $column) = explode('/', $fieldData);
					}

					$query = $em->createQueryBuilder()->select('d')->from('IfrescoClientBundle:DataSource', 'd')->where('d.id = :id')->setParameter('id', $source);
					$dataSources = $query->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);

					$pdo = $this->pdoConnect($dataSources['username'],
                                $dataSources['password'],
                                $dataSources['database_name'],
                                $dataSources['host'],
                                $dataSources['type']);
					$pdo->exec("set names utf8");

					if ($pdo) {
//echo "TYPE: $LookupType <br>";
						if ($LookupType == "datasourcerel") {
							if ($firstParam) {
								$columns_result = $pdo
										->query(
												"
		        						SELECT t2.{$params->t2->col} as val1, t1.*, t2.*
		        						FROM {$params->t1->table} t1 INNER JOIN {$params->t2->table} t2
		        						ON t1.{$params->t1->colRel} = t2.{$params->t2->colRel}
		        						WHERE t2.{$params->t2->col} LIKE '%$queryStr%' AND t1.{$params->t1->col} = '$firstParam'
		        						");
							} else {
								$sql = "SELECT {$params->t2->col} FROM {$params->t2->table}";
								if ($queryStr != '')
									$sql .= " WHERE {$params->t2->col} LIKE '%$queryStr%'";

								/*$columns_result = $pdo->query($sql);*/
								
								// CHANGED OTHERWISE MAPPING PROBLEM
								// TODO - recheck if this is right 
								
								$columns_result = $pdo
								->query(
										"
										SELECT t2.{$params->t2->col} as val1, t1.*, t2.*
										FROM {$params->t1->table} t1 INNER JOIN {$params->t2->table} t2
										ON t1.{$params->t1->colRel} = t2.{$params->t2->colRel}
										WHERE t2.{$params->t2->col} LIKE '%$queryStr%'
										"); 
							}
//echo $sql;
						} elseif ($column == 'sql' && $table == 'sql') {
							$sql = $params->sql;
							$where = str_replace('{0}', $queryStr, $params->where);
							$columns_result = $pdo->query("SELECT $sql WHERE $where");
						} else {
							$sql = "SELECT $column FROM $table";
							if ($queryStr != '')
								$sql .= " WHERE $column LIKE '%$queryStr%'";

							$columns_result = $pdo->query($sql);
						}
						$columns_result = $columns_result->fetchAll();

						$relMap = isset($params->relMap) ? $params->relMap : array();
						
						foreach ($columns_result as $val) {
							$mapData = array();
							//echo "START WITH VAL:<br>";
							//print_R($val);
							foreach ($relMap as $mCol => $relField) {
//echo $mCol ."=>". $relField."|".str_replace(':', '_', $relField)."|".isset($val[$mCol])."=".($val[$mCol])."<br>";
								if (isset($val[$mCol]) && !empty($relField)) {
									
									//$mapData[str_replace(':', '_', $relField)] = ($val[$mCol]);
									$mapData[$relField] = ($val[$mCol]);
								}
							}
							//print_R($mapData);
							//echo "<hr>";
							
							$data[] = array('name' => ($val[0]), 'mapData' => $mapData);
						}

					}

				}
			} catch (\Exception $e) {
				echo $e->getMessage();
				$data = array();
			}
		}

		return new JsonResponse($data);
	}

	/**
	 * @param string $nodeId
	 * @param bool $fieldTypeSeparator
	 * @return \Ifresco\ClientBundle\Controller\Response
	 */
	public function getNodeMetaDataAction($nodeId, $fieldTypeSeparator) {
		$this->responseParams['fieldTypeSeparator'] = $fieldTypeSeparator;
		NodeCache::getInstance()->clearNodeCache($nodeId);
		$hideCreateElements = $this->getRequest()->get('hideCreateElements');

		if ($hideCreateElements == true) {
			$metaDataResult = $this->generateMetaCode($nodeId, false, false, array(), array(), true);
		} else {
			$metaDataResult = $this->generateMetaCode($nodeId);
		}

		$response = new JsonResponse($metaDataResult);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	public function getAspectsAction($nodeId) {
		/**
		 * @var User $user 
		 */
		$user = $this->get('security.context')->getToken();
		$repository = $user->getRepository();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);
		$restAspects = new RESTAspects($repository, $spacesStore, $session);
		$currentAspectList = $this->currentAspects($nodeId, $restAspects);
		$selected = array();
		foreach ($currentAspectList as $list) {
			$selected[] = array('name' => $list->name, 'title' => $list->title);
		}

		return new JsonResponse(array('success' => true, 'data' => array('currentAspectList' => $selected, 'aspectList' => $this->getAspectList($restAspects, $currentAspectList), 'nodeId' => $nodeId)));
	}

	public function saveAspectsAction(Request $request) {
		$nodeId = $request->get('nodeId');
		$aspects = json_decode($request->get('selectedAspects'));
		$allAspects = json_decode($request->get('aspects'));
		$deSelAspects = array();
		foreach ($allAspects as $aspect) {
			$found = false;
			foreach ($aspects as $selectedAspect) {
				if ($aspect === $selectedAspect) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				$deSelAspects[] = $aspect;
			}
		}

		$data = array("success" => false, "nodeId" => $nodeId);
		try {
			/**
			 * @var User $user
			 */
			$user = $this->get('security.context')->getToken();
			$session = $user->getSession();
			$spacesStore = new SpacesStore($session);
			$node = $session->getNode($spacesStore, $nodeId);

			if ($node != null) {
				if (count($aspects) > 0) {
					for ($i = 0; $i < count($aspects); $i++) {
						$aspect = str_replace(":", "_", $aspects[$i]);
						if (!$node->hasAspect($aspect)) {
							$node->addAspect($aspect);
						}
					}
				}

				// DESELECTED ASPECTS
				if (count($deSelAspects) > 0) {
					for ($i = 0; $i < count($deSelAspects); $i++) {
						$aspect = str_replace(":", "_", $deSelAspects[$i]);
						if ($node->hasAspect($aspect)) {
							$node->removeAspect($aspect);
						}
					}
				}

				$session->save();
				$data["success"] = true;

				NodeCache::getInstance()->clearNodeCache($nodeId);
			}

		} catch (\Exception $e) {
			$data["success"] = false;
		}

		$response = new JsonResponse($data);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	private function currentAspects($nodeId, $restAspects) {
		/**
		 * @var AllowedAspect $aspects
		 */
		$em = $this->getDoctrine()->getManager();
		$aspects = $em->getRepository('IfrescoClientBundle:AllowedAspect')->findAll();

		$currentAspects = $restAspects->GetNodeAspects($nodeId);
		$currentAspectList = array();
		if (count($currentAspects->current) > 0) {
			foreach ($currentAspects->current as $aspect) {
				$aspectInfo = $restAspects->GetAspect($aspect);
				if (count($aspects) > 0) {
					if (count($em->getRepository('IfrescoClientBundle:AllowedAspect')->findBy(array('name' => $aspect))) > 0) {
						$currentAspectList[$aspect] = $aspectInfo;
					}
				}
			}
		} else {
			return $currentAspectList;
		}

		return $currentAspectList;
	}

	private function getAspectList($restAspects, $alreadyInUse) {
		$em = $this->getDoctrine()->getManager();
		$aspects = $em->getRepository('IfrescoClientBundle:AllowedAspect')->findAll();

		$aspectList = array();
		if (count($aspects) == 0) {
			$aspectList = $restAspects->GetAllAspects();
			foreach ($aspectList as $aspect) {
				//TODO: check this!!! it is from Alfresco library . int this situation there is no such method as getName()
				//                $aspectInfo = $restAspects->GetAspect($aspect->getName());
				$aspectInfo = $restAspects->GetAspect($aspect->name);

				//TODO: STD class can not be a first parameter
				if (!array_key_exists($aspect->name, $alreadyInUse)) {
					$aspectList[] = $aspectInfo;
				}
			}
		} else {
			foreach ($aspects as $aspect) {
				$aspectInfo = $restAspects->GetAspect($aspect->getName());

				if (!array_key_exists($aspect->getName(), $alreadyInUse)) {
					$aspectList[] = $aspectInfo;
				}
			}
		}

		return $aspectList;
	}

	private function getParentNodeId($node, $parentLevel) {
		$parentNodes = $node->getParents();
		if (count($parentNodes) > 0) {
			foreach ($parentNodes as $path => $parentNode) {
				$parentLevel--;
				if ($parentLevel > 0) {
					return $this->getParentNodeId($parentNode->getParent(), $parentLevel);
				} else {
					if (preg_match("#workspace://SpacesStore/(.*)#eis", $path, $match) && strpos($parentNode->getParent()->getType(), 'store_root') === false) {
						return $match[1];
					} else {
						return strpos($parentNode->getParent()->getType(), 'store_root') !== false ? $node->getId() : false;
					}
				}
			}
		}
	}

	public function getContentTypeAction(Request $request) {
		$nodeId = $request->get('nodeId');
		$types = array();
		/**
		 * @var User $user
		 */
		$user = $this->get('security.context')->getToken();
		$repository = $user->getRepository();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);

		$restDictionary = new RESTDictionary($repository, $spacesStore, $session);
		$node = $session->getNode($spacesStore, $nodeId);
		if ($node != null) {
			$typesFetch = $this->getContentTypesList($restDictionary, $node->getType());
			if (count($typesFetch) > 0) {
				foreach ($typesFetch as $type) {
					$name = isset($type->name) ? $type->name : '';
					$title = isset($type->title) ? $type->title : '';
					$description = isset($type->description) ? $type->description : '';
					$title = empty($title) ? $name : $title;

					if ($title != '') {
						$types["types"][] = array("name" => $name, "title" => $title, "description" => $description);
					}
				}
			}
		}

		$response = new JsonResponse($types);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	public function saveContentTypeAction(Request $request) {
		$nodes = $request->get('nodes');
		$type = $request->get('typeId');

		$data = array("success" => false, "nodeId" => "");

		/*$user = $this->get('security.context')->getToken();
		$repository = $user->getRepository();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);
		
		$realNode = $session->getNode($spacesStore, $nodeId);
		
		if ($realNode != null) {
		    $restDictionary = new RESTDictionary($repository, $spacesStore, $session);
		    $result = $restDictionary->SpecifyType($nodeId, $type);
		
		    if (isset($result->current)) {
		        $data["success"] = true;
		    }
		
		    NodeCache::getInstance()->clearNodeCache($nodeId);
		}
		
		$data["nodeId"] = $nodeId;*/
		if (!empty($type)) {
			try {
				$nodes = json_decode($nodes);
				if (is_array($nodes)) {
					$user = $this->get('security.context')->getToken();
					$repository = $user->getRepository();
					$session = $user->getSession();
					$ticket = $user->getTicket();

					$spacesStore = new SpacesStore($session);
					foreach ($nodes as $nodeId) {
						//$nodeId = $node->nodeId;
						$RealNode = $session->getNode($spacesStore, $nodeId);

						if ($RealNode != null) {
							$RestDictionary = new RESTDictionary($repository, $spacesStore, $session);

							$Result = $RestDictionary->SpecifyType($nodeId, $type);

							if (isset($Result->current))
								$data["success"] = true;

							NodeCache::getInstance()->clearNodeCache($nodeId);
						}
						$data["nodeId"] = $nodeId;
					}
				} else
					throw new \Exception("nodes are not an array");
			} catch (\Exception $e) {
				$data["success"] = false;
				$data["message"] = $e->getMessage();
			}
		}

		$response = new JsonResponse($data);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	public function saveMetaDataAction(Request $request) {
		$nodeId = $request->get('nodeId');
		$fields = $request->get('data');

		$data = array("success" => false, "nodeId" => $nodeId);
		/**
		 * @var User $user
		 */
		$user = $this->get('security.context')->getToken();
		$repository = $user->getRepository();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);
		$node = $session->getNode($spacesStore, $nodeId);
		try {
			if ($node != null && !empty($fields)) {
				$em = $this->getDoctrine()->getManager();
				$fields = json_decode($fields);
				foreach ($fields as $fieldName => $fieldValue) {
					if (preg_match("/^prop_/eis", $fieldName) || preg_match("/^assoc_/eis", $fieldName)) {
						$fieldName = str_replace("prop_", "", $fieldName);
						$fieldName = str_replace("assoc_", "", $fieldName);
						$fieldName = trim($fieldName);
						
						$explode = explode("#", $fieldName);
						if (count($explode) >= 2) {
							$fieldName = $explode[0];
							$dataType = $explode[1];
							$orgFieldName = preg_replace("/(.*?)_(.*)/","$1:$2",$fieldName);
							switch ($dataType) {
							case "mltext":
							case "text":
								$orgField = str_replace("_", ":", $fieldName);
								$lookupSearch = $em->getRepository('IfrescoClientBundle:Lookup')->findBy(array('field' => $orgField));

								if ($lookupSearch != null) {
									if (!empty($fieldValue) && $fieldValue != null) {
										if (!is_array($fieldValue)) {
											$fieldValue = (urldecode($fieldValue));
											$fieldValue = array($fieldValue);
										}
										$fieldValue = implode(", ", $fieldValue);
									} else {
										$fieldValue = null;
									}
								}
								$node->{$fieldName} = $fieldValue;

								break;
							case "long":
							case "int":
							case "double":
							case "float":
								if (strlen($fieldValue) == 0) {
									$node->{$fieldName} = NULL;
									continue;
								}

								$currencyField = $this->getCurrencyField($orgFieldName);
								if ($currencyField != null) {
									$fieldValue = str_replace($currencyField->currencySymbol, "", $fieldValue);
									$fieldValue = str_replace($currencyField->thousands, "", $fieldValue);
									$fieldValue = str_replace($currencyField->decimal, ".", $fieldValue);
								}
								else {
									$fieldValue = str_replace(",", ".", $fieldValue);
								}
						
								$node->{$fieldName} = $fieldValue;
								break;
							case "text_constraints":
								if (!empty($fieldValue) && $fieldValue != null) {
									//if (!is_array($fieldValue))
									//    $fieldValue = array($fieldValue);
								} else {
									$fieldValue = null;
								}
								$node->{$fieldName} = $fieldValue;
								break;
							case "boolean":
								$node->{$fieldName} = (string) ($fieldValue == true ? "true" : "false");
								break;
							case "date":
							case "datetime":
								$fieldValue = empty($fieldValue) ? null : date("c", strtotime($fieldValue));
								$node->{$fieldName} = $fieldValue;

								break;
							case "category": // old one not used - new one at end in else cause
								if (!is_array($fieldValue)) {
									$fieldValue = array();
								}

								$catArray = array();
								foreach ($fieldValue as $category) {
									$id = $category->id;
									$categoryNode = $session->getNode($spacesStore, $id);
									if ($categoryNode != null) {
										$catArray[] = $categoryNode;
									}
								}

								$node->{$fieldName} = $catArray;
								break;
							case "tags":

								$tags = explode(",", $fieldValue);
								if (count($explode) == 0) {
									$tags = array($fieldValue);
								}

								$restTags = new RESTTags($repository, $spacesStore, $session);
								$node->{$fieldName} = null;
								$session->save();
								$restTags->AddNodeTags($nodeId, $tags);
								break;
							case "person":
								$node->removeAssociationOfType($fieldName);
								$session->save();
								if (!empty($fieldValue) && strlen($fieldValue) > 2) {
									$persons = json_decode($fieldValue);
									try {
										if (is_array($persons)) {
											foreach ($persons as $person) {
												$id = $person->id;
												$personNode = $session->getNode($spacesStore, $id);

												if ($personNode != null && !$node->hasAssociation($personNode, $fieldName)) {
													$node->addAssociation($personNode, $fieldName);
												}
											}

										}
									} catch (\Exception $e) {
									}
								}
								break;
							case "content":
								$node->removeAssociationOfType($fieldName);
								$session->save();
								if (!empty($fieldValue) && strlen($fieldValue) > 2) {
									$contents = json_decode($fieldValue);
									try {
										if (is_array($contents)) {
											foreach ($contents as $content) {
												$id = $content->id;
												$contentNode = $session->getNode($spacesStore, $id);
												if ($contentNode != null && !$node->hasAssociation($contentNode, $fieldName)) {
													$node->addAssociation($contentNode, $fieldName);
												}
											}
										}
									} catch (\Exception $e) {
									}
								}
								break;
							default:
								break;
							}
						} else {
							switch ($fieldName) {
							case "cm_taggable":
								$restTags = new RESTTags($repository, $spacesStore, $session);
								$node->cm_taggable = null;
								$session->save();
								$restTags->AddNodeTags($nodeId, $fieldValue);
								break;
							}
						}
					} else {
						switch ($fieldName) {
							//case "categories[]":
						case "categories":
							if (!is_array($fieldValue)) {
								$catArray = empty($fieldValue) ? array() : array($fieldValue);
							} else {
								$catArray = (array) $fieldValue;
							}

							if (count($catArray) > 0) {
								foreach ($catArray as $index => $value) {
									if (empty($value)) {
										unset($catArray[$index]);
									} else {
										$catArray[$index] = "workspace://SpacesStore/" . $value->id;
									}
								}
							}

							$node->cm_categories = $catArray;
							break;
						case "item[tags][]":
							$restTags = new RESTTags($repository, $spacesStore, $session);
							$node->cm_taggable = null;
							$session->save();
							$restTags->AddNodeTags($nodeId, $fieldValue);
							break;
						}
					}
				}

				$session->save();
				$data["success"] = true;
				NodeCache::getInstance()->clearNodeCache($nodeId);
			}
		} catch (\Exception $e) {
			$data["success"] = false;
			$data["errorMessage"] = $e->getMessage();
		}

		$response = new JsonResponse($data);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	public function checkoutAction($nodeId) {
		$checkOut = array("success" => true, "workingCopyId" => null);
		/**
		 * @var USer $user
		 */
		$user = $this->get('security.context')->getToken();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);

		$node = $session->getNode($spacesStore, $nodeId);
		if ($node != null) {
			try {
				$workingCopy = $node->checkOut();
				$checkOut["workingCopyId"] = $workingCopy->getId();
			} catch (\SoapFault $e) {
				$checkOut["success"] = false;
			}
		}

		$response = new JsonResponse($checkOut);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	public function cancelCheckoutAction($nodeId) {
		$checkIn = array("success" => true);
		/**
		 * @var USer $user
		 */
		$user = $this->get('security.context')->getToken();
		$repository = $user->getRepository();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);

		//$node = $session->getNode($spacesStore, $nodeId);
		$RestNode = new RESTNode($repository, $spacesStore, $session);
		//if ($node != null) {
		try {
			//$node->cancelCheckout();
			$RestNode->CancelCheckout($nodeId);
		} catch (\Exception $e) {
			$checkIn["success"] = false;
			$checkIn["message"] = $e->getMessage();
		}
		//}

		$response = new JsonResponse($checkIn);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	public function checkinAction(Request $request) {
		$checkIn = array("success" => true, "origNodeId" => null);
		/**
		 * @var User $user
		 */
		$user = $this->get('security.context')->getToken();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);

		$node = $session->getNode($spacesStore, $request->get('nodeId'));
		if ($node != null) {
			try {
				//TODO: it is empty nothing from response. $request->get('note')
				$origNode = $node->checkIn($request->get('note'), false);
				$checkIn["origNodeId"] = $origNode->getId();
			} catch (\SoapFault $e) {
				$checkIn["success"] = false;
			}
		}

		$response = new JsonResponse($checkIn);
		$response->headers->set('Content-Type', 'application/json; charset=utf-8');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
		$response->headers->set('Pragma', 'no-cache');
		return $response;
	}

	private function getContentTypesList($restDictionary, $currentType) {
		$em = $this->getDoctrine()->getManager();
		$types = $em->getRepository('IfrescoClientBundle:AllowedType')->findAll();
		$typeList = array();

		if (count($types) == 0) {
			$subClasses = $restDictionary->GetSubClassDefinitions("cm_content");

			if ($subClasses != null) {
				foreach ($subClasses as $type) {
					$name = $type->name;
					if ($currentType != $name) {
						$typeList[] = $type;
					}
				}
			}
		} else {
			foreach ($types as $type) {
				try {
					if ($currentType != $type) {
						$name = str_replace(":", "_", $type->getName());
						$typeInfo = $restDictionary->GetClassDefinitions($name);
						$typeList[] = $typeInfo;
					}
				} catch (\Exception $e) {
					echo $e->getMessage();
				}
			}
		}

		return $typeList;
	}
	
	private function getLookupForField($internalName,$dataKeyName,$label,$fieldValue) {
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
				'l.field = :field AND
                                               (l.apply_to = 0 OR l.apply_to = 1) AND
                                               (l.type = :type1 OR l.type = :type2 OR l.type = :type3 OR l.type = :type4 OR l.type = :type5)')->setParameter('field', $internalName)->setParameter('type1', "category")->setParameter('type2', "user")->setParameter('type3', "datasource")
		                                               ->setParameter('type4', "datasourcerel")->setParameter("type5", "sugar");
		
		/**
		 * @var Lookup $lookupSearch
		*/
		$lookupSearch = $query->getQuery()->getOneOrNullResult();
		$foundLookup = false;
		
		$metaDataArray = array();
		
		if ($lookupSearch != null) {
			$catId = $fieldData = $lookupSearch->getFieldData();
			$lookupType = $lookupSearch->getType();
			$single = $lookupSearch->getIsSingle();
			$useCache = $lookupSearch->getUseCache();
			$params = $lookupSearch->getParams();
			if ($lookupType == "category") {
				$categoryNode = $session->getNode($spacesStore, $catId);
				$categoryPathRefs = array_reverse($categoryNode->getRealPathRefs());
		
				$categoryPath = '';
				for ($refCounter = 1; $refCounter < count($categoryPathRefs); $refCounter++) {
					$categoryPath .= NodeCache::getInstance()->getNode($session, $spacesStore, $categoryPathRefs[$refCounter])->cm_name . '/';
				}
		
				$categoryPath = substr($categoryPath, 0, strlen($categoryPath) - 1);
				$categories = CategoryCache::getInstance($this->get('security.context')->getToken())->getCachedCategories($categoryPath);
		
				if (count($categories->items) > 0) {
					$foundLookup = true;
					$choices = array();
					$choicesArr = array();
		
					foreach ($categories->items as $listValue) {
						$catName = preg_replace("/\{.*?\}/is", "", $listValue->name);
						$catDesc = $listValue->description;
						$catDescName = "$catDesc ($catName)";
						if (empty($catDesc) || $catDesc == null) {
							$catDesc = $catName;
							$catDescName = $catName;
						}
		
						if (!empty($choicesList)) {
							$choicesList .= ",";
						}
						$choices[] = $catName;
						$choicesArr[] = array($catName, $catDesc, $catDescName);
					}
		
					if ($single) {
						$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label,
								"store" => array("fields" => array("catName", "catDesc", "catDescName"), "data" => $choicesArr), "type"=>"combo");
					} else {
						if ($this->responseParams['fieldTypeSeparator'] == true) {
							$fieldValue = str_replace(", ", ",", $fieldValue);
							$metaDataResult["data"][$dataKeyName] = $fieldValue;
						}
		
						$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label,
								"store" => array("fields" => array("catName", "catDesc", "catDescName"), "data" => $choicesArr), "type"=>"superboxselect");
					}
				}
			} else if ($lookupType == "user") {
				$users = NodeCache::getInstance()->getRestPeoples($restPerson);
				/**
				 * @var Setting $setting
				*/
				$setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array('key_string' => 'UserLookupLabel'));
				$userLabel = "%firstName% %lastName% (%email%)";
				if ($setting) {
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
							if (!empty($choicesList))
								$choicesList .= ",";
							$choices[] = $name;
						}
		
						sort($choices);
						if ($single) {
							$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "store" => $choices, "hiddenValue" => $fieldValue, "type"=>"combo");
						} else {
							if ($this->responseParams['fieldTypeSeparator'] == true) {
								$fieldValue = str_replace(", ", ",", $fieldValue);
								$metaDataResult["data"][$dataKeyName] = $fieldValue;
							}
							$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "store" => $choices, "type"=>"superboxselect");
						}
					}
				}
			} else if ($lookupType == "sugar") {
				list($source, $table, $column) = explode('/', $fieldData);
				$query = $em->createQueryBuilder()->select('d')->from('IfrescoClientBundle:DataSource', 'd')->where('d.id = :id')->setParameter('id', $source);
				$dataSources = $query->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
		
				$url = $dataSources['host'];
				$url = rtrim($url, "/");
				$url .= "/service/v3/rest.php";
				$username = $dataSources['username'];
				$password = $dataSources['password'];
				$wrapper = new SugarWrapper($url, $username, $password);
				$error = $wrapper->get_error();
		
				if (is_bool($error) && $error == true) {
					$store = array('fields' => array('name'),
							'proxy' => array('type' => 'ajax', 'url' => $this->get('router')->generate('ifresco_client_metadata_sugar_lookup_rows_get') . '?source=' . $lookupSearch->getId()));
		
					$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "store" => $store, "hiddenValue" => $fieldValue, "type" => $single ? "combo" : "superboxselect");
					$foundLookup = true;
				}
		
			} else if ($lookupType == "datasource" || $lookupType == "datasourcerel") {
				if ($lookupType == "datasourcerel") {
					$source = $fieldData;
					$table = $column = '';
				} else {
					list($source, $table, $column) = explode('/', $fieldData);
				}
		
				$query = $em->createQueryBuilder()->select('d')->from('IfrescoClientBundle:DataSource', 'd')->where('d.id = :id')->setParameter('id', $source);
				$dataSources = $query->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
		
				$pdo = $this
				->pdoConnect($dataSources['username'], $dataSources['password'], $dataSources['database_name'], $dataSources['host'], $dataSources['type']);
				if ($pdo) {
					try {
						$dataSourceRel = false;
						$params = json_decode($params);
		
						if (!$useCache) {
							$store = array();
							$queryMode = "local";
		
							if ($lookupType == "datasourcerel") {
								$columns_result = FunctionCache::getInstance()->selectDefinedQuery($pdo, "SELECT t2.{$params->t2->col} FROM {$params->t1->table} t1 INNER JOIN {$params->t2->table} t2 ON t1.{$params->t1->colRel} = t2.{$params->t2->colRel}");
							} elseif ($column == 'sql' && $table == 'sql') {
								$columns_result = FunctionCache::getInstance()->selectDefinedQuery($pdo, 'SELECT ' . $params->sql);
							} else {
								$columns_result = FunctionCache::getInstance()->selectFromTable($pdo, $column, $table);
							}
		
							if ($columns_result != false)
							foreach ($columns_result as $val) {
								$store[] = utf8_encode($val[0]);
							}
						} else {
							$queryMode = 'remote';
							$store = array('fields' => array('name'),
									'proxy' => array('type' => 'ajax', 'url' => $this->get('router')->generate('ifresco_client_metadata_data_source_lookup_rows_get') . '?source=' . $lookupSearch->getId()));
							if ($lookupType == "datasourcerel") {
								$dataSourceRel = array('origName' => $label, 'origUrl' => $this->get('router')->generate('ifresco_client_metadata_data_source_lookup_rows_get') . '?source=' . $lookupSearch->getId(), 'firstDone' => false, 'col1' => $params->t1->col,
										'col2' => $params->t2->col, 'relatedcolumn' => $params->relatedcolumn);
							}
						}
		
						if ($single) {
							$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "store" => $store, 'datasourcerel' => $dataSourceRel, "hiddenValue" => $fieldValue, "queryMode" => $queryMode, "type"=>"combo");
						} else {
							if ($this->responseParams['fieldTypeSeparator']) {
								$metaDataResult["data"][$dataKeyName] = $fieldValue;
							}
		
							$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, 'datasourcerel' => $dataSourceRel, "store" => $store, "queryMode" => $queryMode, "type"=>"superboxselect");
						}
						$foundLookup = true;
					} catch (\Exception $e) {
					}
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

	private function generateMetaCode($nodeId, $separateColumns = false, $renderValues = false, $notAllowedList = array(), $displayInInfoTab = array(), $hideCreateElements = false) {
		/**
		 * @var User $user
		 * @var Lookup $lookupSearch
		 */
		$this->responseParams['uniquePref'] = time();
		$user = $this->get('security.context')->getToken();
		$repository = $user->getRepository();
		$session = $user->getSession();
		$spacesStore = new SpacesStore($session);

		if (preg_match("#workspace://version2store/(.*)#eis", $nodeId, $match)) {
			$spacesStore = new VersionStore($session);
			$nodeId = $match[1];
		}

		if ($nodeId instanceof Node) {
			$node = $nodeId;
			$nodeId = $node->getId();
		} else {
			$node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
		}

		$this->responseParams['MetaNode'] = $node;

		if ($node != null) {
			$restAspects = new RESTAspects($repository, $spacesStore, $session);

			$restDict = new RESTDictionary($repository, $spacesStore, $session);
			$restPerson = new RESTPerson($repository, $spacesStore, $session);
			$metaForm = NodeCache::getInstance()->getFormMetadata($restDict, $node);
			$contentType = isset($metaForm->data->type) ? $metaForm->data->type : '';

			$em = $this->getDoctrine()->getManager();

			/**
			 * @var ContentModelTemplate $template
			 */
			$template = $em->getRepository('IfrescoClientBundle:ContentModelTemplate')->findOneBy(array('class' => str_replace(":", "_", $contentType)));

			$fields = isset($metaForm->data->definition->fields) ? $metaForm->data->definition->fields : array();

			if (count($notAllowedList) == 0) {
				$notAllowedList = array("mimetype", "encoding", "cm:content", "sys:node-dbid", "sys:store-identifier", "sys:node-uuid", "sys:store-protocol", "cm:initialVersion", "size", "cm:modifier", "cm:creator", "cm:created", "cm:modified", "cm:accessed", "cm:versionLabel",
						"cm:autoVersionOnUpdateProps", "cm:workingCopyMode", "fm:discussion", "rn:rendition", "cm:template", "cm:contains", "app:icon", "rule:ruleFolder");
			}

			$formConfig = array("columnCount" => 2);

			$metaDataResult = array("success" => false, "metaData" => array("fields" => array(), "formConfig" => $formConfig, "createElements" => ""), "data" => array());

			if ($renderValues == true) {
				$metaRenderer = MetaRenderer::getInstance($user, $this->getDoctrine());
				$metaRenderer->scanRenderers();
			}

			$metaDataArray = array();
			$createElementsArray = array();
			if (count($fields) > 0) {
				$em = $this->getDoctrine()->getManager();
				foreach ($fields as $fieldProp) {
					$fieldName = $fieldProp->name;
					if (!in_array($fieldName, $displayInInfoTab)) {
						if ($fieldProp->protectedField == 1 || in_array($fieldName, $notAllowedList) || in_array(preg_replace("/(.*?:).*/is", "$1*", $fieldName), $notAllowedList))
							continue;
					}

					$fieldType = $fieldProp->type;
					$dataKeyName = $fieldProp->dataKeyName;
					$dataType = isset($fieldProp->dataType) ? $fieldProp->dataType : '';
					$internalName = $fieldProp->name;
					$label = $fieldProp->label;
					$endpointType = isset($fieldProp->endpointType) ? $fieldProp->endpointType : '';
					$fieldValue = isset($metaForm->data->formData->{$dataKeyName}) ? $metaForm->data->formData->{$dataKeyName} : '';

					$id = $this->responseParams['uniquePref'] . str_replace(":", "_", $internalName);

					if ($this->responseParams['fieldTypeSeparator'] == true) {
						if ($fieldType != "association") {
							$dataKeyName = $dataKeyName . "#" . $dataType;
						} else {
							$type = str_replace("cm:", "", $endpointType);
							$dataKeyName = $dataKeyName . "#" . $type;
						}
					}

					if ($renderValues == true) {
						if ((!empty($endpointType) && ($valueRender = $metaRenderer->getAssocRenderer($endpointType)) != null) || ($valueRender = $metaRenderer->getPropertyRenderer($internalName)) != null || ($valueRender = $metaRenderer->getDataRenderer($dataType)) != null
								|| ($valueRender = $metaRenderer->getClickSearchRenderer($internalName, $label)) != null) {
							$fieldValue = $valueRender->render($fieldValue);
							$fieldValue = $fieldValue;
						}
					}

					if (!empty($fieldValue)) {
						$metaDataResult["data"][$dataKeyName] = $fieldValue;
					}

					switch ($fieldType) {
					case "property":
						switch ($dataType) {
						case "content":
						// Not needed
							continue;
							break;
						case "mltext":
						case "text":
							if (!$this->responseParams['fieldTypeSeparator']) { // just for the view
								$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "type"=>$dataType);
							} else {
								settype($fieldProp->constraints, 'array');
								$constraints = $fieldProp->constraints;
								$findConst = false;

								if (count($constraints) > 0) {
									foreach ($constraints as $value) {
										switch ($value->type) {
										case "LIST":
											if ($this->responseParams['fieldTypeSeparator'] == true) {
												unset($metaDataResult["data"][$dataKeyName]);
												$dataKeyName = $fieldProp->dataKeyName . "#" . $dataType . "_constraints";
												$metaDataResult["data"][$dataKeyName] = $fieldValue;
											}

											$list = $value->parameters->allowedValues;

											if (count($list) > 0) {
												$choices = array();
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

												$findConst = true;

												if ($fieldProp->repeating == true) {
													$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "store" => $choices, "type"=>"combo");
												} else {
													$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "store" => $choices, "type"=>"combo");
												}
											}

											break;
										default:
											break;
										}
									}
								}

								if ($findConst == false) {
									$foundLookup = $this->getLookupForField($internalName,$dataKeyName,$label,$fieldValue);
									if ($foundLookup == false) {
										$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "type"=>$dataType);
									}
									else {
										$metaDataArray = array_merge($metaDataArray,$foundLookup);
									}
								}
							}

							break;
						case "long":
						case "double":
						case "float":
						case "int":							
							$foundLookup = $this->getLookupForField($internalName,$dataKeyName,$label,$fieldValue);
							if ($foundLookup == false) {
								$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "type"=>$dataType);
	
								$currencyField = $this->getCurrencyField($internalName);
								if ($currencyField != null) {
									if (empty($fieldValue))
										$fieldValue = 0;
									$fieldValue = (double)$fieldValue;
									$precision = $currencyField->precision;
									if (empty($precision))
										$precision = 0;
									else
										$precision = (int)$precision;
									$value = number_format($fieldValue, $precision, $currencyField->decimal, $currencyField->thousands );
									$fieldValue = $currencyField->currencySymbol." ".$value;
									$metaDataResult["data"][$dataKeyName] = $fieldValue;
								}
								else {
									if (strlen($fieldValue) > 0 && $fieldValue == 0) {
										$metaDataResult["data"][$dataKeyName] = "0";
									}
								}
							}
							else {
								$metaDataArray = array_merge($metaDataArray,$foundLookup);
							}

							break;
						case "date":
							if (!empty($fieldValue) && strlen($fieldValue) > 1) {
								$metaDataResult["data"][$dataKeyName] = date($user->getDateFormat(), strtotime($fieldValue));
							}

							$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "format" => $user->getDateFormat(), "type"=>$dataType);

							break;
						case "datetime":
							if (!empty($fieldValue) && strlen($fieldValue) > 1) {
								$metaDataResult["data"][$dataKeyName] = date($user->getDateFormat() . " " . $user->getTimeFormat(), strtotime($fieldValue));
							}

							$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "timeFormat" => $user->getTimeFormat(), "dateFormat" => $user->getDateFormat(), "fieldLabel" => $label, "type"=>$dataType);

							break;
						case "boolean":
							$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "type"=>$dataType);

							break;
						case "category":
							if ($internalName == "cm:taggable") {
								if ($this->responseParams['fieldTypeSeparator'] == true) {

									unset($metaDataResult["data"][$dataKeyName]);
									$dataKeyName = $fieldProp->dataKeyName . "#tags";
									$metaDataResult["data"][$dataKeyName] = $fieldValue;
								}

								$boxName = str_replace("#tags", "", $dataKeyName);
								$tagsValuesClr = array();
								if ($renderValues != true) {
									if (!empty($fieldValue)) {
										$tags = explode(",", $fieldValue);
										if (count($tags) == 0) {
											$tags = array($fieldValue);
										}

										$tagsValues = array();
										if (count($tags) > 0) {
											
											foreach ($tags as $tagNodeRef) {
												$tagUUId = str_replace("workspace://SpacesStore/", "", $tagNodeRef);
												$tagNode = $session->getNode($spacesStore, $tagUUId);

												if ($tagNode != null) {
													$tagsValues[] = array("name" => $tagNode->cm_name);
													$tagsValuesClr[] = $tagNode->cm_name;
												}
											}

											$fieldValue = $tagsValues;
											$metaDataResult["data"][$dataKeyName] = join(", ",$tagsValuesClr);
										}
									}
								}

								$restTags = new RESTTags($repository, $spacesStore, $session);
								$allTags = $restTags->GetAllTags(false);
								if (!$allTags) {
									$allTags = array();
								}

								$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "value" => $tagsValuesClr, "store" => $allTags, 'type' => 'tags');
							} else {
								$nodeIdStrip = substr($nodeId, 0, 5);
								$boxName = str_replace("#tags", "", $dataKeyName);
								$boxNameRand = $boxName . "_num_" . $nodeIdStrip;

								$categoriesData = array();
								if ($renderValues != true) {
									if (!empty($fieldValue)) {
										$defaultValues = explode(",", $fieldValue);
										if (count($defaultValues) == 0) {
											$defaultValues = array($fieldValue);
										}

										foreach ($defaultValues as $catVal) {
											$catNodeId = str_replace("workspace://SpacesStore/", "", $catVal);
											$catNode = NodeCache::getInstance()->getNode($session, $spacesStore, $catNodeId);
											$name = $catNode->cm_name;
											$path = $catNode->getFolderPath();
											$path .= "/{$name}";
											$path = str_replace("/categories/General/", "", $path);
											
											$path = ltrim($path,"/");
											
											$catVal = str_replace("workspace://SpacesStore/", "", $catVal);
											$categoriesData[] = array('path' => $path, 'catVal' => $catVal, 'id'=>$catVal);
										}
									}
								}

								$metaDataArray[] = array("name" => $dataKeyName, "realName" => $internalName, "fieldLabel" => $label, "fieldValue" => $fieldValue, "categoriesData" => $categoriesData, 'type' => 'category',
										"elements" => array('boxNameRand' => $boxNameRand, 'name' => $boxName, 'fieldValue' => $fieldValue));

								$createElementsArray[$id] = '<input type="hidden" realField="' . $boxNameRand . '" metatype="category" name="' . $boxName . '" value="' . $fieldValue . '">';
							}

							break;
						default:
							break;
						}
						break;
					case "association":
						$endpointType = $fieldProp->endpointType;
						switch ($endpointType) {
						case "cm:person":
							$widget = new sfAlfrescoWidgetUserAssociation();
							$url = $this->generateUrl('ifresco_client_association_autocomplete_user_data_get');
							$widget->addOption("urlfor", $url);
							$nodeIdStrip = substr($nodeId, 0, 5);
							$boxName = str_replace("#person", "", $dataKeyName);
							$boxNameRand = $boxName . "_num_" . $nodeIdStrip;

							$elements = array();
							if ($renderValues == false) {
								if (!empty($fieldValue)) {
									if (!is_array($fieldValue) && preg_match("/,/eis", $fieldValue)) {
										$fieldValue = explode(",", $fieldValue);
									} else if (!is_array($fieldValue)) {
										$fieldValue = array($fieldValue);
									}

									$newFieldValue = array();
									foreach ($fieldValue as $value) {
										$Id = str_replace("workspace://SpacesStore/", "", $value);
										$referenceNode = $session->getNode($spacesStore, $Id);

										$newFieldValue[] = array("mail" => $referenceNode->cm_email, "id" => $referenceNode->getId(), "firstName" => $referenceNode->cm_firstName, "lastName" => $referenceNode->cm_lastName, "userName" => $referenceNode->cm_userName);

									}
									$fieldValue = $newFieldValue;
								}

								$createElementsArray[$id] = $widget->render($boxNameRand, $fieldValue, array());
								$elements = array('boxNameRand' => $boxNameRand, 'fieldValue' => $fieldValue);
							}

							$metaDataArray[] = array("name" => $boxName, "realName" => $internalName, "fieldLabel" => $label, "elements" => $elements, 'autoCompleteUrl' => $url, 'type' => 'autocomplete');

							break;
						case "cm:content":
							$widget = new sfAlfrescoWidgetContentAssociation();
							$url = $this->generateUrl('ifresco_client_association_autocomplete_content_data_get');
							$widget->addOption("urlfor", $url);
							$nodeIdStrip = substr($nodeId, 0, 5);
							$boxName = str_replace("#content", "", $dataKeyName);
							$boxNameRand = $boxName . "_num_" . $nodeIdStrip;

							$elements = array();
							if ($renderValues == false) {
								if (!empty($fieldValue)) {
									if (!is_array($fieldValue) && preg_match("/,/eis", $fieldValue)) {
										$fieldValue = explode(",", $fieldValue);
									} else if (!is_array($fieldValue)) {
										$fieldValue = array($fieldValue);
									}

									$newFieldValue = array();
									foreach ($fieldValue as $value) {
										$id = str_replace("workspace://SpacesStore/", "", $value);
										$referenceNode = $session->getNode($spacesStore, $id);
										$extension = preg_replace("/.*\.(.*)/is", "$1", $referenceNode->cm_name);
										if (!file_exists(sfConfig::get('sf_web_dir') . "/images/filetypes/16x16/{$extension}.png")) {
											$extension = "txt";
										}

										$newFieldValue[] = array("type" => $extension, "name" => $referenceNode->cm_name);

									}

									$fieldValue = $newFieldValue;
								}

								$createElementsArray[$id] = $widget->render($boxNameRand, $fieldValue, array());
								$elements = array('boxNameRand' => $boxNameRand, 'fieldValue' => $fieldValue);
							}

							$metaDataArray[] = array("name" => $boxName, "realName" => $internalName, "fieldLabel" => $label, "elements" => $elements, 'autoCompleteUrl' => $url, 'type' => 'autocomplete');
							break;
						default:
							$widget = new sfAlfrescoWidgetContentAssociation();
							$url = $this->generateUrl('ifresco_client_association_autocomplete_content_data_get') . "?dataTypeParam=" . $endpointType;
							$widget->addOption("urlfor", $url);

							$nodeIdStrip = substr($nodeId, 0, 5);
							$boxName = $dataKeyName;
							$boxName = str_replace("#" . preg_replace("/.*?:(.*)/is", "$1", $endpointType), "", $boxName);
							$boxName = str_replace(preg_replace("/.*?:(.*)/is", "$1", $endpointType), "", $boxName);
							$boxNameRand = $boxName . "_num_" . $nodeIdStrip;

							$elements = array();
							if ($renderValues == false) {
								if (!empty($fieldValue)) {
									if (!is_array($fieldValue) && preg_match("/,/eis", $fieldValue)) {
										$fieldValue = explode(",", $fieldValue);
									} else if (!is_array($fieldValue)) {
										$fieldValue = array($fieldValue);
									}

									$newFieldValue = array();
									foreach ($fieldValue as $value) {
										$id = str_replace("workspace://SpacesStore/", "", $value);
										$referenceNode = $session->getNode($spacesStore, $id);
										$extension = preg_replace("/.*\.(.*)/is", "$1", $referenceNode->cm_name);
										if (!file_exists($this->get('kernel')->getRootDir() . "/web/images/filetypes/16x16/{$extension}.png")) {
											$extension = "txt";
										}

										$newFieldValue[] = array("type" => $extension, "name" => $referenceNode->cm_name);
									}
									$fieldValue = $newFieldValue;
								}

								$createElementsArray[$id] = $widget->render($boxNameRand, $fieldValue, array());
								$elements = array('boxNameRand' => $boxNameRand, 'fieldValue' => $fieldValue);
							}

							$metaDataArray[] = array("name" => $boxName, "realName" => $internalName, "fieldLabel" => $label, "elements" => $elements, 'autoCompleteUrl' => $url, 'type' => 'autocomplete');
							break;
						}
						break;
					default:
						break;
					}
				}

				if ($template != null) {
					$aspectView = $template->getAspectView();
					$jsonData = $template->getJsonData();

					if (!empty($jsonData)) {
						$jsonData = json_decode($jsonData);
						$column1 = $jsonData->Column1;
						$column2 = $jsonData->Column2;
						$tabs = $jsonData->Tabs;

						if (count($column1) != count($column2)) {
							$col1 = count($column1);
							$col2 = count($column2);

							if ($col1 > $col2) {
								$diff = $col1 - $col2;
								for ($i = 0; $i < $diff; $i++) {
									$column2[] = "";
								}
							} else if ($col1 < $col2) {
								$diff = $col2 - $col1;
								for ($i = 0; $i < $diff; $i++) {
									$column1[] = "";
								}
							}
						}

						$realTabs = array();
						if (count($tabs) > 0) {
							$realTabs = $tabs->tabs;
						}

						$appendAspects = array();
						$aspectsOfNode = $node->getAspects();
						//TODO: WHAT IS THIS? IS IT WORK?
						$aspects = $em->getRepository('IfrescoClientBundle:AllowedAspect');

						foreach ($aspectsOfNode as $aspect) {
							$aspect = $session->namespaceMap->getShortName($aspect, ":");
							if (count($aspects) > 0) {
								if (count($aspects->findByName($aspect)) > 0) {
									$aspectInfo = $restAspects->GetAspect($aspect);
									$appendAspects[$aspect] = $aspectInfo;
								}
							}
						}

						if (count($appendAspects) > 0) {
							foreach ($appendAspects as $aspect) {
								$items = array();
								foreach ($aspect->properties as $aspectProp) {
									$foundPosition = $this->searchFieldArray($fields, $aspectProp->name);
									if ($foundPosition != -1) {
										$fieldIntern = $fields[$foundPosition];
										$object = new \stdClass();
										$object->name = $fieldIntern->name;
										$object->dataType = $fieldIntern->dataType;
										$object->title = $fieldIntern->label;
										$object->type = $fieldIntern->type;

										if ($aspectView == "append") {
											$column1[] = $object;
											$column2[] = "";
										} else {
											$items[] = $object;
										}
									}
								}

								if ($aspectView != "append") {
									$tabObject = new \stdClass();
									$tabObject->title = $aspect->title;
									$tabObject->items = $items;
									$realTabs[] = $tabObject;
								}
							}
						}

						if ($separateColumns) {

							$metaDataArrayNew = $this->useSeparateTemplateRenderer($metaDataArray, $column1, $column2);
						} else {
							$metaDataArrayNew = $this->renderOnTemplate($metaDataArray, $column1, $column2);
						}

						$tempTabCol = array();

						if (count($displayInInfoTab) > 0) {
							$stdClass = new \stdClass();
							$stdClass->title = "Info";
							$itemArray = array();
							foreach ($displayInInfoTab as $item) {
								$tempObj = new \stdClass();
								$tempObj->name = $item;
								$itemArray[] = $tempObj;
							}

							$stdClass->items = $itemArray;
							$realTabs[] = $stdClass;
						}

						if (count($realTabs) > 0) {
							$tabArray = array();
							foreach ($realTabs as $tabValues) {
								$title = $tabValues->title;
								$items = $tabValues->items;

								$itemsArray = array();
								foreach ($items as $itemValue) {
									$tempTabCol[] = $itemValue->name;
									$findItem = $this->searchMetaArray($metaDataArray, $itemValue->name);

									if ($findItem != -1) {
										$itemsArray[] = $metaDataArray[$findItem];
									}
								}

								$tabArray[] = array("title" => $title, "fields" => $itemsArray);
							}

							$tabPanelArray = array("name" => "metaTabPanel", "items" => $tabArray);
							if ($separateColumns == true) {
								$metaDataResult["tabs"] = $tabArray;
								//$metaDataArrayNew["Tabs"] = $tabPanelArray;
							} else {
								$metaDataResult["tabs"] = $tabArray;
								//$metaDataArrayNew["Tabs"] = $tabPanelArray;
							}
						}

						if (count($createElementsArray) > 0 && $hideCreateElements == false) {
							$tempCol1 = array();
							$tempCol2 = array();
							if (count($column1) > 0) {
								foreach ($column1 as $value) {
									$tempCol1[] = isset($value->name) ? $value->name : "empty";
								}
							}

							if (count($column2) > 0) {
								foreach ($column2 as $value) {
									$tempCol2[] = isset($value->name) ? $value->name : "empty";
								}
							}

							foreach ($createElementsArray as $key => $value) {
								$key = str_replace("_", ":", $key);
								if (in_array($key, $tempCol1) || in_array($key, $tempCol2) || in_array($key, $tempTabCol)) {
									$metaDataResult["metaData"]["createElements"] .= $value;
								}
							}
						}

						$metaDataArray = $metaDataArrayNew;
					}

				} else {
					if (count($createElementsArray) > 0 && $hideCreateElements == false) {
						foreach ($createElementsArray as $value) {
							$metaDataResult["metaData"]["createElements"] .= $value;
						}
					}
				}

				$metaDataResult["metaData"]["fields"] = $metaDataArray;
				$metaDataResult["success"] = true;
			}
		}

		return $metaDataResult;
	}

	private function pdoConnect($username, $password, $databaseName, $host = 'localhost', $type = 'mysql') {
		try {
			$conn = new \PDO("$type:host=$host;dbname=$databaseName;", $username, $password, array());

			return $conn;
		} catch (\PDOException $e) {
			return false;
		}
	}

	private function searchMetaArray($array, $string) {
		//TODO: check unique param
		// $string = str_replace(":", "_", $string);
		$in = -1;

		foreach ($array as $key => $value) {
			if ($value["realName"] == $string) {
				$in = $key;
			}
		}
		return $in;
	}

	private function useSeparateTemplateRenderer($array, $column1, $column2) {
		//TODO: check what is wrong with templates
		$tempCol1 = array();
		$tempCol2 = array();

		/*if (count($column1) > 0) {
			foreach ($column1 as $value) {
				$tempCol1[] = isset($value->name) ? $value->name : 'empty';
			}
		}

		if (count($column2) > 0) {
			foreach ($column2 as $value) {
				$tempCol2[] = isset($value->name) ? $value->name : 'empty';
			}
		}*/
		$emptyIndex = 0;
		if (count($column1) > 0) {
			foreach ($column1 as $value) {
				if (isset($value->name))
					$tempCol1[] = $value->name;
				else {
					$tempCol1[] = 'empty#'.$emptyIndex;
					$emptyIndex++;
				}
			}
		}
		
		if (count($column2) > 0) {
			foreach ($column2 as $value) {
				if (isset($value->name))
					$tempCol2[] = $value->name;
				else {
					$tempCol2[] = 'empty#'.$emptyIndex;
					$emptyIndex++;
				}
			}
		}

		$columns = count($tempCol1) > 0 && count($tempCol2) > 0 ? array_combine($tempCol1, $tempCol2) : array();
		$metaData = array();

		foreach ($columns as $key => $value) {
			$column1 = $this->searchMetaArray($array, $key);
			$metaData["Column1"][] = $column1 != -1 ? $array[$column1] : array("empty" => true);

			$column2 = $this->searchMetaArray($array, $value);
			$metaData["Column2"][] = $column2 != -1 ? $array[$column2] : array("empty" => true);
		}

		return $metaData;
	}

	private function renderOnTemplate($array, $column1, $column2) {
		$tempCol1 = array();
		$tempCol2 = array();
		$emptyIndex = 0;
		if (count($column1) > 0) {
			foreach ($column1 as $value) {
				if (isset($value->name))
					$tempCol1[] = $value->name;
				else {
					$tempCol1[] = 'empty#'.$emptyIndex;
					$emptyIndex++;
				}
			}
		}

		if (count($column2) > 0) {
			foreach ($column2 as $value) {
				if (isset($value->name))
					$tempCol2[] = $value->name;
				else {
					$tempCol2[] = 'empty#'.$emptyIndex;
					$emptyIndex++;
				}
			}
		}

		$columns = array_combine($tempCol1, $tempCol2);
		$metaData = array();

		foreach ($columns as $key => $value) {
			$column1 = $this->searchMetaArray($array, $key);
			$metaData[] = $column1 != -1 ? $array[$column1] : array("empty" => true);

			$column2 = $this->searchMetaArray($array, $value);
			$metaData[] = $column2 != -1 ? $array[$column2] : array("empty" => true);
		}

		return $metaData;
	}

	private function searchFieldArray($array, $string) {
		$in = -1;
		foreach ($array as $key => $value) {
			if ($value->name == $string) {
				$in = $key;
			}
		}

		return $in;
	}
	
	private $currencyFields = null;
	public function getCurrencyField($searchField,$em=null) {
		if ($em == null)
			$em = $this->getDoctrine()->getManager();

		if ($this->currencyFields == null) {
			$currencyFieldsSetting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
					'key_string' => 'CurrencyFields'
			));
			
			if ($currencyFieldsSetting != null) {
				$jsonData = json_decode($currencyFieldsSetting->getValueString());
				$this->currencyFields = $jsonData ? $jsonData : array();
			} else {
				$this->currencyFields = array();
			}
		}
		
		if (count($this->currencyFields) > 0) {
			foreach ($this->currencyFields as $field) {
				if ($field->name == $searchField)
					return $field;
			}
		}
		return null;
	}
}
