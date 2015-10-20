<?php




$path = 'd:\44\test';
//echo "Input path: ";
//$path = trim(fgets(STDIN));
//getFile($path);
getFile($path);

function getFile($path){
    if ($handle = @opendir($path)) {
        while(($file = readdir($handle))) {
            if($file == '.' || $file == '..'){
                continue;
            }
            $name = $path . DIRECTORY_SEPARATOR . $file;
            if(is_file($name)){
                processFile ($name);
            }else{
                getFile($name);
            }
        }
        closedir($handle);
    }else{
        die("Cann't open dir: ".$path.PHP_EOL);
    }
}

function processFile($path){
    echo $path. PHP_EOL;
}