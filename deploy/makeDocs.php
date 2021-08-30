<?php

/*
TODO 

*/

// fichiers individuels
$files = array('docs/conf.py',
               'docs/Makefile',
               'docs/Annex.rst',
               'docs/Changelog.rst',
               'docs/Definitions.rst',
               'docs/FAQ.rst',
               'docs/Introduction.rst',
               'docs/index.rst',
              );

// dossiers complets
$dirs = array( 'docs/images', 
               'docs/Administrator', 
               'docs/Gettingstarted', 
               'docs/Reference', 
               'docs/User', 
              );



////////////////////////////////////////
// Execution
////////////////////////////////////////
shell_exec('php docsSrc/buildDefinitions.php --config 2.3.0.json');

foreach($dirs as $dir){
    copyDirectory($dir);
}

foreach($files as $file){
    copyFile($file);
}

// git stage
// git commit : quel message ? 
// git push : 

//curl -X POST -d "branches=latest" -d "token=ec26b47cf9fc0c19c1513ed5f7d1e22424a9e2fe" https://readthedocs.org/api/v2/webhook/exakat/158686/

print "Finished\n";

////////////////////////////////////////
// Definitions
////////////////////////////////////////

function copyFile(string $file) {
    $dir = dirname($file);
    if (!file_exists('../exakat-docs/'.noDocs($dir))) {
        mkdir('../exakat-docs/'.noDocs($dir), 0755, true);
    }

    if (!file_exists('./'.$file)) {
        print "Skip missing file : $file\n";
        return; 
    }
    if (is_dir('./'.$file)) {
        copyDirectory('./'.$file);
    } else {
        copy('./'.$file, '../exakat-docs/'.noDocs($file));
    }
}

function copyDirectory(string $dir) {
    if (file_exists('../exakat-docs/'.noDocs($dir))) {
        shell_exec("rm -rf ../exakat-docs/".noDocs($dir));
    }
    mkdir('../exakat-docs/'.noDocs($dir), 0755, true);
    
    shell_exec('cp '.$dir. '/* ../exakat-docs/'.noDocs($dir).'/');
}

function noDocs($file)  {
    return substr($file, 5);
}

?>