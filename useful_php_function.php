<?php

function diffMysqlDateTime($date1, $date2, $full = false){ 
		if(!$date1 || !$date2) { return false; }
		$dtf = new DateTime($date1);
		$dtt = new DateTime($date2);
		$diff = $dtf->diff($dtt);
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
		return $string; 
		
		//if (!$full) $string = array_slice($string, 0, 1);
		//return $string ? implode(', ', $string) . ' ago' : 'just now';
		
}

function generateRandomCode($length) {
	$charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$str = '';
	$count = strlen($charset);
	while ($length--) {
			$str .= $charset[mt_rand(0, $count-1)];
	}
	return $str;
}


function objectToArray( $object ) {
	if( !is_object( $object ) && !is_array( $object ) ) {
		return $object;
	}
	if( is_object( $object ) ) {
		$object = (array) $object;
	}
	return array_map( 'objectToArray', $object );
}
function getUserIP(){
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] )
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED'] )
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR'])
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED'])
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if($_SERVER['REMOTE_ADDR'])
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';

	return $ipaddress;
}
function indexArrayByField($arr, $field){
	$indexedArr = array();
	array_walk($arr, function($item) use(&$indexedArr, &$field){
		if(is_object($item) && isset($item->$field)){
			$indexedArr[$item->$field] = $item;
		} else if(is_array($item) && isset($item[$field]){
			$indexedArr[$item[$field]] = $item;
		} else {
			$indexedArr[] = $item;
		}
	});
	return $indexedArr;
}

/* turns urls to hyperlinks */
/* USAGE: echo urls_to_links('http://daneden.me'); outputs <a href="http://daneden.me">http://daneden.me</a> */
function urls_to_links($str) { /* Credit for this function goes to @coleydotco */
    $pattern = '/((?:http|https)(?::\\/{2}[\\w]+)(?:[\\/|\\.]?)(?:[^\\s"]*))/is';
    $replace = '<a target="blank" href="$1">$1</a>';
    return preg_replace($pattern, $replace, $str);
}
function is_valid_email($email, $test_mx = false) { /* checks if email address is valid */
	if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
		if($test_mx)
		{
			list($username, $domain) = split("@", $email);
			return getmxrr($domain, $mxrecords);
		}
		else
			return true;
	else
		return false;
}
function show_gravatar($email, $size, $default, $rating) { /* allows use of gravatars */
	echo '<img src="http://www.gravatar.com/avatar.php?gravatar_id='.md5($email).
		'&default='.$default.'&size='.$size.'&rating='.$rating.'" width="'.$size.'px"
		height="'.$size.'px" />';
}

/*Useful magic_quotes_gpc hack*/
if (get_magic_quotes_gpc())
{
  $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
  while (list($key, $val) = each($process))
  {
    foreach ($val as $k => $v)
    {
      unset($process[$key][$k]);
      if (is_array($v))
      {
        $process[$key][stripslashes($k)] = $v;
        $process[] = &$process[$key][stripslashes($k)];
      }
      else
      {
        $process[$key][stripslashes($k)] = stripslashes($v);
      }
    }
  }
  unset($process);
}


// EXAMPLE:     EXPORT_TABLES("localhost","user","pass","db_name" );  
//optional: 5th parameter(array) for specific tables: array("mytable1","mytable2","mytable3")   
//optional: 6th parameter(array) to adequatelly replace strings in DB!! MUST READ!!!! - https://goo.gl/2fZDQL
	
function EXPORT_TABLES($host,$user,$pass,$name,       $tables=false, $backup_name=false, $replacements=array('OLD_DOMAIN.com','NEW_DOMAIN.com') ){ 
	set_time_limit(3000); $mysqli = new mysqli($host,$user,$pass,$name); $mysqli->select_db($name); $mysqli->query("SET NAMES 'utf8'");
	$queryTables = $mysqli->query('SHOW TABLES'); while($row = $queryTables->fetch_row()) { $target_tables[] = $row[0]; }	if($tables !== false) { $target_tables = array_intersect( $target_tables, $tables); } 
	$content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$name."`\r\n--\r\n\r\n\r\n";
	foreach($target_tables as $table){
		if (empty($table)){ continue; } 
		$result	= $mysqli->query('SELECT * FROM `'.$table.'`');  	$fields_amount=$result->field_count;  $rows_num=$mysqli->affected_rows; 	$res = $mysqli->query('SHOW CREATE TABLE '.$table);	$TableMLine=$res->fetch_row(); 
		$content .= "\n\n".$TableMLine[1].";\n\n";
		for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
			while($row = $result->fetch_row())	{ //when started (and every after 100 command cycle):
				if ($st_counter%100 == 0 || $st_counter == 0 )	{$content .= "\nINSERT INTO ".$table." VALUES";}
					$content .= "\n(";    for($j=0; $j<$fields_amount; $j++){ $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ;}  else{$content .= '""';}	   if ($j<($fields_amount-1)){$content.= ',';}   }        $content .=")";
				//every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
				if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";}	$st_counter=$st_counter+1;
			}
		} $content .="\n\n\n";
	}
	$content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";                              if (function_exists('DOMAIN_or_STRING_modifier_in_DB')) { $content = DOMAIN_or_STRING_modifier_in_DB($replacements[0], $replacements[1], $content); }
	$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
	ob_get_clean(); header('Content-Type: application/octet-stream');	header("Content-Transfer-Encoding: Binary"); header("Content-disposition: attachment; filename=\"".$backup_name."\"");
	echo $content; exit;
}    
// EXAMPLE:	IMPORT_TABLES("localhost","user","pass","db_name", "my_baseeee.sql"); //TABLES WILL BE OVERWRITTEN
//optional: 6th parameter(array) to adequatelly replace strings in DB!! MUST READ!!!! - https://goo.gl/2fZDQL 
function IMPORT_TABLES($host,$user,$pass,$dbname, $sql_file_OR_content,      $replacements=array('OLD_DOMAIN.com','NEW_DOMAIN.com') ){
	set_time_limit(3000); $SQL_CONTENT = (strlen($sql_file_OR_content) > 200 ?  $sql_file_OR_content : file_get_contents($sql_file_OR_content)  );        if (function_exists('DOMAIN_or_STRING_modifier_in_DB')) { $SQL_CONTENT = DOMAIN_or_STRING_modifier_in_DB($replacements[0], $replacements[1], $SQL_CONTENT); }
	$allLines = explode("\n",$SQL_CONTENT); 
	$mysqli = new mysqli($host, $user, $pass, $dbname); if (mysqli_connect_errno()){echo "Failed to connect to MySQL: " . mysqli_connect_error();} 
		$zzzzzz = $mysqli->query('SET foreign_key_checks = 0');	        preg_match_all("/\nCREATE TABLE(.*?)\`(.*?)\`/si", "\n". $SQL_CONTENT, $target_tables); foreach ($target_tables[2] as $table){$mysqli->query('DROP TABLE IF EXISTS '.$table);}         $zzzzzz = $mysqli->query('SET foreign_key_checks = 1');    $mysqli->query("SET NAMES 'utf8'");	
	$templine = '';	// Temporary variable, used to store current query
	foreach ($allLines as $line)	{											// Loop through each line
		if (substr($line, 0, 2) != '--' && $line != '') {$templine .= $line; 	// (if it is not a comment..) Add this line to the current segment
			if (substr(trim($line), -1, 1) == ';') {		// If it has a semicolon at the end, it's the end of the query
				$mysqli->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');  $templine = ''; // set variable to empty, to start picking up the lines after ";"
			}
		}
	}	echo 'Importing finished. Now, Delete the import file.';
}   

