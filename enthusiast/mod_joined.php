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
function get_joined( $status = 'all', $start = 'none', $bydate = 'no' ) {
   require 'config.php';

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );

   $query = "SELECT `joinedid` FROM `$db_joined`";

   if( $status == 'pending' )
      $query .= " WHERE `pending` = 1";
   else if( $status == 'approved' )
      $query .= " WHERE `pending` = 0";

   if( $bydate == 'bydate' )
      $query .= " ORDER BY `added` DESC";
   else if( $bydate == 'id' )
      $query .= " ORDER BY `joinedid` DESC";
   else
      $query .= " ORDER BY `subject`";

   if( $start != 'none' && ctype_digit( $start ) ) {
      $settingq = "SELECT `value` FROM `$db_settings` " .
         "WHERE `setting` = 'per_page'";
      $result = mysqli_query( $db_link, $settingq );
      $row = mysqli_fetch_array( $result );
      $limit = $row['value'];
      $query .= " LIMIT $start, $limit";
   }

   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   $ids = array();
   while( $row = mysqli_fetch_array( $result ) )
      $ids[] = $row['joinedid'];
   return $ids;
}


/*___________________________________________________________________________*/
function get_joined_cats() {
   require 'config.php';
   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error()  );

   $query = "SELECT DISTINCT( `catid` ) as `id` FROM `$db_joined` ";

   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   if( mysqli_num_rows( $result ) == 0 )
      return array(); // return empty array, no cats

   $query = "SELECT `catid` FROM `$db_category` WHERE ( ";
   $allcats = array();
   while( $row = mysqli_fetch_array( $result ) ) {
      $cats = explode( '|', $row['id'] );
      foreach( $cats as $cat )
         if( $cat != '' && !in_array( $cat, $allcats ) ) {
            $query .= "`catid` = '$cat' OR ";
            $allcats[] = $cat;
         }
   }
   $query = rtrim( $query, 'OR ' ) . ' ) ';
   $query .= ' ORDER BY `catname` ASC';

   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   $ids = array();
   while( $row = mysqli_fetch_array( $result ) )
      $ids[] = $row['catid'];
   return $ids;
}


/*___________________________________________________________________________*/
function get_joined_info( $id ) {
   require 'config.php';
   $query = "SELECT * FROM `$db_joined` WHERE `joinedid` = '$id'";

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   return mysqli_fetch_array( $result );
}


/*___________________________________________________________________________*/
function get_joined_by_category( $catid ) {
   require 'config.php';
   $query = "SELECT `joinedid` FROM `$db_joined` WHERE `catid` LIKE " .
      "'%|$catid|%' ORDER BY `subject`";

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   $ids = array();
   while( $row = mysqli_fetch_array( $result ) )
      $ids[] = $row['joinedid'];
   return $ids;
}



/*___________________________________________________________________________*/
function parse_joined_template( $id ) {
   require 'config.php';

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );

   $query = "SELECT * FROM `$db_joined` WHERE `joinedid` = '$id'";
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $info = mysqli_fetch_array( $result );

   $query = "SELECT `setting`, `value` FROM `$db_settings` WHERE `setting` =" .
      ' "joined_images_dir" OR `setting` = "root_path_absolute" ' .
      ' OR `setting` = "root_path_web"';
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $dir = '';
   $root_web = '';
   $root_abs = '';
   while( $row = mysqli_fetch_array( $result ) )
      if( $row['setting'] == 'joined_images_dir' )
         $dir = $row['value'];
      else if( $row['setting'] == 'root_path_absolute' )
         $root_abs = $row['value'];
      else
         $root_web = $row['value'];
   @$image = ( $info['imagefile'] && is_file( $dir . $info['imagefile'] ) )
      ? getimagesize( $dir . $info['imagefile'] ) : '';
   // make sure $image is an array, in case getimagesize() failed
   if( !is_array( $image ) )
      $image = array();
   $dir = str_replace( $root_abs, $root_web, $dir );

   $query = "SELECT `value` FROM `$db_settings` WHERE `setting` = " .
      "'joined_template'";
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $setting = mysqli_fetch_array( $result );

   $formatted = str_replace( 'enth3-url', $info['url'], $setting['value'] );
   $formatted = str_replace( 'enth3-subject', $info['subject'], $formatted );
   $formatted = str_replace( 'enth3-desc', $info['desc'], $formatted );
   $formatted = str_replace( 'enth3-image', $dir . $info['imagefile'],
      $formatted );
   if( is_array( $image ) && count( $image ) ) {
      $formatted = str_replace( 'enth3-width', $image[0], $formatted );
      $formatted = str_replace( 'enth3-height', $image[1], $formatted );
   } else {
      $formatted = str_replace( 'enth3-width', '', $formatted );
      $formatted = str_replace( 'enth3-height', '', $formatted );
   }
   return $formatted;
}

