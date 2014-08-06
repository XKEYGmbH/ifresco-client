<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

class PDFMerge {

    private $files = array();

    public function __construct() {
        
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

    private $pdf = null;
    public function merge() {
    	$this->pdf = new \ZendPdf\PdfDocument();
    	$extractor = new \ZendPdf\Resource\Extractor();

        foreach($this->files AS $file) {
        	try {
		        $pdfWorking = \ZendPdf\PdfDocument::load($file);
		
		    	for ($i = 0; $i < count($pdfWorking->pages); $i++) {
		    		$page = $extractor->clonePage($pdfWorking->pages[$i]);
		    		$this->pdf->pages[] = $page;
		    	}
        	}
        	catch (\Exception $e) {
        		
        	}
        }
    }
    
    public function Output($filename) {
    	if ($this->pdf != null) {
    		$this->pdf->save($filename);
    	}
    }

}