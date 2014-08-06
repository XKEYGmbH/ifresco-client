<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib\Mimetype;
/**
 * Mimetype handler
 *
 * @author Tom Reitsma <treitsma@rse.nl>
 * @version 0.5
 */
Class MimetypeHandler
{
	/**
	 * @var array $mimeTypes
	 */
	private $mimeTypes = array();
	
	/**
	 * @var string $mime_ini_location
	 * @desc The location of the ini file that contains the mimetypes
	 */
	private $mime_ini_location = "mime_types.ini";

	/**
	 * Class constructor
	 */
	public function __construct()
	{
        $this->mime_ini_location = __DIR__.DIRECTORY_SEPARATOR.$this->mime_ini_location;
		$this->mimeTypes = parse_ini_file($this->mime_ini_location, false);
	}

	/**
	 * Loads another mime type file
	 * 
	 * @var string $mime_ini_location
	 * @return void
	 */
	public function loadIni($mime_ini_location)
	{
		if(!file_exists($mime_ini_location))
		{
			throw new \Exception("File {$mime_ini_location} not found.");
		}
		
		$newEntries = parse_ini_file($mime_ini_location, false);
		
		foreach($newEntries as $key => $value)
		{
			$this->mimeTypes[$key] = $value;
		}
	}
	
	/**
	 * Gets the mimetype of the string given in the constructor, or the string given as the first parameter
	 *
	 * @param string $filename
	 * @return string mimetype
	 */
	public function getMimetype($filename=false)
	{
		if(count($this->mimeTypes) == 0)
		{
			$this->__construct();
		}
		
		if($filename == false || !is_string($filename))
		{
			throw new \Exception("No input specified.");
		}

		$exploded = explode(".", $filename);
		$ext = $exploded[count($exploded)-1];
		if(!$this->mimetypeExists($ext))
		{
		    return 'text/plain';
		}
		else
		{
		    return $this->mimeTypes[$ext];
		}
	}
	
	/**
	 * Adds a mimetype to the array
	 * 
	 * @param string $ext 		Mime extension
	 * @param string $fileType	Filetype (Example: text/plain)
	 * @param bool $writeToFile	Determines wether to add the mimetype to the ini file
	 * @return bool
	 */
	public function addMimetype($ext, $fileType, $writeToFile = false)
	{
		if($writeToFile == true)
		{
			if(!$this->mimetypeExists($ext))
			{
				$fp = fopen($this->mime_ini_location, "a+");
				fwrite($fp, sprintf("\n\n; Custom mimetype\n%s = %s",$ext,$fileType));
				fclose($fp);
			}
		}
		
		$this->mimeTypes[$ext] = $fileType;
		
		return true;
	}
	
	/**
	 * Checks wether a mimetype exists
	 * 
	 * @param string $ext	Extension
	 * @return bool
	 */
	public function mimetypeExists($ext)
	{
		return isset($this->mimeTypes[$ext])?true:false;
	}
	
	/**
	 * Gets all the currently loaded mimetypes
	 * 
	 * @return array mimetypes
	 */
	public function getMimetypes()
	{
		return $this->mimeTypes;
	}
}

?>