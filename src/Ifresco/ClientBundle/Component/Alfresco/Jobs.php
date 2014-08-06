<?php
namespace Ifresco\ClientBundle\Component\Alfresco;

use Ifresco\ClientBundle\Component\Alfresco\ContentData;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Mailer\PHPMailer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Symfony\Component\HttpFoundation\Request;

class Jobs {

    private $viewValues = array();

    function __set($name, $value) {
        $this->viewValues[$name] = $value;
    }

    function __get($name) {
        return isset($this->viewValues[$name]) ? $this->viewValues[$name] : null;
    }

    public function exportNodes($JobId, $columns, $folderExport, $email)
    {
        $this->folderExport = $folderExport;
        $this->JobId = $JobId;
        $this->email = $email;

        $this->namespace = NamespaceMap::getInstance();

        $user = $GLOBALS['kernel']->getContainer()->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();

        $spacesStore = $this->spacesStore = new SpacesStore($session);

        if (!is_array($columns))
            throw new \Exception("No columns found");

        $columns = (object)$columns;

        $this->DateFormat = $DateFormat = Registry::getSetting("DateFormat","m/d/Y");
        $this->TimeFormat = $TimeFormat = Registry::getSetting("TimeFormat","H:i");

        if ($JobId == null)
            throw new \Exception("Couldn't create a new job (maybe database table does not exists)");
        self::updateJob($JobId,"STARTED");

        $this->tmpFileName = $tmpFileName = tempnam($GLOBALS['kernel']->getCacheDir()."/tmp","ExportCSV");

        $this->tmpFile = $tmpFile = fopen($tmpFileName,"w+");
        fwrite($tmpFile, "\xEF\xBB\xBF");
        self::exportAll($columns);
    }

