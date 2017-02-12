<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<meta name="robots" content="all" />
		<meta name="generator" content="RapidWeaver" />
		<meta name="generatorversion" content="3.6.1 (Build 1048)" />
		<title>Charts</title>
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/simplebusiness/styles.css"  />
		<link rel="stylesheet" type="text/css" media="print" href="../rw_common/themes/simplebusiness/print.css"  />
		<link rel="stylesheet" type="text/css" media="handheld" href="../rw_common/themes/simplebusiness/handheld.css"  />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/simplebusiness/css/sidebar/sidebar_left.css" />
		<script type="text/javascript" src="../rw_common/themes/simplebusiness/javascript.js"></script>
	</head>
<body>
<div id="sidebarContainer"><!-- Start Sidebar wrapper -->
	<div id="navcontainer"><!-- Start Navigation -->
		<ul><li><a href="../index.html" rel="self">Home Page</a></li><li><a href="../blog/blog.html" rel="self">Blog</a></li><li><a href="../data/data.php" rel="self">Data Management</a></li><li><a href="charting.php" rel="self" id="current">Charting</a></li><li><a href="../simulations/simulations.php" rel="self">Simulations</a></li><li><a href="../signals/signals.php" rel="self">Signals</a></li><li><a href="../links/links.html" rel="self">Links</a></li></ul>
	</div><!-- End navigation --> 
	<div id="sidebar"><!-- Start sidebar content -->
		<h1 class="sideHeader"></h1><!-- Sidebar header -->
		
<?php
	include "chartSideBar.php";
?>

 <br /><!-- sidebar content you enter in the page inspector -->
		 <!-- sidebar content such as the blog archive links -->
	</div><!-- End sidebar content -->
</div><!-- End sidebar wrapper -->
	
<div id="container"><!-- Start container -->	
	<div id="pageHeader"><!-- Start page header -->
		
		<h1>Financial Opportunities</h1>
		<h2>Best On Your Own</h2>
	</div><!-- End page header -->
	
	<div id="contentContainer"><!-- Start main content wrapper -->
		<div id="content"><!-- Start content -->

<?php
	include "selectIssuer.php";
?>

		</div><!-- End content -->
	</div><!-- End main content wrapper -->
	<div class="clearer"></div>
	<div id="breadcrumbcontainer"><!-- Start the breadcrumb wrapper -->
		<li><ul><a href="../index.html">Home Page</a>&nbsp;/&nbsp;</li><li><a href="charting.php">Charting</a>&nbsp;/&nbsp;</li></ul>
	</div><!-- End breadcrumb -->
	<div id="footer"><!-- Start Footer -->
		<p>&copy; 2007 <a href="mailto:invest@joaris.net">Alain Joaris</a> - &copy; Data <a href="http://finance.yahoo.com" onclick="return popup(this,'finance.yahoo.com')" alt="finance.yahoo.com">Yahoo.com</a></p>
	</div><!-- End Footer -->
</div><!-- End container -->
</body>
</html>
