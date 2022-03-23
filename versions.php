<?php

$files = glob('/var/www/versions/*');

$latest = array('version'   => '1.4.0',
                'phar'      => '',
                'signature' => '');

$versions = array();
$filesHTML = array();
foreach($files as $path) {
    $file = basename($path);
    switch(substr($file, -4)) {
        case 'phar' : 
            $size = size_format(filesize($path));
            $date = date('d-M-Y H:i', filectime($path));

            if (!preg_match('/exakat-(\d+\.\d+\.\d+[abc]?)/', $file, $r)) {
                break;
            }
            $version = $r[1];
            
            $md5    = md5_file($path);
            file_put_contents($path.'.md5', $md5.'  '.basename($path).PHP_EOL);
            $sha256 = hash('sha256', file_get_contents($path));
            file_put_contents($path.'.sha256', $sha256.'  '.basename($path));

            $row = <<<HTML
<tr>
    <td valign="top"><img src="phar.png" alt="Signature for exakat version $version"></td>
    <td><a href="index.php?file=$file" alt="Download exakat version $version">$file</a></td>
    <td align="right">$date</td>
    <td align="right">$size </td>
    <td><pre id="md5"><a href="index.php?file=$file.md5">$md5</a></pre></td>
    <td><pre id="sha256"><a href="index.php?file=$file.sha256">$sha256</a></pre></td>
</tr>

HTML;
            $filesHTML[$version.'b'] = $row;
            $versions[] = $version;

	    if (version_compare($version, $latest['version']) >= 0) {
    	    $latest['version'] = $version;
	    	$row = <<<HTML
<tr>
    <td valign="top"><img src="phar.png" alt="Signature for exakat version $version (latest)"></td>
    <td><a href="index.php?file=$file"  alt="Download exakat version $version (Latest)">latest</a></td>
    <td align="right">$date  </td>
    <td align="right">$size </td>
    <td><pre id="md5"><a href="index.php?file=$file.md5">$md5</a></pre></td>
    <td><pre id="sha256"><a href="index.php?file=$file.sha256">$sha256</a></pre></td>
</tr>

HTML;
                $latest['phar']      = $row;
            }

            break;
            
        case '.sig' : 
            $size = size_format(filesize($path));
            $date = date('d-M-Y H:i', filectime($path));

            preg_match('/exakat-(\d+\.\d+\.\d+)/', $file, $r);
            $version = $r[1];

            $md5    = md5_file($path);
            file_put_contents($path.'.md5', $md5.'  '.basename($path).PHP_EOL);
            $sha256 = hash('sha256', file_get_contents($path));
            file_put_contents($path.'.sha256', $sha256.'  '.basename($path));

            $row = <<<HTML
<tr>
    <td valign="top"><img src="signature.png" alt="Signature for exakat version $version"></td>
    <td><a href="$file" alt="Download exakat version $version">$file</a></td>
    <td align="right">$date  </td>
    <td align="right">$size </td>
    <td><pre id="md5"><a href="index.php?file=$file.md5">$md5</a></pre></td>
    <td><pre id="sha256"><a href="index.php?file=$file.sha256">$sha256</a></pre></td>
</tr>

HTML;

            $filesHTML[$version.'a'] = $row;


            if (version_compare($version, $latest['version']) >= 0) {
                $latest['version'] = $version;
		$row = <<<HTML
<tr>
    <td valign="top"><img src="signature.png" alt="Signature for exakat version $version (latest)"></td>
    <td><a href="$file"  alt="Download exakat version $version (Latest)">latest.sig</a></td>
    <td align="right">$date  </td>
    <td align="right">$size </td>
    <td><pre id="md5"><a href="index.php?file=$file.md5">$md5</a></pre></td>
    <td><pre id="sha256"><a href="index.php?file=$file.sha256">$sha256</a></pre></td>
</tr>

HTML;
                $latest['signature'] = $row;
            }


            break;

        case '.zip' : 
            if (!preg_match('/^apache-tinkerpop-gremlin-server-(\d+\.\d+\.\d+)-bin.zip$/', $file, $r)) {
                break;
            }
            $version = $r[1];
            
            $md5    = md5_file($path);
            file_put_contents($path.'.md5', $md5.'  '.basename($path).PHP_EOL);
            $sha256 = hash('sha256', file_get_contents($path));
            file_put_contents($path.'.sha256', $sha256.'  '.basename($path));
            
            // No display on html
            break;

        default :
            $file .= '';
    }
}

