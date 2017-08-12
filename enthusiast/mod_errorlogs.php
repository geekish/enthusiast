<?php
/*****************************************************************************
 Enthusiast: Listing Collective Management System
 Copyright (c) by Angela Sabas
 http://scripts.indisguise.org/

 Enthusiast is a tool for (fan)listing collective owners to easily
 maintain their listing collectives and listings under that collective.

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

 For more information please view the readme.txt file.
******************************************************************************/


/*___________________________________________________________________________*/
function log_error( $page, $text, $kill = true ) {
   require 'config.php';
   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error( $db_link ) );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error( $db_link ) );
   // check if we're monitoring errors!
   $query = "SELECT `value` FROM `$db_settings` WHERE " .
      "`setting` = 'log_errors'";
   $result = mysqli_query( $db_link, $query )
      or die( 'Error executing query: ' . mysqli_error( $db_link ) );
   $row = mysqli_fetch_array( $result );
   if( $row['value'] == 'yes' ) {
      $text = addslashes( $text );
      $query = "INSERT INTO `$db_errorlog` VALUES( NOW(), '$page', '$text' )";
      $result = mysqli_query( $db_link, $query )
         or die( 'Error executing query: ' . mysqli_error( $db_link ) );
   } else {
      // we're not monitoring, so we just echo the thing :p
      if( $kill ) {
         echo "On $page - $text";
         die();
      }
   }
   return true;
}

/*___________________________________________________________________________*/
function get_logs( $start = 'none', $date = '' ) {
   require 'config.php';
   $query = "SELECT * FROM `$db_errorlog`";
   if( $date )
      $query .= " WHERE `date` = '$date'";
   $query .= ' ORDER BY `date` DESC';
   if( ctype_digit( $start ) )
      $query .= " LIMIT $start, " . get_setting( 'per_page' );
   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error( $db_link ) );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error( $db_link ) );
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error( $db_link ) .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $logs = array();
   while( $row = mysqli_fetch_array( $result ) )
      $logs[] = $row;
   return $logs;
}


/*___________________________________________________________________________*/
function flush_logs() {
   require 'config.php';
   $query = "TRUNCATE `$db_errorlog`";
   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error( $db_link ) );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error( $db_link ) );
   return mysqli_query( $db_link, $query );
}
?>
