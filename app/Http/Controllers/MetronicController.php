<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MetronicController extends Controller{

	/**
	 * Update translations .
	 * This function can be used ONLY on test environment
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postUpdateTranslations(Request $request)
	{
		//Function can be used only on test environment
		//If debug is true it means we are not on test environment
		//So we will return.
		if(config('app.debug') == false){return;}
		$zipFile = $request->file('file');
		$zipFilePath = $zipFile->getRealPath();
		$zip = new \ZipArchive;
		if ($zip->open($zipFilePath) === TRUE) {
            $pathToExtract = base_path('../callburn-angular/assets/translations/');
		    $zip->extractTo($pathToExtract);
		    $zip->close();
		    \Log::info('extracted.');
		    return response()->json(['error' => ['no' => 0, 'text' => 'extracted.']]);
		} else {
            \Log::info('Unable to open ZIP.');
		    return response()->json(['error' => ['no' => -1, 'text' => 'Unable to open ZIP.']]);
		}
	}

}