/*___________________________________________________________________________*/
function search_joined( $search, $status = 'all', $start = 'none' ) {
   require 'config.php';

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );

   $query = "SELECT `joinedid` FROM `$db_joined` WHERE ( MATCH( " .
      "`subject`, `desc`, `comments` ) " .
      "AGAINST( '$search' ) OR `subject` LIKE '%$search%' )";

   if( $status == 'pending' )
      $query .= " AND `pending` = 1";
   else if( $status == 'approved' )
      $query .= " AND `pending` = 0";

   $query .= " ORDER BY `subject` DESC";

   if( $start != 'none' && ctype_digit( $start ) ) {
      $settingq = "SELECT `value` FROM `$db_settings` " .
         "WHERE `setting` = 'per_page'";
      $result = mysqli_query( $db_link, $settingq );
      $row = mysqli_fetch_array( $result );
      $limit = $row['value'];
      $query .= " LIMIT $start, $limit";
   }

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   $ids = array();
   while( $row = mysqli_fetch_array( $result ) )
      $ids[] = $row['joinedid'];
   return $ids;
}


/*___________________________________________________________________________*/
function add_joined( $catids, $url, $subject, $desc, $comments,
   $pending = 1 ) {
   require 'config.php';
   $cats = implode( '|', $catids );
   $cats = '|' . trim( $cats, '|' ) . '|';
   $query = "INSERT INTO `$db_joined` VALUES( null, '$cats', '$url', " .
      "'$subject', '$desc', '$comments', null, NOW(), '$pending' )";

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   return mysqli_insert_id( $db_link );
}


/*___________________________________________________________________________*/
function edit_joined( $id, $image = '', $catid = array(), $url = '',
   $subject = '', $desc = '', $comments = '', $pending = 'none' ) {
   require 'config.php';

   $query = "UPDATE `$db_joined` SET ";

   if( $image != '' && $image != 'null' )
      $query .= "`imagefile` = '$image', ";
   else if( $image == 'null' )
      $query .= "`imagefile` = null, ";

   if( count( $catid ) > 0 ) {
      $query .= "`catid` = '|" . trim( implode( '|', $catid ), '|' ) . "|', ";
   }

   if( $url )
      $query .= "`url` = '$url', ";
   if( $subject )
      $query .= "`subject` = '$subject', ";

   if( $pending != 'none' )
      $query .= "`pending` = '$pending', ";

   if( $desc )
      $query .= "`desc` = '$desc', ";
   else if( $desc == '' )
      $query .= "`desc` = null, ";

   if( $comments )
      $query .= "`comments` = '$comments', ";
   else if( $comments == '' )
      $query .= "`comments` = null, ";

   $query = rtrim( $query, ', ' );
   $query .= " WHERE `joinedid` = '$id'";

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   return $result;
}


/*___________________________________________________________________________*/
function delete_joined( $id ) {
   require 'config.php';
   $query = "DELETE FROM `$db_joined` WHERE `joinedid` = '$id'";

   $db_link = mysqli_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   mysqli_select_db( $db_link, $db_database )
      or die( DATABASE_CONNECT_ERROR . mysqli_error() );
   $result = mysqli_query( $db_link, $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysqli_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   return $result;
}

?>