function get_remote_data($url, $post_paramtrs=false, return_full_array=false)	{
	$c = curl_init();curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	//if parameters were passed to this function, then transform into POST method.. (if you need GET request, then simply change the passed URL)
	if($post_paramtrs){curl_setopt($c, CURLOPT_POST,TRUE);	curl_setopt($c, CURLOPT_POSTFIELDS, "var1=bla&".$post_paramtrs );}
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);                  
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0"); 
	curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
					//We'd better to use the above command, because the following command gave some weird STATUS results..
					//$header[0]= $user_agent="User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0";  $header[]="Cookie:CookieName1=Value;"; $header[]="Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";  $header[]="Cache-Control: max-age=0"; $header[]="Connection: keep-alive"; $header[]="Keep-Alive: 300"; $header[]="Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; $header[] = "Accept-Language: en-us,en;q=0.5"; $header[] = "Pragma: ";  curl_setopt($c, CURLOPT_HEADER, true);     curl_setopt($c, CURLOPT_HTTPHEADER, $header);
					
	curl_setopt($c, CURLOPT_MAXREDIRS, 10); 
	//if SAFE_MODE or OPEN_BASEDIR is set,then FollowLocation cant be used.. so...
	$follow_allowed= ( ini_get('open_basedir') || ini_get('safe_mode')) ? false:true;  if ($follow_allowed){curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);}
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 9);
	curl_setopt($c, CURLOPT_REFERER, $url);    
	curl_setopt($c, CURLOPT_TIMEOUT, 60);
	curl_setopt($c, CURLOPT_AUTOREFERER, true);  
	curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
	$data=curl_exec($c);$status=curl_getinfo($c);curl_close($c);
	
	preg_match('/(http(|s)):\/\/(.*?)\/(.*\/|)/si',  $status['url'],$link);	
	//correct assets URLs(i.e. retrieved url is: http://site.com/DIR/SUBDIR/page.html... then href="./image.JPG" becomes href="http://site.com/DIR/SUBDIR/image.JPG", but  href="/image.JPG" needs to become href="http://site.com/image.JPG")
	
	//inside all links(except starting with HTTP,javascript:,HTTPS,//,/ ) insert that current DIRECTORY url (href="./image.JPG" becomes href="http://site.com/DIR/SUBDIR/image.JPG")
	$data=preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/|\/)).*?)(\'|\")/si','$1=$2'.$link[0].'$3$4$5', $data);     
	//inside all links(except starting with HTTP,javascript:,HTTPS,//)    insert that DOMAIN url (href="/image.JPG" becomes href="http://site.com/image.JPG")
	$data=preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/)).*?)(\'|\")/si','$1=$2'.$link[1].'://'.$link[3].'$3$4$5', $data);   
	// if redirected, then get that redirected page
	if($status['http_code']==301 || $status['http_code']==302) { 
		//if we FOLLOWLOCATION was not allowed, then re-get REDIRECTED URL
		//p.s. WE dont need "else", because if FOLLOWLOCATION was allowed, then we wouldnt have come to this place, because 301 could already auto-followed by curl  :)
		if (!$follow_allowed){
			//if REDIRECT URL is found in HEADER
			if(empty($redirURL)){if(!empty($status['redirect_url'])){$redirURL=$status['redirect_url'];}}
			//if REDIRECT URL is found in RESPONSE
			if(empty($redirURL)){preg_match('/(Location:|URI:)(.*?)(\r|\n)/si', $data, $m);	                if (!empty($m[2])){ $redirURL=$m[2]; } }
			//if REDIRECT URL is found in OUTPUT
			if(empty($redirURL)){preg_match('/moved\s\<a(.*?)href\=\"(.*?)\"(.*?)here\<\/a\>/si',$data,$m); if (!empty($m[1])){ $redirURL=$m[1]; } }
			//if URL found, then re-use this function again, for the found url
			if(!empty($redirURL)){$t=debug_backtrace(); return call_user_func( $t[0]["function"], trim($redirURL), $post_paramtrs);}
		}
	}
	// if not redirected,and nor "status 200" page, then error..
	elseif ( $status['http_code'] != 200 ) { $data =  "ERRORCODE22 with $url!!<br/>Last status codes:".json_encode($status)."<br/><br/>Last data got:$data";}
	return ( $return_full_array ? array('data'=>$data,'info'=>$status) : $data);
}


function remove_Dir($path) {
	if(!empty($path) && is_dir($path) ){
		$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS); //upper dirs are not included,otherwise DISASTER HAPPENS :)
		$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $f) {if (is_file($f)) {unlink($f);} else {$empty_dirs[] = $f;}} if (!empty($empty_dirs)) {foreach ($empty_dirs as $eachDir) {rmdir($eachDir);}} rmdir($path);
	}
}

function cleanInput($input) {
    if (is_array($input)) {
        foreach($input as $var=>$val) {
            $output[$var] = cleanScript($val);
        }
    }
    else {
        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);
        }
        $input  = cleanScript($input);
        $output = mysql_real_escape_string($input);
    }
    return $output;
}

function cleanScript($input) {
    // Remove <script> and html comments
    $search = array(
        '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
        '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
    );
    
    // Remove JS events
    $output = preg_replace($search, '', $input);
    $output = str_replace(array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'), "", $output);
    return $output;
}

/**
 * Parse xml content to array
 *
 * @param $xmlInput
 * @return array
 */
function xml_to_array($xmlInput){
	$xmlParser = xml_parser_create();
	xml_parse_into_struct($xmlParser, $xmlInput, $values);
	xml_parser_free($xmlParser);
	return $values;
}
