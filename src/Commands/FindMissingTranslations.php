<?php

namespace VetonMuhaxhiri\Laravelfindmissingtranslations\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
    public function handle()
    {
        $languageDir = base_path(). $this->argument('language directory');
        $baseLanguageDirectory = $languageDir. '/'. $this->argument('base language');

        try {
            $directoriesOfLanguages = File::directories($languageDir);
            $baseLanguageFiles = $this->getFilenames($baseLanguageDirectory);
        } catch (\Exception $e) {
            $this->line('');
            $this->error($e->getMessage());

            exit;
        }

        foreach ($directoriesOfLanguages as $languageDirectory) {
            $languageFiles = $this->getFilenames($languageDirectory);

            $baseLanguageName = explode("/", $baseLanguageDirectory);
            $baseLanguageName = explode("\\", $baseLanguageName[count($baseLanguageName)-1]);
            $baseLanguageName = $baseLanguageName[count($baseLanguageName)-1];

            $languageName = explode("/", $languageDirectory);
            $languageName = explode("\\", $languageName[count($languageName)-1]);
            $languageName = $languageName[count($languageName)-1];

            if ($baseLanguageName == $languageName) {
                //Skip the base language, we don't have to compare language by it's self
                continue;
            }

            $this->info('Comparing '. $baseLanguageName. ' to '. $languageName. '.');

            $this->compareLanguages($baseLanguageDirectory, $baseLanguageFiles, $languageDirectory, $languageFiles, $languageName);
           
            $this->info('');
            $this->info('Successfuly compared all languages');
        }
    }
    
    /**
     * Comparing languages
     *
     * @param String $baseLanguagePath
     * @param String $baseLanguageFiles
     * @param String $languagePath
     * @param Array $languageFiles The list of language files
     * @param String $languageName
     *
     * @return void
     */
    private function compareLanguages($baseLanguagePath, $baseLanguageFiles, $languagePath, $languageFiles, $languageName)
    {
        foreach ($baseLanguageFiles as $languageFile) {
            $baseLanguageFile = File::getRequire($baseLanguagePath. '/'. $languageFile);
            
            if (!in_array($languageFile, $languageFiles)) {
                $this->line('');
                $this->comment('Comparing translations in '. $languageFile. '.');
                $this->error($languageName. '/'. $languageFile. ' file is missing.');
                continue;
            }
            $secondLanguageFile = File::getRequire($languagePath. '/'. $languageFile);
            
            $this->compareFileKeys($baseLanguageFile, $secondLanguageFile, $languageName, $languageFile);
        }
    }

    /**
     *  Compare files and display missing translations
     *
     * @param Array $baseLanguageFileKeys
     * @param Array $secondLanguageFileKeys
     * @param String $languageName
     * @param String $filename
     *
     * @return void
     */
    private function compareFileKeys($baseLanguageFileKeys, $secondLanguageFileKeys, $languageName, $filename)
    {
        $missingKeys = $this->arrayDiffRecursive($baseLanguageFileKeys, $secondLanguageFileKeys);

        if (is_array($missingKeys)) {
            if (count($missingKeys)) {
                $this->line('');
                $this->comment('Comparing translations in '. $filename. '.');
                $this->error('Found missing translations in /' . $languageName. '/'. $filename. '.');

                foreach ($missingKeys as $key) {
                    $this->line('"'.$key.'" is not translated to /'. $languageName. '/'. $filename);
                }
            }
        } else {
            $this->info('Bad file, cannot proccess!');
        }
    }

    /**
     * Compare array keys recursivly
     *
     * @param array $firstArray
     * @param array $secondArray
     *
     * @return array
     */
    private function arrayDiffRecursive($firstArray, $secondArray)
    {
        $outputDiff = [];

        foreach ($firstArray as $key => $value) {
            if (array_key_exists($key, $secondArray)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayDiffRecursive($value, $secondArray[$key]);
                    if (count($recursiveDiff)) {
                        foreach ($recursiveDiff as $diff) {
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
     * @param String $directory
     * @return array
     */
    private function getFilenames($directory)
    {
        $fileNames = [];

        $filesInFolder = File::files($directory);

        foreach ($filesInFolder as $path) {
            $file = pathinfo($path);
            $fileNames[] = $file['basename'];
        }

        return $fileNames;
    }
}