<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class FindMissingTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:missing {language directory : Relative path of language directory for ex. /resources/lang is a directory that contains all supported language.} 
                                    {base language : Base language for ex. en. All other languages are compared to this language.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command helps developers to finding words which are not translated, by comparing one base language to others.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $languageDir = base_path(). $this->argument('language directory');
        $baseLangDir = $languageDir. '/'. $this->argument('base language');

        try {
            $languagesDir = File::directories($languageDir); 
            $baseLanguageFiles = $this->getFilesName($baseLangDir);
        } catch (\Exception $e) {
            $this->line('');
            $this->error($e->getMessage());
            exit;
        }

        foreach($languagesDir as $secondLanguageDir) {
            $this->line('');

            $secondLanguageFiles = $this->getFilesName($secondLanguageDir);
            $baseLangName = explode("/", $baseLangDir);
            $baseLangName = explode("\\", $baseLangName[count($baseLangName)-1]);
            $baseLangName = $baseLangName[count($baseLangName)-1];

            $secondLangName = explode("/", $secondLanguageDir);
            $secondLangName = explode("\\", $secondLangName[count($secondLangName)-1]);
            $secondLangName = $secondLangName[count($secondLangName)-1];

            if($baseLangName == $secondLangName) { 
                continue;
            }

            $this->info('Comparing '. $baseLangName. ' to '. $secondLangName. '.');
            $this->compareLanguages($baseLangDir, $baseLanguageFiles, $secondLanguageDir, $secondLanguageFiles,$baseLangName,$secondLangName);
        }
    }
    
    /**
     * Comparing languages
     * 
     * @param String $baseLangDir
     * @param String $baseLanguageFiles
     * @param String $secondLanguageDir
     * @param String $secondLanguageFiles
     * @param String $lang1
     * @param String $lang2
     * 
     * @return void
     */
    function compareLanguages($baseLangDir, $baseLanguageFiles, $secondLanguageDir, $secondLanguageFiles, $lang1, $lang2) {
        foreach($baseLanguageFiles as $languageFile) {
            $file = File::getRequire($baseLangDir. '/'. $languageFile);
            
            if(!in_array($languageFile,$secondLanguageFiles)) {
                $this->line('');
                $this->comment('Comparing translations in '. $languageFile. '.');
                $this->error($lang2. '/'. $languageFile. ' file is missing.');
                continue;
            }
            $secondLanguageFile = File::getRequire($secondLanguageDir. '/'. $languageFile);
            
            $this->compareFiles($file,$secondLanguageFile,$lang1,$lang2,$languageFile);
        }
    }

    /**
     *  Display missing translations
     *
     * @param Array $array1
     * @param Array $array2
     * @param String $lang1 
     * @param String $lang2 
     * @param String $filename 
     *
     * @return void
     */
    function compareFiles($array1, $array2, $lang1, $lang2, $filename) {
        $diff_result = $this->arrayDiffRecursive($array1, $array2);
        if(is_array($diff_result)) {
            if(count($diff_result)) {
                $this->line('');
                $this->comment('Comparing translations in '. $filename. '.');
                $this->error('Found missing translations in /' . $lang2. '/'. $filename. '.');
                foreach($diff_result as $result) {
                    $this->line('"'.$result.'" is not translated to /'. $lang2. '/'. $filename);
                }
            }
        } else {
            $this->info('Not array!');
        }
    }

    /**
     * Compare array keys recursivly
     * 
     * @param Array $arr1
     * @param Array $arr2
     * 
     * @return Array
     */
    function arrayDiffRecursive($arr1, $arr2) {
        $outputDiff = [];
        foreach($arr1 as $key => $value) {
            if(array_key_exists($key, $arr2)) {
                if(is_array($value)) {
                    $recursiveDiff = $this->arrayDiffRecursive($value, $arr2[$key]);
                    if(count($recursiveDiff)) {
                        foreach($recursiveDiff as $diff) {
                            $outputDiff[] = $diff;
                        }
                    }
                }
            } else {
                $outputDiff[] = $key;
            }
        }
        return $outputDiff;
    }

    /**
     * Get filenames of directory
     *
     * @param String $folder
     * @return Array
     */
    public function getFilesName($folder) {
        $fileNames = [];
        $filesInFolder = File::files($folder);     
        foreach($filesInFolder as $path) { 
            $file = pathinfo($path);
            $fileNames[] = $file['basename'];
        }
        return $fileNames;
    } 
}
