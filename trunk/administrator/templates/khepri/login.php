<jdoc:comment>
@copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
@license GNU/GPL, see LICENSE.php
Joomla! is free software. This version may have been modified pursuant
to the GNU General Public License, and as distributed it includes or
is derivative of works licensed under the GNU General Public License or
other free or open source software licenses.
See COPYRIGHT.php for copyright notices and details.
</jdoc:comment>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LANG_TAG}" lang="{LANG_TAG}" dir="{LANG_DIR}" >
	<head>
	<jdoc:include type="head" />
	<jdoc:tmpl name="loadcss" varscope="document" type="condition" conditionvar="LANG_DIR">
		<jdoc:sub condition="rtl">
			<link href="templates/{TEMPLATE}/css/login_rtl.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
		<jdoc:sub condition="ltr">
			<link href="templates/{TEMPLATE}/css/login.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
	</jdoc:tmpl>

	<!--[if lte IE 6]>
  <link href="templates/{TEMPLATE}/css/ie.css" rel="stylesheet" type="text/css" />
  <![endif]-->

	<jdoc:tmpl name="useRoundedCorners" varscope="document" type="condition" conditionvar="PARAM_USEROUNDEDCORNERS">
		<jdoc:sub condition="0">
			<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/norounded.css" />
		</jdoc:sub >
		<jdoc:sub condition="1">
			<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/rounded.css" />
		</jdoc:sub >
	</jdoc:tmpl>

	<script language="javascript" type="text/javascript">
		function setFocus() {
			document.loginForm.username.select();
			document.loginForm.username.focus();
		}
	</script>
	</head>
	<body onload="javascript:setFocus()">
		<div id="border-top">
			<div>
				<div>
					<span class="title"><jdoc:translate>Administration</jdoc:translate></span>
				</div>
			</div>
		</div>
		<div id="content-box">
			<div class="padding">
        <div id="element-box" class="login">
    			<div class="t">
            <div class="t">
              <div class="t"></div>
            </div>
          </div>
          <div class="m">
						<h1><jdoc:translate>Joomla! Administration Login</jdoc:translate></h1>
            <div class="section-box">
        			<div class="t">
                <div class="t">
                  <div class="t"></div>
                </div>
              </div>
              <div class="m">
								<jdoc:include type="module" name="login" />
							</div>
              <div class="b">
                <div class="b">
                  <div class="b"></div>
                </div>
              </div>
            </div>
						<p><jdoc:translate>DESCUSEVALIDLOGIN</jdoc:translate></p>

						<p>
							<a href="<?php echo $mainframe->getSiteURL(); ?>"><jdoc:translate>Return to site Home Page</jdoc:translate></a>
						</p>
						<div id="lock"></div>
						<div class="clr"></div>

          </div>
          <div class="b">
            <div class="b">
              <div class="b"></div>
            </div>
          </div>
        </div>

				<noscript>
					<jdoc:translate key="WARNJAVASCRIPT" />
				</noscript>
				<div class="clr"></div>
			</div>
		</div>
		<div id="border-bottom"><div><div></div></div>
		</div>

		<div id="footer">
			<p class="copyright">
				<a href="http://www.joomla.org" target="_blank"><jdoc:translate>Joomla!</jdoc:translate></a>
				<jdoc:translate key="ISFREESOFTWARE">is Free Software released under the GNU/GPL License.</jdoc:translate>
			</p>
		</div>
	</body>
</html>
