<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="bg" xml:lang="bg">
<head>
	<title><?php echo $site_name; ?></title>
	<meta http-equiv="content-language" content="bg"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="Карта на престъпността в България генерирана от сигнали на обикновени граждани" />
	<meta name="keywords" content="престъпност, кражба, убийство, катастрофа, полиция, МВР, българия, crime, bulgaria" />
	<meta http-equiv="author" content="Boyan Yurukov" />
	<meta http-equiv="contact" content="yurukov@gmail.com" />

	<meta name="copyright" content="Creative Commons Attribution 2.5" />

	<link rel="image_src" type="image/jpeg" href="http://crime.bg/themes/default/images/big_logo.gif" />


		<?php echo $header_block; ?>
	<?php
	// Action::header_scripts - Additional Inline Scripts from Plugins
	Event::run('ushahidi_action.header_scripts');
	?>
</head>
<body id="page">
<div class="submit-incident"><a target="_blank" href="<?php echo url::site()."reports/submit"; ?>"><?php echo Kohana::lang('ui_main.submit');?></a></div>

<div id="logo">
	<h1><a href="<?php echo url::site();?>"><?php echo $site_name; ?></a></h1>
	<span><?php echo $site_tagline; ?></span>
</div>
