<?php
$tabconfig = '';
$tabdefaults = '';
$tabedit = FALSE;

if(!empty($_GET['p'])){
	if($_GET['p']=='defaults')
		$tabdefaults = 'nav-tab-active';
	else if($_GET['p']=='edit')
		$tabedit = TRUE;
	else
		$tabconfig = 'nav-tab-active';	
		
}
else		
		$tabconfig = 'nav-tab-active';	
?>
<div id="icon-themes" class="icon32"><br /></div>
<h2>
<a class="nav-tab <?php echo $tabconfig; ?>" href="themes.php?page=customsidebars">Custom Sidebars</a>
<a class="nav-tab <?php echo $tabdefaults; ?>" href="themes.php?page=customsidebars&p=defaults">Default Sidebars</a>
<?php if($tabedit): ?>
<a class="nav-tab nav-tab-active" href="#">Edit Sidebar</a>
<?php endif; ?>
</h2>
<?php $this->message(); ?>	
