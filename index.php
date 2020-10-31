<?php

/**
 * Habari Index
 *
 * In this file, we just set the root dir and include system/system.php
 *
 * @package Habari
 */#

/**
 * Define the constant HABARI_PATH.
 * The path to the root of this Habari installation.
 */
if ( !defined( 'HABARI_PATH' ) ) {
	define( 'HABARI_PATH', dirname( __FILE__ ) );
}

// define( 'DEBUG', true ); // habari debug

// DEV forces stripe test keys 
if( isset($_GET['dev']) ) { define( 'DEV', true ); define( 'VDEBUG', true ); } else { define( 'DEV', false); }

// dvector debug
if( isset($_GET['debug']) ) { define( 'THEMEDEBUG', true ); define( 'VDEBUG', true ); }

if ( !defined( 'VDEBUG' ) ) { define( 'VDEBUG', false ); }



require_once('class.debug.2.2.php');

// register our own tidy display handler, needs Debug 2.2
// note: no stack trace
register_shutdown_function( "Debug::error" );


/**
 * Require system/index.php, where the magic happens
 */
require( HABARI_PATH . '/system/index.php' );

