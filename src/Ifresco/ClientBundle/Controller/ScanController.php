<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\ContentData;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTContent;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTUpload;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTQuickShare;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;

class ScanController extends Controller
{
	public function getScannerProfilesAction(Request $request) {
		$result = array();
	
		exec("scanimage -f \"%d,%v,%m \n\"", $o, $err);
	
		if($err === 0 && count($o) > 0) {
			foreach($o as $line) {
				list($name, $vendor, $model) = explode(',', $line);
				$result[] = array('name' => $name, 'title' => $model, 'description' => $vendor);
			}
		}
	
		$response = new JsonResponse($result);
		return $response;
	}
	
	public function getScannedImageAction(Request $request) {
	
		$file = $request->get('file');
		$format = $request->get('format');
		$imagine = new \Imagine\Imagick\Imagine();
	
		$tmpFile = $this->get('kernel')->getCacheDir()."/".$file;
	
		//->resize(new \Imagine\Image\Box(430, 594))
	
		$imagine->open($tmpFile)->show('jpeg');
	
		exit;
		/*$response = new Response('');
	
		$response->headers->set('Content-Type','image/jpeg; charset=utf-8');
		$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
		$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
		$response->headers->set('Pragma','no-cache');
	
		return $response;*/
	}
	
	public function initialScanAction(Request $request) {
	
		$device = $request->get('device');
	
		$result = array();
	
		$data = $this->doScan(false, false, false, false, 'Color', 'TIFF', 100, $device);
	
		$file = $data['file'];
	
		if($data['error'] == 0 ) {
	
			$result['success'] = true;
		}
		else {
			$result['success'] = false;
		}
	
		$result['tmpfile'] = $file;
		$result['cmd'] = $data['cmd'];
	

		$response = new JsonResponse($result);
		return $response;
	}
	
	public function scanSaveAction(Request $request) {
	
		$device = $request->get('device');
		$format = $request->get('format');
		$type = $request->get('type');
		$mode = $request->get('mode');
		$name = $request->get('name');
		$nodeId = $request->get('nodeId');
		$x = $request->get('x', false);
		$y = $request->get('y', false);
		$left = $request->get('left', false);
		$top = $request->get('top', false);
		$resolution = $request->get('resolution');
		$quality = $request->get('quality');
	
		if(!$type) {
			$type = 'cm:content';
		}
	
		//var_dump($top, $left, $x, $y);
		if(0 == $x && 0 == $y && 0 == $left && 0 == $top) {
			$x = $y = $left = $top = false;
		}
		else {
			//coefficient defined for 100 dpi (px to mm)
			$x = round($x * 0.5);
			$y = round($y * 0.5);
			$left = round($left * 0.5);
			$top = round($top * 0.5); //0.228
		}
		//var_dump($top, $left, $x, $y); exit;
	
		$result = array();
	
		$data = $this->doScan($top, $left, $x, $y, $mode, 'tiff', $resolution, $device);
		$file = $data['file'];
	
		if($data['error'] == 0 ) {
			$this->saveScannedDoc($file, $format, $name, $nodeId, $type, $resolution, $quality);
			$result['success'] = true;
		}
		else {
			$result['success'] = false;
		}
	
	
	
		$result['tmpfile'] = $file;
		$result['cmd'] = $data['cmd'];
	
		$response = new JsonResponse($result);
		return $response;
	}
	
	private function saveScannedDoc($file, $format, $name, $nodeId, $type, $resolution, $quality = 90) {
		$user = $this->get('security.context')->getToken();
		$repository = $user->getRepository();
		$session = $user->getSession();
		$ticket = $user->getTicket();
	
		$spacesStore = new SpacesStore($session);
	
		$RESTUpload = new RESTUpload($repository, $spacesStore, $session);
	
	
		switch ($format) {
			case 'JPEG':
			case 'JPG':
				$imagine = new \Imagine\Imagick\Imagine();
				$file = $this->get('kernel')->getCacheDir()."/".$file;
				$finalFile = $this->get('kernel')->getCacheDir()."/".$name.'.jpg';
				$imagine->open($file)->save($finalFile, array(
						'format' => 'jpg',
						'quality' => $quality,
						'resolution-x' => $resolution,
						'resolution-y' => $resolution,
						'resolution-units' => 'ppi'
				));
				$name = $name.'.jpg';
	
				break;
			case 'TIFF':
			case 'TIF':
				$finalFile = $this->get('kernel')->getCacheDir()."/".$name.'.tif';
				$file = $this->get('kernel')->getCacheDir()."/".$file;
				$name = $name.'.tif';
	
				rename($file, $finalFile);
				break;
	
			case 'PDF':
			default:
				$imagine = new \Imagine\Imagick\Imagine();
				$file = $this->get('kernel')->getCacheDir()."/".$file;
				$finalFile = $this->get('kernel')->getCacheDir()."/".$name.'.pdf';
				$imagine->open($file)->save($finalFile, array(
						'format' => 'pdf'
				));
				$name = $name.'.pdf';
	
				break;
		}
	
		$UploadResult = $RESTUpload->UploadNewFile($finalFile, $name, $type, "workspace://SpacesStore/".$nodeId,false);
	
	}
	
	private function doScan($top, $left, $x, $y, $mode, $format, $resolution, $device) {
	
		$command = 'scanimage';
	
		$command .= $top !== false ? ' -t '.$top.'mm' : '';
		$command .= $left !== false ? ' -l '.$left.'mm' : '';
		$command .= $x !== false ? ' -x '.$x.'mm' : '';
		$command .= $y !== false ? ' -y '.$y.'mm' : '';
		$command .= $mode !== false ? ' --mode='.$mode : '';
		$command .= $format !== false ? ' --format='.$format : '';
		$command .= $resolution !== false ? ' --resolution='.$resolution : '';
		$command .= $device !== false ? ' -d \''.$device.'\'' : '';
	
		$tmp_name = 'scan_'.time();
		$tmp_path = $this->get('kernel')->getCacheDir()."/".$tmp_name;
	
		$command .= ' > '.$tmp_path;
	
		exec($command, $output, $err);
	
		return array(
				'output' => $output,
				'cmd' => $command,
				'error' => $err,
				'file' => $tmp_name
		);
	
	}
}
