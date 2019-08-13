<?php
/**
 * Grep Searc-Engine
 * Use grep command line with a PHP interface.
 * inspired by an article in http://programmabilities.com/php/?id=2 by Chief Programmabilities
 * Distributed under GNU LGPL. See http://gnu.org/licenses/lgpl.html for details.
 *
 * @version 1.0
 * @copyright 2005-2006 Alejandro Vásquez admin[at]digitart.net
 * @version 1.1
 * @copyright 2019 Alejandro Vásquez admin[at]masclientes.mx
 *
 * Note from PHP manual: When safe mode is enabled, you can only execute
 * grep within the safe_mode_exec_dir.
 * With safe mode enabled, all words following the initial command string
 * are treated as a single argument. Thus, echo y | echo x becomes echo "y | echo x".
 *
 * split() is deprecated since PHPv5.3 use explode() instead in line 171
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

extract($_POST);
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Grep Search-engine</title>
<style>
body{
	font-family: Arial,Helvetica,sans-serif;
}
h1{
	font-family: Verdana,Helvetica,sans-serif;
	font-size:21px;
	letter-spacing: -1px;
}
h2{
	font-family: Verdana,Helvetica,sans-serif;
	font-size:18px;
	letter-spacing: -1px;
	color:#666;
}
label{
	display:block;
	width:80px;
	float:left;
	font-size:12px;
	color:#444;
	text-align:right;
	padding-right:5px;
}
.inputnote,.copyright{
	font-size:11px;
	color:#888;
}
.inputnote{
	padding-left:5px;
}
.copyright{
	text-align:center;
	border-top:1px solid #ccc;
	margin-top:50px;
}
a{
	font-size:16px;
	font-weight:bold;
	text-decoration:none;
	color:#600;
}
a:hover{
	color:#800;
	text-decoration:underline;
}
.copyright a{
	font-size:10px;
	color:#444;
}
ul{
	padding-left:0;
	margin-bottom:10px;
	border:1px solid #ccc;
	<? echo $nowrap ? 'white-space: nowrap':'' ?>
}
ul li{
	font-size:11px;
	list-style:none;
	margin-left:0;
}
li strong{
	background-color:#BFB;
	font-weight:normal;
}
.linenumber{
	background-color:#EEE;
	float:left;
	width:30px;
	padding: 0 5px;
	margin-right:5px;
	text-align:right;
	border-right:1px solid #ccc;
}
code{
	display:block;
	padding-left:50px;
	font-family:"Courier New",Courier;
	font-size:11px;
}
.noresults{
	text-align:center;
	color:#800;
	font-size:14px;
	margin-top:50px;
	margin-bottom:150px;
}
.bkgeven{
	background-color:#F4F4F4;
}
.bkgodd{
	background-color:#FCFCFC;
}
</style>
</head>
 <body>
  <h1>GrePHP - Grep search-engine with PHP</h1>

   <form action="<?php echo "$PHP_SELF"; ?>" method="post">
    <label>Search for:</label><input type="text" name="searchstr"
     value="<?php echo "$searchstr"; ?>" size="20" /><br>
    <label>in directory:</label><input type="text" name="searchdir"
     value="<?php echo "$searchdir"; ?>" size="20" /><br>
	<label>&nbsp;</label><input type="checkbox" name="matchcase" value=1 <? echo $matchcase?'checked':'' ?>><span class="inputnote">Match case</span><br>
	<label>&nbsp;</label><input type="checkbox" name="recursive" value=1 <? echo $recursive?'checked':'' ?>><span class="inputnote">Recursive (search subfolders)</span><br>
    <label>include files:</label><input type="text" name="includefiles"
     value="<?php echo "$includefiles"; ?>" size="5"
      maxlength="30"/><span class="inputnote">(When recurse in directories only searching file matching PATTERN. I.e.: *.php)</span><br>
    <label>exclude files:</label><input type="text" name="excludefiles"
     value="<?php echo "$excludefiles"; ?>" size="5"
      maxlength="30"/><span class="inputnote">(When recurse in directories skip file matching PATTERN. I.e.: *.js)</span><br>
	<label>&nbsp;</label><input type="checkbox" name="nowrap" value=1 <? echo $nowrap?'checked':'' ?>><span class="inputnote">No wrap lines</span><br>
    <label>&nbsp;</label><input type="submit" value="Search!"/>
   </form>


<?php
if (! empty($_POST['searchstr'])) {
     //If form has being submited extract $_POST vars into the current symbol table
     echo '<hr>';
	 if(!$searchdir) $searchdir = '*';
	 $options = '-nH'; //Force grep to retrive line numbers and file names
	 if(!$matchcase) $options.= 'i';
	 if($recursive){
	 	$options.= 'r';
		$rs = 'recursive';
		if($includefiles){ //only available in recursive searches
			$options.= " --include=$includefiles";
		}
		if($excludefiles){ //only available in recursive searches
			$options.= " --exclude=$excludefiles";
		}
	 }else{
	 	$rs = 'nonrecursive';
	 }
	 $cmdstr = "grep $options '$searchstr' $searchdir";
     $fp = popen($cmdstr, 'r'); // open the output of command as a pipe
     $results = array(); // to hold my search results
     while ($buffer = fgets($fp, 4096)) {
          // grep returns results separated with :
          list($fname,$linenumber, $fline) = explode(':', $buffer, 3);
		  $myresult[$fname]['linenumber'][] = $linenumber;
		  $myresult[$fname]['line'][] = str_replace($searchstr,"<strong>$searchstr</strong>",htmlentities($fline));
      }
      // we have results in a var. lets walk through it and print it
	  $numresults = count($myresult);
      if ($numresults) {
	  		echo "<h2>Your $rs search on '$searchstr' in directory $searchdir returned $numresults files</h2>\n";
            echo '<ol>';
		   foreach($myresult as $key=>$r){
			$count = count($r['linenumber']);
		   	echo "<li><a href=\"$key\">$key</a> <span class=\"inputnote\">($count lines)</span>:";

				echo "<ul>";
				for($i=0;$i<$count;$i++){
					$codeclass = $i %2 ? 'bkgeven':'bkgodd';
					echo "<li><div class=\"linenumber\">".$r['linenumber'][$i]."</div><code class=\"$codeclass\">".$r['line'][$i]."</code></li>";
				}
				echo "</ul>";

			echo '</li>';
		   }
		   echo "</ol>";
       } else {
            // no hits
            echo "<div class=\"noresults\">Sorry. Your $rs search on <strong>$searchstr</strong> on directory $searchdir
                returned no results.</div>\n";
       }
       pclose($fp);
   }
?>
<div class="copyright">&copy;2005-2019 Digitart, Alejandro V&aacute;squez. All rights reserved<br>Distributed under GNU LGPL. See <a href="http://gnu.org/licenses/lgpl.html">http://gnu.org/licenses/lgpl.html</a> for details.</div>
</body>
</html>
