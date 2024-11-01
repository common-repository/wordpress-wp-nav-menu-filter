<?php
/*
Plugin Name: WordPress wp_nav_menu Filter
Plugin URI: http://www.travishoglund.com
Description: Want to show submenus on internal pages controlled by your main navigation?  Now you can!  Plugin supports passing of Page Title along with passing on Page ID.
Version: 1.1
Author: Travis Hoglund
Aurhor URI: http://www.travishoglund.com
License: GPL2
*/

/*  Copyright 2011  Travis Hoglund  (email : travis@travishoglund.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_filter('wp_nav_menu_objects', 'wp_nav_menu_filter', 10, 2);
function wp_nav_menu_filter ($items, $args) {
  // Check $wp_obj  for array
  $wp_obj = $args->submenu;
  if (!isset($wp_obj) || empty($wp_obj))
    return $items;
  if (is_string($wp_obj))
    $wp_obj = split("/", $wp_obj);
  if (empty($wp_obj))
    return $items;

  // Create a slug string for each individual item
  foreach ($items as $item) {
    if (empty($item->slug))
      $item->slug = sanitize_title_with_dashes($item->title);
  }

  //  find the selected parent item ID(s)
  $cursor = 0;
  foreach ($wp_obj as $slug) {
	if (ctype_digit($slug)) $passedID = (int)$slug; else $passedID = null;
    $slug = sanitize_title_with_dashes($slug);
	
	if ($passedID != null) {
		foreach ($items as $item) { //Function was passed a Page ID
		
		//We have the menu ID (Ex: 134), but we need to convert it to the actual page ID (Ex: 22)
		$sub_objid = $item->object_id;
        $theRelevantPage = get_post($sub_objid);
		
		  if ($theRelevantPage->ID == $slug) {
			$cursor = $item->ID;
			continue 2;
		  }
		}
	}
	else { //Standard Pagename Passed
		foreach ($items as $item) {
		  if ($cursor == $item->menu_item_parent && $slug == $item->slug) {
			$cursor = $item->ID;
			continue 2;
		  }
		}
		
	}
    return array();
  }

  //  walk finding items until all levels are exhausted
  $parents = array($cursor);
  $out = array();
  while (!empty($parents)) {
    $newparents = array();

    foreach ($items as $item) {
      if (in_array($item->menu_item_parent, $parents)) {
        if ($item->menu_item_parent == $cursor)
          $item->menu_item_parent = 0;
        $out[] = $item;
        $newparents[] = $item->ID;
      }
    }

    $parents = $newparents;
  }

  return $out;
}
