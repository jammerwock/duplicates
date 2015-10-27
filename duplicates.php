<?php

echo "Input path: ";
$path = trim(fgets(STDIN));
if(!is_dir($path)){
    exit('Error: '.$path . ' is not a directory. '. PHP_EOL . 'Exit' . PHP_EOL);
}
$files = new \DuplicateFinder($path);
$files->findDuplicates();


class DuplicateFinder{
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
        echo 'Start'.PHP_EOL;
        $this
            ->processPath($this->path)
            ->removeUnique()
            ->hashCheck()
            ->removeUnique()
            ->byteCheck()
            ->outputResult();
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
        return $this;
    }

    private function processFile($path){
        echo 'Process '.$path . PHP_EOL;
        $this->files[$this->getFileSize($path)][] = $path;
    }

    private function removeUnique(){
        echo 'Removing unique... ';
        $this->files = array_filter($this->files, function($val){
            return count($val) > 1;
        });
        echo 'Done.'.PHP_EOL;
        return $this;
    }


    private function hashCheck(){
        echo 'Hash checking... ';
        $newFiles = array();
        foreach($this->files as $files){
            foreach($files as $file){
                $newFiles[$this->getFileHash($file)][] = $file;
            }unset($file);
        }unset($files);
        $this->files = $newFiles;
        unset($newFiles);
        echo 'Done.'.PHP_EOL;
        return $this;
    }

    private function byteCheck(){
        echo 'Byte checking... ';
        $res = array();
        foreach($this->files as $files){
            foreach ($files as $file1) {
                $res[$file1][] = $file1;
                foreach ($files as $file2) {
                    if($file1 == $file2){
                        continue;
                    }
                    if($this->isFilesIdentical($file1, $file2)){
                        $res[$file1][] = $file2;
                    }
                }
            }
        }unset($files);

        foreach($res as $k => &$v){
            foreach($v as $val){
                if($val == $k){ continue; }
                unset($res[$val]);
            }
        }unset($k, $v);
        $this->files = array_values($res);
        unset($res);
        echo 'Done.'.PHP_EOL;
        return $this;
    }

    private function getFileHash($path){
        return md5_file($path);
    }

    private function getFileSize($path){
        return filesize($path);
    }

    function isFilesIdentical($file_a, $file_b){

        static $readSize = 4096;
        $fp_a = fopen($file_a, 'rb');
        $fp_b = fopen($file_b, 'rb');

        $result = true;
        while (!feof($fp_a) and !feof($fp_b)) {
            if (fread($fp_a, $readSize) !== fread($fp_b, $readSize)) {
                $result = false;
                break;
            }
        }
        fclose($fp_a);
        fclose($fp_b);
        return $result;
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

        echo 'Finish. Found '.$countFound.' duplicates.'.PHP_EOL;

        if(!$result){
            echo 'No duplicates was found.'.PHP_EOL;
        }elseif(file_put_contents($resultFileName, $result)){
            echo 'Result successfully written to file ' . __DIR__ . DIRECTORY_SEPARATOR . $resultFileName . PHP_EOL;
        }else{
            echo 'Error while writing in file ' . __DIR__ . DIRECTORY_SEPARATOR . $resultFileName . PHP_EOL;
        }
    }
}