usort($versions, 'version_compare');
$versions = array_reverse($versions);
$latestVersion = $versions[0];
print count($versions)." versions found : " . min($versions).'-'.max($versions)."\n";
print"Current version : $latestVersion\n";

uksort($filesHTML, function($a, $b) { return version_compare($b, $a); });
//krsort($filesHTML);
$filesHTML = implode("\n", $filesHTML);
$date = date('c');

$html = <<<HTML

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<html>
 <head>
  <title>Exakat Community Edition versions</title>
  <META NAME="Date" CONTENT="$date">
 </head>
 <body>
<h1>Versions of Exakat CE</h1>
<table>
    <tr>
        <th>&nbsp;</th>
        <th>Name</th>
        <th>Last modified</th>
        <th>Size</th>
        <th>MD5</th>
        <th>SHA256</th>
    </tr>
<tr><th colspan="6"><hr></th></tr>
$latest[phar]
$latest[signature]
$filesHTML
<tr><th colspan="6"><hr></th></tr>
</table>
</body></html>

HTML;

$php = '<?php

/*-- generated on '.$date.' --*/
$latest = \''.$latestVersion.'\';
$versions = '.var_export($versions, true).';

'.<<<'PHP'

if (isset($_GET['file'])) {
    if (is_string($_GET['file'])) {
        if ($_GET['file'] === 'latest') {
            download('exakat-'.$latest.'.phar');
        } elseif ($_GET['file'] === 'latest.sig') {
            download('exakat-'.$latest.'.sig');
        } elseif ($_GET['file'] === 'latest.md5') {
            download('exakat-'.$latest.'.phar.md5');
        } elseif ($_GET['file'] === 'latest.sha256') {
            download('exakat-'.$latest.'.phar.sha256');
        } elseif (preg_match('/^apache-tinkerpop-gremlin-server-3.\d.\d-bin.zip$/', $_GET['file']) && file_exists('./'.$_GET['file'])) {
            download($_GET['file']);
        } else {
            $version = str_replace(array('exakat-', '.md5', '.sha256', '.phar', '.sig'), '', $_GET['file']);
            if (in_array($version, $versions)) {
                if (strpos($_GET['file'], '.phar.md5')) {
                    download('exakat-'.$version.'.phar.md5');
                } elseif (strpos($_GET['file'], '.phar.sha256')) {
                    download('exakat-'.$version.'.phar.sha256');
                } elseif (strpos($_GET['file'], '.phar')) {
                    download('exakat-'.$version.'.phar');
                } elseif (strpos($_GET['file'], '.sig.md5')) {
                    download('exakat-'.$version.'.sig.md5');
                } elseif (strpos($_GET['file'], '.sig.sha256')) {
                    download('exakat-'.$version.'.sig.sha256');
                } elseif (strpos($_GET['file'], '.sig')) {
                    download('exakat-'.$version.'.sig');
                } 
            }
        } // else ignore, go to HTML page
    } // else ignore, go to HTML page
}

function download($file) {
    $fp = fopen('download.log', 'a');
    fwrite($fp, "$file\t".date('r').PHP_EOL);
    fclose($fp);
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file)); 
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));

    readfile($file);
    die();
}

?
PHP
.'>';

print file_put_contents('/var/www/versions/index.php',$php."\n".$html)." bytes written\n";
$res = shell_exec('php -l /var/www/versions/index.php');
if (strpos($res, 'No syntax error') === false ) {
    print "Error : $res\n";
}

function size_format($size) {
    $units = ['', 'k', 'M', 'G', 'T'];
    
    $size *= 10;
    $u = 0;
    while ($size > 5120) {
        $size = $size / 1024;
        ++$u;
    }
    
    return (floor($size) / 10) . ' ' . $units[$u];
}

?>
