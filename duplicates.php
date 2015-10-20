<?php

echo "Input path: ";
$path = trim(fgets(STDIN));
if(!is_dir($path)){
    exit('Error: '.$path . ' is not a directory. '. PHP_EOL . 'Exit' . PHP_EOL);
}

$files = new \Duplicates($path);
$files->findDuplicates();


class Duplicates{
    private $path;
    private $resultFileName;
    private $processUnixHiddenFiles;
    private $files = array();

    public function __construct($path, $processUnixHiddenFiles = false, $resultFileName = 'duplicates.txt'){
        $this->path = $path;
        $this->processUnixHiddenFiles = $processUnixHiddenFiles;
        $this->resultFileName = $resultFileName;
    }

    public function findDuplicates(){
        $this->processPath($this->path);
        $this->outputResult();
    }
    // private
    private function processPath($path){
        if ($handle = @opendir($path)) {
            while(($file = readdir($handle))) {
                if($file == '.' || $file == '..'){
                    continue;
                }
                if($file[0] == '.' && !$this->processUnixHiddenFiles){
                    continue;
                }
                $filename = $path . DIRECTORY_SEPARATOR . $file;
                if(is_file($filename)){
                    $this->processFile($filename);
                }else{
                    $this->processPath($filename);
                }
            }
            closedir($handle);
        }else{
            echo("Cannot open dir: ".$path.PHP_EOL);
        }
    }

    private function processFile($path){
        echo 'Process '.$path . PHP_EOL;
        $encode = $this->getFileHash($path);
        $this->files[$encode][] = $path;
    }

    private function getFileHash($path){
        return md5_file($path);
    }

    private function outputResult(){
        $foundFiles = $this->files;
        foreach($foundFiles as $hash => &$files){
            if(count($files) == 1){
                unset($foundFiles[$hash]);
            }
        }unset($hash, $files);

        $resultFileName = $this->resultFileName;
        $result = array();
        $i = 0;
        $countFound = 0;
        foreach($foundFiles as $hash => $files){
            $result[] = (++$i) . PHP_EOL;
            $result[] = implode(PHP_EOL, $files).PHP_EOL;
            $countFound += count($files);
        }unset($hash, $files, $foundFiles);

        echo 'Done. Found '.$countFound.' duplicates.'.PHP_EOL;

        if(file_put_contents($resultFileName, $result)){
            echo 'Result successfully written to file ' . __DIR__ . DIRECTORY_SEPARATOR . $resultFileName . PHP_EOL;
        }else{
            echo 'Error while writing in file ' . __DIR__ . DIRECTORY_SEPARATOR . $resultFileName . PHP_EOL;
        }
    }
}

