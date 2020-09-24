<?php
/**

# The MIT License (MIT)

Copyright (c) 2020 <yangqingrong@wudimei.com>
qq:290359552
weixin: 13714715608
homepage: http://wudimei.com/yangqingrong

*/

ini_set('memory_limit','256M');
ini_set("display_errors",true);
error_reporting(E_ALL|E_ERROR|E_COMPILE_ERROR);


$dir = __DIR__;
$path_containns ="#php|phtml|inc|md#i";

$path_doesnt_containns ="#\\.git#i";
$pattern="#test#i";


/**

*/
function read_from_all_dir ( $dir )
{   
  global $path_containns,$path_doesnt_containns;
  $result = array();
  $handle = opendir($dir);
  if ( $handle )
  {
   while ( ( $file = readdir ( $handle ) ) !== false )
   {
    if ( $file != '.' && $file != '..')
    {
     $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
     if ( is_dir ( $cur_path ) )
     {
       $files=read_from_all_dir ( $cur_path );
       $result =array_merge($result,$files);
     }
     else
     {
      	if(preg_match($path_doesnt_containns,$cur_path)==false){
 						  //echo "m".$cur_path;
						 	if(preg_match($path_containns,$cur_path)!==false){
	 						  $result[]= $cur_path;
							 }
						 }
      

						 
     }
    }
   }
   closedir($handle);
  }
  return $result;
}
 
function post($name,$d) {
  $val = trim( @$_POST[$name] );
  if( $val == "")
  {
  	$val = $d;
  }
  return $val;
}

function getMatchedCode($c,$m ,$pattern){
    $code = "";
    foreach($m as $i){
      $s =$i[1]-500;
      
      if($s<0){
        $s =0;
      }
      $code .= mb_substr($c,$s,1000) ."_____findinfiles.php:separator______";
    }
    $code =preg_replace($pattern,'_____findinfiles.php:start______$0_____findinfiles.php:end______',$code);
    
    $code =htmlspecialchars($code);
    $code = str_replace("_____findinfiles.php:separator______","<br />...<br />",$code);
    $code = str_replace("_____findinfiles.php:start______","<b style=\"color:red\">",$code);
    $code = str_replace("_____findinfiles.php:end______","</b>",$code);
    
    $code = "<hr /><pre>$code</pre><hr />";
    return $code;

}

/**

  
*/
function exec_findWord($dir,$path_containns,$pattern){
  $html = "";
  $list = read_from_all_dir ( $dir,$path_containns );
  foreach($list as $file){
    $c = file_get_contents($file);
	   
		  
		  if( preg_match($pattern,$c,$m,PREG_OFFSET_CAPTURE)){
		     $code =getMatchedCode($c,$m ,$pattern);
		     $html.=$file.$code;
		  }   
  }
  
  return  $html;
}

function exec_replace($dir,$path_containns,$pattern,$replacement){
  if($replacement===null) return;
  $html = "";
  $list = read_from_all_dir ( $dir,$path_containns );
  foreach($list as $file){
    if(realpath($file)==__FILE__)
    { 
      $html .="Skip ".$file."<hr />";
      continue;
    }
    
    $c = file_get_contents($file);
    // echo $file;
 
    if( preg_match($pattern,$c)){
      	$c = preg_replace($pattern,$replacement,$c);
     // echo $c;
      file_put_contents($file, $c);
      $html.=$file."<hr />";
    }   
  }
 
   return  $html;
}



$dir = post( "dir",$dir );
$path_containns = post( "path_containns",$path_containns);
$path_doesnt_containns =post("path_doesnt_containns", $path_doesnt_containns);
$pattern= post( "pattern",$pattern);
$replacement= post( "replacement",null);





$result = "";
if( isset($_POST["find"]) != "" && $pattern != ""){
   $result =exec_findWord($dir,$path_containns,$pattern);
}

if( isset($_POST["replace"]) != "" && $pattern != ""){
   
   $result =exec_replace($dir,$path_containns,$pattern,$replacement);
}

?>
	
	
<!DOCTYPE html> 
<html>
 	<head>
 	 		<meta charset="UTF-8">
 	 		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 		
 	 		<meta http-equiv="X-UA-Compatible" content="ie=edge"> 		
 	 		<title>findinfiles.php - powered by  yangqingrong@wudimei.com</title>
 	 		
   <style>
   .btn{ margin:3px 8px;padding:3px 5px;}
   </style>
 	 </head>
 	 <body>
 	<h3>Find in files</h3>
<form method="post">

dir: 
<br />
<textarea name="dir" style="width:100%;height:60px;"><?php echo $dir; ?></textarea> 
<br />
path containns: <input type="text" name="path_dont_containns" value="<?php echo $path_containns; ?>" required  /> <br />
path doesn't containns: <input type="text" name="path_doesnt_containns" value="<?php echo $path_doesnt_containns; ?>" required  /> <br />
pattern: <input type="text" name="pattern"  value="<?php echo $pattern; ?>" required />
eg: #htm[l]+# <br />
replacement: <input type="text" name="replacement"  value="<?php echo $replacement; ?>" /> eg:$0_new<br />
<input type="submit" name="find" value="find"  class="btn" />
<input type="submit" name="replace" value="replace" onclick="return confirm('Danger!The replacement in your files can not be restore.Do you want to continue?')" class="btn" />

</form>

<?php echo $result; ?>

	<hr />
	powered by <a href="http://wudimei.com/yangqingrong" target="_blank">
	Yang Qing-rong</a>
   </body>
</html>
