<?php

// this file is not really needed, when empty it can be ommitted
// however you can override the default methods and add custom
// installation logic

namespace phpbbgallery\core;

class ext extends \phpbb\extension\base
{
  	function disable_step($old_state)
  	{
  	  	global $db;
  	  	$sql = 'DELETE FROM ' . NOTIFICATION_TYPES_TABLE . "
  	  	  	WHERE notification_type_name = 'notification.type.phpbbgallery_new_comment'";
  	  	$db->sql_query($sql);
  	  	return parent::enable_step($old_state);
  	}
}
