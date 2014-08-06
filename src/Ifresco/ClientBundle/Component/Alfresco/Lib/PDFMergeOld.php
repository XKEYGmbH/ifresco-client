<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Ifresco\ClientBundle\Component\Alfresco\Lib\FPDFI\FPDI;

define('FPDF_FONTPATH',__DIR__.'/FPDF/font/');


class PDFMerge extends FPDI {

    private $files = array();

    public function __construct($orientation='P',$unit='mm',$format='A4') {
        parent::FPDF($orientation,$unit,$format);
    }

    public function setFiles($files) {
        $this->files = $files;
    }
    
    public function addFile($file) {
        $this->files[] = $file;
    }
    
    public function UnlinkFiles() {
        foreach($this->files AS $file) {
            try {
                @unlink($file);    
            }   
            catch (\Exception $e) {
                
            } 
        }
    }

    public function merge() {
        $this->SetDisplayMode("default","single");

        foreach($this->files AS $file) {
            $pagecount = $this->setSourceFile($file);

            $pageFormats = array();
            for ($i = 1; $i <= $pagecount; $i++) {
                 $tplidx = $this->ImportPage($i);
                 
                 $format = $this->getTemplateSize($tplidx);
                 $pageFormats[$i] = $format;
            }
            
            for ($i = 1; $i <= $pagecount; $i++) {
                $this->AddPage("P",array($pageFormats[$i]["w"],$pageFormats[$i]["h"]));
                $tplidx = $this->ImportPage($i);
                
                $this->useTemplate($tplidx,0,0,$pageFormats[$i]["w"],$pageFormats[$i]["h"],true);
            }
            //echo $file."<br>"; 
            //flush();
            
            $this->_closeParsers();
            //unlink($file);  
            //unset($file);  
        }
        //flush();
    }

}