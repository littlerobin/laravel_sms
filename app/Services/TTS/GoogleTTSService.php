<?php namespace App\Services\TTS;

use \GetId3\GetId3Core as GetId3;
use App\Helper;
use App\Services\FileService;

class GoogleTTSService{

	/**
	 * Create a new instance of GoogleTTSService class
	 *
	 * @return void
	 */
	public function __construct()
	{
	    $this->fileRepo = new FileService();
	}

	/**
	 * Create an audio file from text with GOOGLE
	 *
	 * @param string $text
	 * @param string $language
	 * @param string $userId
	 * @return File
	 */
	public function createFromText($text, $language, $user,  $ttsPrice, $savedFrom = null)
	{
		$googleTts = new \App\Services\GoogleTTSService();
		$uploadFolder = public_path() . '/uploads/audio/';
		$newName = str_random();
		$fileExtension = 'mp3';
		$path = $uploadFolder . $newName . '.' . $fileExtension;
		$wavFileName = $uploadFolder . $newName;
		$googleTts->converTextToMP3($text, $path, $language, $wavFileName);
		
		$file = \App\Models\File::create([
			'orig_filename' => $newName . '.' . $fileExtension,
			'map_filename' => $newName . '.' . $fileExtension,
			'extension' => $fileExtension,
			'stripped_name' => $newName,
			'tts_language' => $language,
			'tts_text' => $text,
			'user_id' => $user->_id,
			'type' => 'TTS',
			'saved_from' => $savedFrom,
			'is_template' => 1,
			'cost' =>  $ttsPrice
			]);
        $gsmAudioFile = Helper::_stripFileExtension($newName).'.gsm';
        $cmd = 'sox ' . $wavFileName . '.mp3 -r 8000 -c 1 ' . $uploadFolder . $gsmAudioFile . ' silence 1 0.1 1%';
        $response = shell_exec( $cmd );
        $length = $this->fileRepo->getFileSizeByPK($file->_id);
        $file->length = ceil($length/1000);
        $file->save();
        return $file;
	}

	public function converTextToMP3($str,$outfile, $lang, $wavFileName)
	{
	    $base_url='http://translate.google.com/translate_tts?tl=' . $lang . '&client=tw-ob&ie=UTF-8&q=';
	    $words = $this->splitString($str);
	    $files=array();
	    foreach($words as $word)
	    {
	        $url= $base_url.urlencode($word);
	        $filename = md5($word).".mp3";
	        //echo ".";
	        if(!$this->downloadMP3($url,$filename))
	        {
	            //echo "Failed to Download URL.".$url."n";
	        }
	        else
	        {
	            $files[] = $filename;
	        }
	    }
	    if(count($files) == count($words)) //if all the strings are converted
	        $this->CombineMultipleMP3sTo($outfile,$files, $wavFileName, $lang);
	    else
	        //echo "ERROR. Unable to convert n";
	 
	    foreach($files as $file)
	    {
	        unlink($file);
	    }
	}

	private function splitString($str)
	{
	    $ret = array();
	    $arr = explode(" ",$str);
	    $constr = '';
	    for($i = 0; $i < count($arr); $i++)
	    {
	        if(strlen($constr.$arr[$i]." ") < 98)
	        {
	            $constr =$constr.$arr[$i]." ";
	        }
	        else
	        {
	            $ret[] = $constr;
	            $constr = '';
	            $i--;
	        }
	 
	    }
	    $ret[]=$constr;
	    return $ret;
	}

	private function downloadMP3($url,$file)
	{
	    $ch = curl_init();  
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	    $output=curl_exec($ch);
	    curl_close($ch);
	    if($output === false)   
	    return false;
	 
	    $fp = fopen($file,"wb");
	    fwrite($fp,$output);
	    fclose($fp);
	    return true;
	}


	private function CombineMultipleMP3sTo($FilenameOut, $FilenamesIn, $wavFileName, $lang) {
	 
	    foreach ($FilenamesIn as $nextinputfilename) {
	        if (!is_readable($nextinputfilename)) {
	            //echo 'Cannot read "'.$nextinputfilename.'"<BR>';
	            return false;
	        }
	    }
	 
	    ob_start();
	    if ($fp_output = fopen($FilenameOut, 'wb')) {
	 
	        ob_end_clean();
	        // Initialize getID3 engine
	        $getID3 = new GetId3();
	        foreach ($FilenamesIn as $nextinputfilename) {
	 
	            $CurrentFileInfo = $getID3->analyze($nextinputfilename);
	            if ($CurrentFileInfo['fileformat'] == 'mp3') {
	 
	                ob_start();
	                if ($fp_source = fopen($nextinputfilename, 'rb')) {
	 
	                    ob_end_clean();
	                    $CurrentOutputPosition = ftell($fp_output);
	 
	                    // copy audio data from first file
	                    fseek($fp_source, $CurrentFileInfo['avdataoffset'], SEEK_SET);
	                    while (!feof($fp_source) && (ftell($fp_source) < $CurrentFileInfo['avdataend'])) {
	                        fwrite($fp_output, fread($fp_source, 32768));
	                    }
	                    fclose($fp_source);
	 
	                    // trim post-audio data (if any) copied from first file that we don't need or want
	                    $EndOfFileOffset = $CurrentOutputPosition + ($CurrentFileInfo['avdataend'] - $CurrentFileInfo['avdataoffset']);
	                    fseek($fp_output, $EndOfFileOffset, SEEK_SET);
	                    ftruncate($fp_output, $EndOfFileOffset);
	 
	                } else {
	 
	                    $errormessage = ob_get_contents();
	                    ob_end_clean();
	                    //echo 'failed to open '.$nextinputfilename.' for reading';
	                    fclose($fp_output);
	                    return false;
	 
	                }
	 
	            } else {
	 
	                //echo $nextinputfilename.' is not MP3 format';
	                fclose($fp_output);
	                return false;
	 
	            }
	 
	        }
	 
	    } else {
	 
	        $errormessage = ob_get_contents();
	        ob_end_clean();
	        //echo 'failed to open '.$FilenameOut.' for writing';
	        return false;
	 
	    }
	 
	    fclose($fp_output);

	    //Speed up the file
	   	$ttsConfig = \App\Models\TtsConfiguration::where('google_tts_code', $lang)->first();
	    if($ttsConfig->google_tts_speed && $ttsConfig->google_tts_speed != 1){
	    	exec('sox ' . $wavFileName . '.mp3' . ' -q -r 44100 -t mp3 ' . $wavFileName . 'temp.mp3' . ' tempo -s ' . $ttsConfig->google_tts_speed);
	    	unlink($wavFileName . '.mp3');
	    	rename($wavFileName . 'temp.mp3', $wavFileName . '.mp3');
	    }
	    return true;
	}

}