    private function exportAll($columns) {
        $skip = array();
        $user = $GLOBALS['kernel']->getContainer()->get('security.context')->getToken();
        $skipRootFolder = array("Data Dictionary", "Datenverzeichnis", "Besucher-Home", "Benutzer-Home", "Guest Home", "User Homes", "Sites");
        $skipType = array("{http://www.alfresco.org/model/content/1.0}systemfolder",
            "{http://www.alfresco.org/model/transfer/1.0}transferGroup",
            "{http://www.alfresco.org/model/publishing/1.0}Environment",
            "{http://www.alfresco.org/model/action/1.0}action",
            "{http://www.alfresco.org/model/content/1.0}category",
        );

        $companyHome = $this->spacesStore->companyHome;
        if ($companyHome != null) {
            $this->updateJob($this->JobId, "EXPORTING...");
            $colArr = array();
            foreach ($columns as $col) {
                $col = (object)$col;

                $name = $col->name;
                $title = $col->title . "({$col->name})";
                if (empty($col->title))
                    $title = $name;

                $colArr[] = $title;
            }
            if ($this->folderExport == false) {
                $colArr[] = "Parent";
            }

            $colArr[] = 'alfresco_url';
            $colArr[] = 'alfresco_mimetype';
            $colArr[] = 'alfresco_node_path';
            $colArr[] = 'nodeId';

            $this->writeCSVLine($colArr);
            $Childs = $companyHome->getChildren();
            $count = count($Childs);
            $done = 0;
            foreach ($Childs as $Child) {

                $Node = $Child->getChild();
                if (in_array($Node->cm_name,$skipRootFolder))
                    continue;

                if (in_array($Node->getType(),$skipType))
                    continue;

                $this->parseFields($Node,$columns,$skip);

                if ($Node->getType() == "{http://www.alfresco.org/model/content/1.0}folder") {
                    $this->csvRecursiveFolder($Node,$columns,$skipRootFolder,$skipType);
                }
                $done++;
                $this->updateJob($this->JobId,"EXPORTED {$done}/{$count}");
            }
        }

        $this->updateJob($this->JobId,"DONE",array("link"=>$this->tmpFileName));
        fclose($this->tmpFile);

        if ($this->email != null && $this->email != 'null') {

            $SMTP_HOST = Registry::getSetting("SMTP_HOST");
            $SMTP_PORT = Registry::getSetting("SMTP_PORT");

            if (empty($SMTP_HOST) || $SMTP_HOST == null || empty($SMTP_PORT) || $SMTP_PORT == null)
                throw new \Exception("No SMTP Server is set");

            $SMTP_AUTH = Registry::getSetting("SMTP_AUTH");
            $SMTP_USERNAME = Registry::getSetting("SMTP_USERNAME");
            $SMTP_PASSWORD = Registry::getSetting("SMTP_PASSWORD");

            if ($SMTP_AUTH == "true")
                if (empty($SMTP_USERNAME) || empty($SMTP_PASSWORD) || $SMTP_USERNAME == null || $SMTP_PASSWORD == null)
                    throw new \Exception("SMTP Authentication active but no Username or Password is set");

            $FROM_EMAIL = Registry::getSetting("FROM_EMAIL");
            if (empty($FROM_EMAIL) || $FROM_EMAIL == null)
                $FROM_EMAIL = "noreplay@localhost.com";

            $FROM_NAME = Registry::getSetting("FROM_NAME");
            if (empty($FROM_NAME) || $FROM_NAME == null)
                $FROM_NAME = "ifresco client";

            $emailSubject = "ifresco - export";

            $mail = new PHPMailer(true);
            $mail->IsSMTP();

            $mail->Host = $SMTP_HOST;
            $mail->Port = $SMTP_PORT;
            $mail->SMTPDebug = 0;
            if ($SMTP_AUTH == "true") {
                $mail->SMTPAuth = true;
                $mail->Username = $SMTP_USERNAME;
                $mail->Password = $SMTP_PASSWORD;
            }
            else
                $mail->SMTPAuth = false;

            //$GLOBALS['kernel']->getCacheDir()
            //$mail->PluginDir = $this->get('kernel')->getRootDir().'/src/ifrescoClient/AlfrescoBundle/Lib/Mailer/';
            //$mail->PluginDir = sfConfig::get('sf_lib_dir').'/PHPMailer/';
            $mail->AddAttachment($this->tmpFileName, $user->getUsername().'_'.date('Y-m-d-H-i-s').'.csv');

            $mail->MsgHTML(" ");
            $mail->AddAddress($this->email);
            $mail->SetFrom($FROM_EMAIL, $FROM_NAME);
            $mail->Subject = $emailSubject;

            $send = $mail->Send();

            $mail->ClearAddresses();

            $mail->ClearBCCs();
            $mail->ClearCCs();
            $mail->ClearReplyTos();
            $mail->ClearAllRecipients();
            $mail->ClearCustomHeaders();

            $this->updateJob($this->JobId,"DONE (SEND TO {$this->email})",array("link"=>$this->tmpFileName));
        }
    }

    private function csvRecursiveFolder($RootNode, $columns, $skip, $skipType) {
        $Childs = $RootNode->getChildren();
        if ($Childs != null) {
            foreach ($Childs as $Child) {
                $Node = $Child->getChild();

                if (in_array($Node->cm_name,$skip))
                    continue;

                if (in_array($Node->getType(),$skipType))
                    continue;

                $this->parseFields($Node,$columns,$skip);

                if ($Node->getType() == "{http://www.alfresco.org/model/content/1.0}folder") {
                    $this->csvRecursiveFolder($Node,$columns, $skip, $skipType);
                }
            }
        }
    }

