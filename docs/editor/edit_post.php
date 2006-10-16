<?php
/****************************************************************************/
/* ATutor																	*/
/****************************************************************************/
/* Copyright (c) 2002-2006 by Greg Gay, Joel Kronenberg & Heidi Hazelton	*/
/* Adaptive Technology Resource Centre / University of Toronto				*/
/* http://atutor.ca															*/
/*																			*/
/* This program is free software. You can redistribute it and/or			*/
/* modify it under the terms of the GNU General Public License				*/
/* as published by the Free Software Foundation.							*/
/****************************************************************************/
// $Id$

define('AT_INCLUDE_PATH', '../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');

authenticate(AT_PRIV_FORUMS);

require(AT_INCLUDE_PATH.'lib/forums.inc.php');

$fid = intval($_REQUEST['fid']);

if (!$fid) {
	header('Location: ../forum/list.php');
	exit;
}

if (!valid_forum_user($fid)) {
	require(AT_INCLUDE_PATH.'header.inc.php');
	$msg->addError('FORUM_DENIED');
	require(AT_INCLUDE_PATH.'footer.inc.php');
}

if ($_POST['cancel']) {
	$msg->addFeedback('CANCELLED');
	Header('Location: ../forum/view.php?fid='.$_POST['fid'].SEP.'pid='.$_POST['pid']);
	exit;
}

if ($_POST['edit_post']) {
	$missing_fields = array();

	$_POST['subject']	= str_replace('<', '&lt;', trim($_POST['subject']));
	$_POST['body']		= str_replace('<', '&lt;', trim($_POST['body']));
	$_POST['pid']		= intval($_POST['pid']);

	$_POST['subject']  = $addslashes($_POST['subject']);
	$_POST['body']  = $addslashes($_POST['body']);

	if ($_POST['subject'] == '')  {
		$missing_fields[] = _AT('subject');
	}

	if ($_POST['body'] == '') {
		$missing_fields[] = _AT('body');
	}
	if ($missing_fields) {
		$missing_fields = implode(', ', $missing_fields);
		$msg->addError(array('EMPTY_FIELDS', $missing_fields));
	}
	if (!$msg->containsErrors()) {
		$sql = "UPDATE ".TABLE_PREFIX."forums_threads SET subject='$_POST[subject]', body='$_POST[body]' WHERE post_id=$_POST[pid]";
		$result = mysql_query($sql,$db);

		$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		if ($_POST['ppid'] == 0) {
			$_POST['ppid'] = $_POST['pid'];
		}
		header('Location: ../forum/view.php?fid='.$_POST['fid'].SEP.'pid='.$_POST['ppid']);
		exit;
	}
}

$_pages['forum/index.php?fid='.$fid]['title']    = get_forum_name($fid);
$_pages['forum/index.php?fid='.$fid]['parent']   = 'forum/list.php';
$_pages['forum/index.php?fid='.$fid]['children'] = array('forum/new_thread.php?fid='.$fid);

$_pages['forum/new_thread.php?fid='.$fid]['title_var'] = 'new_thread';
$_pages['forum/new_thread.php?fid='.$fid]['parent']    = 'forum/index.php?fid='.$fid;

$_pages['forum/view.php']['title']  = $post_row['subject'];
$_pages['forum/view.php']['parent'] = 'forum/index.php?fid='.$fid;

$_pages['editor/edit_post.php']['title_var'] = 'edit_post';
$_pages['editor/edit_post.php']['parent']    = 'forum/index.php?fid='.$fid;
$_pages['editor/edit_post.php']['children']  = array();


$onload = 'document.form.subject.focus();';

require(AT_INCLUDE_PATH.'header.inc.php');


if (isset($_GET['pid'])) {
	$pid = intval($_GET['pid']);
} else {
	$pid = intval($_POST['pid']);
}

if ($pid == 0) {
	$msg->addError('ITEM_NOT_FOUND');
	require (AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}

$sql = "SELECT * FROM ".TABLE_PREFIX."forums_threads WHERE post_id=$pid";
$result = mysql_query($sql,$db);
if (!($row = mysql_fetch_assoc($result))) {
	$msg->addError('ITEM_NOT_FOUND');
	require (AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form">
<input type="hidden" name="edit_post" value="true" />
<input type="hidden" name="pid" value="<?php echo $pid; ?>" />
<input type="hidden" name="ppid" value="<?php echo $row['parent_id']; ?>" />
<input type="hidden" name="fid" value="<?php echo $row['forum_id']; ?>" />

<div class="input-form">
	<div class="row">
		<div class="required" title="<?php echo _AT('required_field'); ?>">*</div><label for="subject"><?php echo _AT('subject'); ?></label><br />
		<input type="text" maxlength="45" name="subject" size="36" value="<?php echo stripslashes(htmlspecialchars($row['subject'])); ?>" id="subject" />
	</div>

	<div class="row">
		<div class="required" title="<?php echo _AT('required_field'); ?>">*</div><label for="body"><?php echo _AT('body'); ?></label>
		<textarea cols="65" name="body" rows="10" id="body"><?php echo $row['body']; ?></textarea>
	</div>
	
	<div class="row">
		<small class="spacer">&middot;<?php echo _AT('forum_links'); ?>
		&middot; <?php echo _AT('forum_email_links'); ?>
		&middot; <?php echo _AT('forum_html_disabled'); ?></small>
	</div>

	<div class="row buttons">
		<input name="submit" type="submit" value="  <?php echo _AT('save'); ?>" accesskey="s" />
		<input type="submit" name="cancel" value=" <?php echo _AT('cancel'); ?> " />
	</div>
</div>
</form>

<?php require (AT_INCLUDE_PATH.'footer.inc.php'); ?>