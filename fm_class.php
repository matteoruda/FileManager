<?php
DEFINE('DS', DIRECTORY_SEPARATOR); 

Class FileManager {

	/**
	 * Dato il percorso di una cartella scansiona i files/cartelle al suo interno e restituisce un
	 * array con informazioni su ogni file/cartella trovato 
	 * @param  [type] $dir_path 
	 * @return [mix] Array di files se ha avuto successo altrimenti false
	 */
	public function scan($dir_path)
	{
		
		if(is_dir($dir_path))
		{
			$files_temp = scandir($dir_path);
			//restituisce l'array di files senza "."
			$file_temp =  array_diff($files_temp, array('.'));
			foreach ($files_temp as $file) 
			{
				$full_path = $dir_path . DS . $file;
				
				if(is_dir($full_path))
				{
					$files[] = array(
						'name' => $file,
						'type' => 'Folder',
						'dir_path' => realpath($full_path)
					);

				}
				
				elseif(is_file($full_path))
				{
					$files[] = array(
						'name' => $file,
						'type' => 'File',
						'dir_path' => $dir_path,
						'size' => $this->formatSizeUnits(filesize($full_path)),
						'extension' => pathinfo($full_path, PATHINFO_EXTENSION) ? : 'Unknown'
					);
				}
			}
			return $files;
		}
		else
		{
			//directory non valida
			return false;
		}
	}
 

	/**
	 * Dato un percorso ne cancella il file (se esiste)
	 * @param  [string] $path il percorso del file da cancellare
	 * @return [bool]       
	 */
	public function deleteFile($path)
	{

		if (is_file($path) && file_exists($path)) 
			return unlink($path);

		return false;	
	}


	/**
	 * Rimuove una cartella e i suoi file/cartelle al suo interno (ricorsiva)
	 * @param  [string] $path il percorso della cartella da cacncellare
	 * @return [bool]       vero se la cancellazione della cartella è riuscita altrimenti falso
	 */
	public function deleteDir($path)
	{
		echo $path;
		if(is_dir($path))
		{
			$files = array_diff(scandir($path),array('.','..'));
			foreach ($files as $file) 
			{
				$file_path = $path . DS . $file;
				if(is_dir($file_path))
				{
					$this->deleteDir($file_path);
				}
				if(is_file($file_path))
				{
					unlink($file_path);
				}
			}
			return rmdir($path);
		}
		return false;
	}


	/**
	 * Dato il percorso di un archivio ZIP lo estrae nel percorso indicato da $extract_to_path 
	 * (se vuoto estrae nella stessa cartella)
	 * @param  [string] $path           
	 * @param  [string] $extract_to_path
	 * @return [bool]                 
	 */
	public function extractZip($path, $extract_to_path = '')
	{
		if($extract_to_path == '')
			$dir_path = dirname($path);
		else
			$dir_path = $extract_to_path;

		$zipArchive = new ZipArchive();
		
		$result = $zipArchive->open($path);
		if ($result === TRUE) 
		{
		    $zipArchive ->extractTo($dir_path);
		    $zipArchive ->close();
		    return $result;
		}

		return false;
	}

	public function extractRar($path, $extract_to_path = '')
	{
		if($extract_to_path == '')
			$dir_path = dirname($path);
		else
			$dir_path = $extract_to_path;

		if(!class_exists('RarArchive'))
		{
			echo "RarArchive not installed";
			return false;
		}

		$rarArchive = RarArchive::open($path);

		if($rarArchive)
		{
			$entries = $rarArchive->getEntries();
			foreach ($entries as $entry) {
			    $entry->extract($dir_path);
			}
			$archive->close();
			return true;
		}

		return false;
	}

	/**
	 * Converte bytes in KB/MB
	 * @param  [int]	 bytes
	 * @return [string]  bytes convertiti in una stringa leggibile
	 */
	private function formatSizeUnits($bytes)
    {
        
        if ($bytes >= pow(1024,2))
        {
            $bytes = number_format($bytes / pow(1024,2), 1) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 1) . ' KB';
        }
        elseif ($bytes > 0)
        {
            $bytes = $bytes . ' Bytes';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}

}//Fine classe FileManager


//Example
$root_directory = "C:".DS."xampp".DS."htdocs";

$file_manager = new FileManager();
$result = $file_manager->scan($root_directory);
echo "<pre>";
print_r($result);
echo "</pre>";


?>