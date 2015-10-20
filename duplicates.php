<?php
$path = 'd:\44\test';
$files = new \Duplicates($path);
$files->findDuplicates();

class Duplicates{

    private $path;
    private $files = array();
    private $sizes = array();

    public function __construct($path){
        $this->path = $path;
    }

    private function getFile($path){
        if ($handle = @opendir($path)) {
            while(($file = readdir($handle))) {
                if($file == '.' || $file == '..'){
                    continue;
                }
                $name = $path . DIRECTORY_SEPARATOR . $file;
                if(is_file($name)){
                    $this->processFile($name);
                }else{
                    $this->getFile($name);
                }
            }
            closedir($handle);
        }else{
            die("Cann't open dir: ".$path.PHP_EOL);
        }
    }

    private function processFile($path){
        echo 'Process '.$path . PHP_EOL;
        $encode = md5_file($path);
        $this->files[$encode][] = $path;
    }

    public function findDuplicates(){
        $this->getFile($this->path);
        print_r($this->files);
    }

}