    private function parseFields($Node,$columns,$skip) {
        if ($Node->getType() == "{http://www.alfresco.org/model/content/1.0}folder" && $this->folderExport == false) {
            return;
        }

        $valArr = array();
        $Properties = $Node->getProperties();
        foreach ($columns as $col) {
            $col = (object)$col;

            $name = $col->name;
            $dataType = $col->dataType;
            $nameFull = $this->namespace->getFullName($name,":");

            if (in_array($name,$skip))
                continue;

            switch ($name) {
                case "{http://www.alfresco.org/model/content/1.0}content":
                case "cm:content":
                case "cm_content":
                    if ($Node->getType() != "{http://www.alfresco.org/model/content/1.0}folder") {
                        $contentData = $Properties[$nameFull];
                        if ($contentData instanceof ContentData) {
                            $val = $contentData->getUrlWithoutTicket();
                        }
                        else {
                            $val = "NO CONTENT!";
                        }
                    }
                    else
                        $val = "";
                    break;
                case "cm:type":
                    $val = $Node->getType();
                    break;
                case "cm:taggable":
                case "cm:categories":
                    $FieldValue = $Properties[$nameFull];
                    if (!empty($FieldValue)) {
                        $CatVal = array();
                        if (!is_array($FieldValue)) {
                            $Categories = explode(",",$FieldValue);
                            if (count($Categories) == 0)
                                $Categories = array($FieldValue);
                        }
                        else
                            $Categories = $FieldValue;

                        if (count($Categories) > 0) {
                            foreach ($Categories as $Key => $CatNodeRef) {
                                $CatUUId = str_replace("workspace://SpacesStore/","",$CatNodeRef);

                                $CatNode = $this->session->getNode($this->spacesStore, $CatUUId);
                                if ($CatNode != null) {
                                    $CatName = $CatNode->cm_name;
                                    //$CategoriesValues[$CatNode->getId()] = $CatNode->cm_name;
                                    $CatVal[] = $CatName;
                                }
                            }
                            $val = "\"".implode(", ",$CatVal)."\"";
                        }
                        else {
                            $val = "";
                        }

                    }
                    else
                        $val = "";
                    break;
                case "cm:tags":

                    break;
                default:
                    if (!empty($Properties[$nameFull])) {
                        switch($dataType) {
                            case "d:date":
                                $val = "\"".date($this->DateFormat,strtotime($Properties[$nameFull]))."\"";
                                break;
                            case "d:datetime":
                                $val = "\"".date($this->DateFormat." ".$this->TimeFormat,strtotime($Properties[$nameFull]))."\"";
                                break;
                            case "d:bool":
                            case "d:boolean":
                                $val = "\"".($Properties[$nameFull] == true ? "true" : "false")."\"";
                                break;
                            default:
                                $val = "\"".($Properties[$nameFull])."\"";
                                break;
                        }
                    }
                    else
                        $val = "";
                    break;
            }
            $valArr[] = ($val);
        }

        if ($this->folderExport == false) {
            $PrimaryParentName = "";
            try {
                $PrimaryParent = $Node->getPrimaryParent();
                if ($PrimaryParent != null) {
                    $PrimaryParentName = $PrimaryParent->cm_name;
                }
            }
            catch (\Exception $e) {

            }

            $valArr[] = utf8_decode($PrimaryParentName);
        }

        if ($Node->cm_content instanceof ContentData) {
            $valArr[] = $Node->cm_content->getUrl();
            $valArr[] = $Node->cm_content->getMimetype();
        }
        else {
            $valArr[] = '';
            $valArr[] = '';
        }

        $valArr[] = str_replace('/Company Home', '', $Node->getFolderPath(true, true));
        $valArr[] = $Node->getId();

        $this->writeCSVLine($valArr);
    }

    private function writeCSVLine($arr) {
        fwrite($this->tmpFile,join(";",$arr)."\r\n");
    }

    private function updateJob($jobId, $status, $data="") {
        try {

            $em = $GLOBALS['kernel']->getContainer()->get('doctrine')->getEntityManager();

            $query = $em->createQueryBuilder()->select('c')->from('IfrescoClientBundle:CurrentJob', 'c')
                ->where('c.id = :id')
                ->setParameter('id', $jobId);
            $Job = $query->getQuery()->getOneOrNullResult();

            if ($Job == null)
                throw new \Exception("no job found");

            $Job->setStatus($status);
            if (!empty($data)) {
                $Job->setJsonData(json_encode($data));
            }
            $em->persist($Job);
            $em->flush();
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

}