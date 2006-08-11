<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	if (document.getElementById('jformtitle').value == ""){
		alert( "<?php echo JText::_( 'Weblink item must have a title', true ); ?>" );
	} else if (document.getElementById('jformcatid').value < 1) {
		alert( "<?php echo JText::_( 'You must select a category.', true ); ?>" );
	} else if (document.getElementById('jformurl').value == ""){
		alert( "<?php echo JText::_( 'You must have a url.', true ); ?>" );
	} else {
		submitform( pressbutton );
	}
}
</script>

<form action="<?php echo sefRelToAbs("index.php"); ?>" method="post" name="adminForm" id="adminForm">
<div class="componentheading">
	<?php echo JText::_( 'Submit A Web Link' );?>
</div>

<div style="float: right;">
	<?php
	mosToolBar::startTable();
	mosToolBar::spacer();
	mosToolBar::save();
	mosToolBar::cancel();
	mosToolBar::endtable();
	?>
</div>

<table cellpadding="4" cellspacing="1" border="0" width="100%">
<tr>
	<td width="10%">
		<label for="jformtitle">
			<?php echo JText::_( 'Name' ); ?>:
		</label>
	</td>
	<td width="80%">
		<input class="inputbox" type="text" id="jformtitle" name="jform[title]" size="50" maxlength="250" value="<?php echo htmlspecialchars( $row->title, ENT_QUOTES );?>" />
	</td>
</tr>
<tr>
	<td valign="top">
		<label for="jformcatid">
			<?php echo JText::_( 'Category' ); ?>:
		</label>
	</td>
	<td>
		<?php echo $categories['catid']; ?>
	</td>
</tr>
<tr>
	<td valign="top">
		<label for="jformurl">
			<?php echo JText::_( 'URL' ); ?>:
		</label>
	</td>
	<td>
		<input class="inputbox" type="text" id="jformurl" name="jform[url]" value="<?php echo $row->url; ?>" size="50" maxlength="250" />
	</td>
</tr>
<tr>
	<td valign="top">
		<label for="jformdescription">
			<?php echo JText::_( 'Description' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="inputbox" cols="30" rows="6" id="jformdescription" name="jform[description]" style="width:300px"><?php echo htmlspecialchars( $row->description, ENT_QUOTES );?></textarea>
	</td>
</tr>
</table>

<input type="hidden" name="jform[id]" value="<?php echo $row->id; ?>" />
<input type="hidden" name="jform[ordering]" value="<?php echo $row->ordering; ?>" />	
<input type="hidden" name="jform[approved]" value="<?php echo $row->approved; ?>" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="Returnid" value="<?php echo $Returnid; ?>" />
<input type="hidden" name="<?php echo JUtility::spoofKey(); ?>" value="1" />
</form>