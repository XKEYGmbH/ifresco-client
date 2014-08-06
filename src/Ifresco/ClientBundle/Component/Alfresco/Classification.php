<?php
namespace Ifresco\ClientBundle\Component\Alfresco;

use Ifresco\ClientBundle\Component\Alfresco\REST\RESTContent;

class Classification extends BaseObject {
    
    private $_repository;
    private $_session;
    private $_store;
    private $_ticket;
    
    public function __construct($repository, $store, $session) {
        $this->_repository = $repository;
        $this->_store = $store;
        $this->_session = $session;
        $this->_ticket = $this->_session->getTicket();
    }
    
    public function addCategory($categoryName,$PathNode="") {
        $Found = true;
        try {
            if (!empty($PathNode)) {
                if($PathNode instanceof Node) {
                    $CategoryRoot = $PathNode;   
                    if ($CategoryRoot == null)
                        $Found = false;        
                } else {
                    $LuceneNodes = $this->_session->query($this->_store, 'PATH:"cm:categoryRoot/cm:generalclassifiable/'.$PathNode.'"');
                    if ($LuceneNodes != null && count($LuceneNodes) > 0) {
                        $CategoryRoot = $LuceneNodes[0];
                        if ($CategoryRoot == null) {
                            $Found = false;    
                        }
                    } else {
                        $Found = false;
                    }
                }
            } else {
                $CategoryRoot = $this->_store->categoryRoot;
            }
                
            if ($Found == true) {
                $AssocType = "cm_subcategories";
                $CategoryNode = $CategoryRoot->createChild("cm_category", $AssocType, "cm_".$categoryName);           
                $CategoryNode->cm_name = $categoryName;
            }

            return $CategoryNode;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function addCategoryAndDescription($categoryName,$categoryDescription,$PathNode="") {
        $Found = true;
        try {
            if (!empty($PathNode)) {
                if($PathNode instanceof Node) {
                    $CategoryRoot = $PathNode;   
                    if ($CategoryRoot == null)
                        $Found = false;        
                }
                else {
                    $LuceneNodes = $this->_session->query($this->_store, 'PATH:"cm:categoryRoot/cm:generalclassifiable/'.$PathNode.'"');
                    if ($LuceneNodes != null && count($LuceneNodes) > 0) {
                        $CategoryRoot = $LuceneNodes[0];
                        if ($CategoryRoot == null) {
                            $Found = false;    
                        }
                    }
                    else {
                        $Found = false;
                    }
                }
            }
            else
                $CategoryRoot = $this->_store->categoryRoot;   
                
            if ($Found == true) {
                $AssocType = "cm_subcategories";
                $CategoryNode = $CategoryRoot->createChild("cm_category", $AssocType, "cm_".$categoryName);             
                $CategoryNode->cm_name = $categoryName;
                $CategoryNode->cm_description = $categoryDescription;
            }
            return $CategoryNode;
        }
        catch (\Exception $e) {
            return false;
        }
    }
    
    public function removeCategory($categoryNodeRefOrNode) {
        $Found = true;
        try {
            if($categoryNodeRefOrNode instanceof Node) {
                $CatNode = $categoryNodeRefOrNode;   
                if ($CatNode == null) {
                    $Found = false;
                }
            } else {
                $categoryNodeRefOrNode = str_replace("workspace://SpacesStore/","",$categoryNodeRefOrNode);
                $CatNode = $this->_session->getNode($this->_store,$categoryNodeRefOrNode);
                if ($CatNode == null)
                    $Found = false;        
            }
            
            if ($Found == true) {
               $Id = $CatNode->getId(); 
               $Id = str_replace("workspace://SpacesStore/","",$Id);
               $RestContent = new RESTContent($this->_repository,$this->_store,$this->_session);
               $RestContent->DeleteNode($Id);
            }

            return $Found;
        } catch (\Exception $e) {
            return false;
        }
    }
}