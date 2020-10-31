<?php

// 33v 2019-06-13 v2.2 TODO add method to pass in fatal error array from custom handler set in register_shutdown_function(
// 33v 2019-05-28 fixed boolean output, filtered for text only no varaiable for a simple message out
// 33v use debug::out($var1, $var2... ) anywhere in habari
class Debug {
	
	// 
	static $css = 'pre.vdebug{font-size:11px;line-height:11px;color:#999;background:#ffc;margin:2px 2px 2px 0;border:1px solid #fc0;padding:1px 0 1px 2px}div.vdebug pre a{color:#999;text-decoration:none}div.vdebug pre a:hover{text-decoration:underline}';
	
	//
	static $css_err = 'pre.vdebug.error{background:#f2e1e1;border-color:#f49393;color:#dd5252; padding:1px 1px 1px 0;}pre.vdebug.error a{ background-color:#dd5252;color:#fff;text-decoration:none;padding:0 2px; }';
	
	// see https://www.color-hex.com/color/ffffcc
	//
	
	/**
	* 2019-06-06 probably never used!
	* 
	* 
	**/
	public static function spacer()
	{
		echo '<br>';
	}	
	/**
	* handle php system errors
	* 
	* 
	**/
	public static function error()
	{
		if ( !defined( 'VDEBUG' ) ) { return '';} // 33v TODO maybe enforce on error display on production
		
		$e = error_get_last();
		
		$types = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING,E_RECOVERABLE_ERROR );
		
		$tt = array( E_ERROR =>'E_ERROR', E_PARSE=>'E_PARSE', E_CORE_ERROR=>'E_CORE_ERROR', E_CORE_WARNING=>'E_CORE_WARNING', E_COMPILE_ERROR=>'E_COMPILE_ERROR', E_COMPILE_WARNING=>'E_COMPILE_WARNING',E_RECOVERABLE_ERROR=>'E_RECOVERABLE_ERROR' );
		
