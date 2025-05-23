<?php

    function uploadFile($fileName,$newname,$upload_directory){
        if (is_uploaded_file(filename: $fileName['tmp_name'])){
            $fname=basename($fileName['name']);
            $uploadFile=$upload_directory.$newname.".".end(explode(".",$fname));
            
            if(move_uploaded_file($fileName['tmp_name'],$uploadFile)){
                $res = "File was successfully Upload";
            }else{
                $res = "Problem uploading File";
            }
        }

        return $res;
    }

    function cleanText($str)
    {
        $strClean=trim($str);
        $strClean=htmlspecialchars($strClean);
        return $strClean;
    }
?>