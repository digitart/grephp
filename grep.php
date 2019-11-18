<?php
/**
 * Grep Searc-Engine
 * Use grep command line with a PHP interface.
 * inspired by an article in http://programmabilities.com/php/?id=2 by Chief Programmabilities
 * Distributed under MIT License.
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
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
//*/
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
        label {
            display: block;
            width: 7em;
            float: left;
            font-size: .9em;
            color: #444;
            text-align: right;
            padding-right: 5px;
            line-height: 2.3em;
        }
        .check-label{
            font-size:.9em;
            color:#444;
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
            border-top:1px solid #bbb;
            margin-top:50px;
            padding-top:5px;
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
            font-size:.8rem;
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
        input {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
            padding: .3em;
            margin: .2em;
            font-size: 1em;
            color:#555;
        }
        .form-row{
            height:1.8em;
            margin:5px;
        }
        .search-command {
            font-weight: bold;
            border: 1px solid #ccc;
            padding: .5em;
            color: #444;
        }
        hr{
            color: #ccc;
            margin: 15px;
        }
        .btn{
            background-color:#69c;
            color:#fff;
            padding: 3px 15px;
            border: 1px solid #23496f;
        }
        .btn:hover{
            background-color:#58b;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>GrePHP - Grep search-engine with PHP</h1>

    <form action="<?php echo "$PHP_SELF"; ?>" method="post">
        <label>Search for:</label><input type="text" name="searchstr" value="<?php echo "$searchstr"; ?>" size="20" /><br>
        <label>in directory:</label><input type="text" name="searchdir" value="<?php echo "$searchdir"; ?>" size="20" /><br>
        <div class="form-row">
            <label>&nbsp;</label><input type="checkbox" name="matchcase" value=1 <?php echo $matchcase?'checked':'' ?>>
            <span class="check-label">Match case</span>
            <input type="checkbox" name="recursive" value=1 <?php echo $recursive?'checked':'' ?>>
            <span class="check-label">Recursive (search subfolders)</span>
        </div>
        <div class="form-row">
            <label>include files:</label><input type="text" name="includefiles" value="<?php echo "$includefiles"; ?>" size="5" maxlength="30"/>
            <span class="inputnote">(When recurse in directories only searching file matching PATTERN. I.e.: *.php)</span>
        </div>
        <div class="form-row">
            <label>exclude files:</label><input type="text" name="excludefiles" value="<?php echo "$excludefiles"; ?>" size="5" maxlength="30"/>
            <span class="inputnote">(When recurse in directories skip file matching PATTERN. I.e.: *.js)</span>
        </div>
        <div class="form-row">
            <label>&nbsp;</label>
            <input type="checkbox" name="nowrap" value=1 <?php echo $nowrap?'checked':'' ?>><span class="check-label">No wrap lines</span>
        </div>
        <div class="form-row">
            <label>&nbsp;</label><input type="submit" value="Search!" class="btn"/>
        </div>
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
    echo "<div class='search-command'>Grep search used: $cmdstr</div>";
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
        echo "<div class=\"noresults\">Sorry. Your $rs search on <strong>$searchstr</strong> on directory $searchdir returned no results.</div>\n";
    }
    pclose($fp);
}
?>
    <div class="copyright">&copy;2005-2019 Digitart, Aleks V&aacute;squez. All rights reserved<br>Distributed under MIT License.</div>
</body>
</html>