		// fatal error, E_ERROR === 1
		if ($e !== NULL && in_array($e['type'], $types )) {
			// debug::err_out($error);
			
			$i = in_array($e['type'], $types); $err_name = $tt[$i];
			
			$file = str_replace('\\', '/', $e['file']); //echo DIRECTORY_SEPARATOR.' ';
			
			$message = str_replace(array("\r","\n","\t","   ")," ",$e['message']);
			
			$html= '<a href="ekomodo://'.$file.'#'.$e['line'].'">'.basename($e['file']).' #'.$e['line'].'</a> '.$err_name.' '.$message;
	
			echo '<pre class="vdebug error">'.$html.'</pre><style>'.self::$css_err.'</style>';
			
		}
	}
	//
	
	
	
	
	/**
	* 2019-06-06 33v main formatting code broken out to new function self::do_majic()
	* 2013-04-25 33v echo out line number, filename, [function name], and print_r variable
	* 
	* 
	**/
	public static function out()
	{
		if ( !defined( 'VDEBUG' ) ) { return '';}
		
		$bt   = debug_backtrace(); $bf = $bt;
		
		$html = self::do_majic( $bt, array_shift($bf), func_get_args() );
		
		echo '<div class="vdebug"><pre class="vdebug">'.$html.'</pre></div><style>'.self::$css.'</style>';

	}
	//
    
	/**
	* 2019-06-06 33v main formatting code broken out to new function self::do_majic()
	* 2013-04-25 33v echo out line number, filename, [function name], and print_r variable
	* 
	* 
	**/
	public static function get()
	{
		// if ( !defined( 'VDEBUG' ) ) { return '';}

		$bt = debug_backtrace(); $bf = $bt;
		
		$bt   = debug_backtrace(); $bf = $bt;
		
		$html = self::do_majic( $bt, array_shift($bf), func_get_args() );
		
		return '<div class="vdebug"><pre class="vdebug">'.$html.'</pre></div><style>'.self::$css.'</style>';

	}
	//

	/**
	* 2019-06-06 never used??
	* output a value only
	* 
	* 
	**/
	public static function get_val()
	{
		return '';
	exit();
	
		if ( !defined( 'VDEBUG' ) ) { return '';}
		
		$html ='';
		//$bt = debug_backtrace();
		//$bf = $bt;
		//$caller = array_shift($bf);// breaks $bt so we use $temp
		$html .= '<style>
			pre.vdebug.values { display:inline; font-size:11px; line-height:12px; color:#999999; background:#ccffff; margin:4px 2px 2px 0; border:1px solid #ffcc00; padding:0px 0px 0px 8px;
			}
			</style>';
		$html .= '<pre class="vdebug values">';

		$vars = func_get_args(); // $val = "__undefin_e_d__", $filedetails=true
		
		// get array of var names
		//$var_names = self::get_varnames($bt,$vars);
		// lets hope they are in the same order as the vars;
		
		//echo '<pre>var_names='.print_r($var_names,true).'</pre>';
		$c=0;
		foreach ( $vars as $arg1 ) {
			//$html.= print_r( '<em>' . gettype( $arg1 ) . '</em> ',true );
			//$html.= '<br>'.$var_names[$c].' = '; $c++;
			if ( gettype( $arg1 ) == 'boolean' ) {
				$html.= htmlentities( var_export( $arg1 ) ) . '';
			}
			else {
				$html.= htmlentities( print_r( $arg1, true ) ) . '';
			}
		}
		return $html.'</pre>';
	
	}
	//

	/**
	* 2019-06-06 33v unifies the ouptput of debug::out() and get()
	* 
	* 
	* return html
	**/
	public static function do_majic( $bt, $caller, $vars )
	{
		//if ( !defined( 'VDEBUG' ) ) { return '';}

		$html ='';
		//$bt = debug_backtrace();
		$bf = $bt;
		//$caller = array_shift($bf);// breaks $bt so we use $temp
		// gets array of incoming $args
		// see habar Utils::debug();
		//$vars = func_get_args(); // $val = "__undefin_e_d__", $filedetails=true
		
		// 33v 2019-06-06 new feature: see if we can get system to open the file location, NEEDS https://chrome.google.com/webstore/detail/local-explorer-file-manag/eokekhgpaakbkfkmjjcbffibkencdfkl?hl=en
		// 2019 correct solution is to use the get_included_files or getcwd() function
		// list($scriptPath) = get_included_files();
		$dir  ='<a href="localexplorer:'.getcwd().'">[dir]</a>';
	
		//if($filedetails){ //file:\\
		$path = $caller['file'];
		$url  = str_replace('\\', '/', $path); // '#'.$caller['line']

		$html.= '<a href="ekomodo://'.$url.'#'.$caller['line'].'">['.basename($caller['file']).'#'.$caller['line'].']</a> '.$dir.' ';
		//}
		
		// $html = print_r($bf,true);
		// display source function if it exists
		// if( isset($bf[0]) ) { $html .= 'function ['.print_r( $bf[0]['function'],true ).'] '; }
		// if( isset($bf[0]) ) { $html .= 'function <b>'.print_r( $bf[0]['function'],true ).'(...)</b>'; }
		
		if( isset($bf[0]) ) {
			
			// $html .= ''.print_r( $bf[0],true ).'';
			if( isset($bf[0]['function']) )
			{
				// print title of function, but ignore if self
				if( isset($bf[0]['class']) &&  $bf[0]['class'] == 'Debug') {  } else {
					$html .= print_r( $bf[0]['function'],true );
				}
				
				
			} else {
				// $html .= '<br><b>Function:</b>'.print_r( $bf[0]['function'],true );
			}
		}
		// 


		// get array of var names
		$var_names = self::get_varnames($bt,$vars);
		// lets hope they are in the same order as the vars;
		
		//echo '<pre>var_names='.print_r($var_names,true).'</pre>';
		$c=0;
		// $html.= print_r( $vars, true);
		// $html.= print_r( $var_names, true);
		
		foreach ( $vars as $arg1 )
		{
			// $html.= gettype( $arg1 ); //print_r( $arg1, true );
			//$html.= print_r( '<em>' . gettype( $arg1 ) . '</em> ',true );
			if ( gettype( $arg1 ) == 'string' ) {

				if( trim( $var_names[$c], "'" ) == $arg1 )
				{	// case: a plain string was entered into debug, here we avoid 'string = string' being displayed
					$html .= $arg1;
				} else {
					// case: variable containing string
					$html .= trim($var_names[$c]).' = '.$arg1; //htmlentities( var_export( $arg1 ) ) . '';
				}
				$c++;
				continue;
			}
			
			if ( gettype( $arg1 ) == 'boolean' ) {
				$html.= '<br>'.$var_names[$c].' = BOOLEAN:';
				$html.= htmlentities( var_export( $arg1,true ) ) . '';
				$c++;
			}
			else {
				$html.= ''.@$var_names[$c].' = '; //@ needed sometimes
				$html.= htmlentities( print_r( $arg1, true ) ) . '';
				$c++;
			}
		}

		return $html;
// echo '<div class="vdebug"><pre>'.$html.'</pre></div>';
// echo '<div class="vdebug"><pre class="vdebug">'.$html.'</pre></div><style>'.self::$css.'</style>';
	
	}
	//
   























	/**
	* 33v this is hard in php, return array of variable names 
	* $bt is the debug_backtrace() which contains variable names
	* $vars the variables whose names we are looking for 
	* 
	**/
	private static function get_varnames($bt,$vars)
	{
		
		//$bt = debug_backtrace();
		$src = file($bt[0]["file"]);
		$line = $src[ $bt[0]['line'] - 1 ];
	
		// let's match the function call and the last closing bracket
		preg_match( "#out\((.+)\)#", $line, $match );

		
//echo '<pre>$match='.print_r($match,true).'</pre>';
		/* let's count brackets to see how many of them actually belongs 
		   to the var name
		   Eg:   die(inspect($this->getUser()->hasCredential("delete")));
			  We want:   $this->getUser()->hasCredential("delete")
		*/

		if(!isset($match[1])){ return array(); }

		$max = strlen($match[1]);		
		$varnames = "";
		$c = 0;
		// complex build string one char at a time 
		for($i = 0; $i < $max; $i++){
		    if(     $match[1]{$i} == "(" ) $c++;
		    elseif( $match[1]{$i} == ")" ) $c--;
		    if($c < 0) break;
		    $varnames .=  $match[1]{$i};
		}
		// $varname now a ", " seperated STRING
		
		// $label now holds the name of the passed variable ($ included)
		// Eg:   inspect($hello) 
		//             => $label = "$hello"
		// or the whole expression evaluated
		// Eg:   inspect($this->getUser()->hasCredential("delete"))
		//             => $label = "$this->getUser()->hasCredential(\"delete\")"
		
		// return var names as array
		return explode(', ',$varnames);
	}
    
    
    
    

    
    
    
    
    
    
    
    
}
?>