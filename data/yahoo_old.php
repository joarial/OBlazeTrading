<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type"
        content="text/html; charset=utf-8" />
  <meta name="robots"
        content="all" />
  <meta name="generator"
        content="RapidWeaver" />
  <meta name="generatorversion"
        content="3.5.1 (Build 264)" />

  <title>Data</title>
  <link rel="stylesheet"
        type="text/css"
        media="screen"
        href="../rw_common/themes/simplebusiness/styles.css" />
  <link rel="stylesheet"
        type="text/css"
        media="print"
        href="../rw_common/themes/simplebusiness/print.css" />
  <link rel="stylesheet"
        type="text/css"
        media="handheld"
        href="../rw_common/themes/simplebusiness/handheld.css" />
  <link rel="stylesheet"
        type="text/css"
        media="screen"
        href=
        "../rw_common/themes/simplebusiness/css/sidebar/sidebar_left.css" />

<script type="text/javascript"
      src="../rw_common/themes/simplebusiness/javascript.js">
</script>
</head>

<body>
  <div id="sidebarContainer">
    <!-- Start Sidebar wrapper -->

    <div id="navcontainer">
      <!-- Start Navigation -->

      <ul>
        <li><a href="../index.html"
           rel="self">Home Page</a></li>

        <li><a href="../blog/blog.html"
           rel="self">Blog</a></li>

        <li><a href="../data/data.php"
           rel="self">Data Management</a></li>

        <li><a href="../charting/charting.php"
           rel="self">Charting</a></li>

        <li><a href="../simulations/simulations.php"
           rel="self">Simulations</a></li>

        <li><a href="../signals/signals.php"
           rel="self">Signals</a></li>

        <li><a href="../links/links.html"
           rel="self">Links</a></li>
      </ul>
    </div><!-- End navigation -->

    <div class="sideHeader"></div><!-- Sidebar header -->

    <div id="sidebar">
    </div><!-- End sidebar content -->
  </div><!-- End sidebar wrapper -->

  <div id="container">
    <!-- Start container -->

    <div id="pageHeader">
      <!-- Start page header -->

      <h1>Financial Opportunities</h1>

      <h2>Best On Your Own</h2>
    </div><!-- End page header -->

    <div id="contentContainer">
      <!-- Start main content wrapper -->

      <div id="content">
        <!-- Start content -->
        <?php
        	ini_set('max_execution_time', 1200);
			require_once "../include/db.inc.php";
			require_once "../include/date_validate.inc.php";
			require_once "../include/dataArraysSQL.php";
			require_once "../include/class.Yahoo.php";
			require_once "../include/indicators.php";
			require_once "../include/class.progressbar.php";
			require_once "../include/class.Numerical.php";
			require_once "../include/yahooFunctions.inc.php";
			$fromDate = $_POST['ymd'];
	
			if ($fromDate)
			{
				$fDate = date_validate($fromDate);
				if (substr($fDate, 0, 5) == "Error")
				die ($fDate);
			}
			$ticker = $_POST['ticker'];
// Global Update
// read all tickers and update from the latest Date in database
			if ($_POST['globalUpdateDB'])
			{
				include("globalUpdateDB.php");
			}
// Reload quotes for a specific ticker	
			else if ($_POST['oneTickerReloadDB'])
			{
				include("oneTickerReloadDB.php");
			}
// Load fundamentals for all quotes 
			else if ($_POST['loadFundamentals'])
			{
				include("loadFundamentals.php");
			}
// Identify those ticker for whihc the data is not up to date.  Requires further manual checks 

			else if ($_POST['checkIntegrity'])
			{
				include("checkIntegrity.php");		
			}
// Recalculate the signals in the database, for instance when the algorithm changes
			else if ($_POST['recalculateSignals'])
			{
				include("recalculateSignals.php");		
			}
			else	
				die("Wrong action selected");
	
			echo "\n<br /><br /><br /><br />";
        ?>
      </div><!-- End content -->
    </div><!-- End main content wrapper -->

    <div class="clearer"></div>
	
    <br />
    <br />
    <br />
        
    <div id="breadcrumbcontainer">
      <!-- Start the breadcrumb wrapper -->

      <ul>
        <li><a href="../index.html">Home Page</a>&nbsp;/&nbsp;</li>

        <li><a href="yahoo.php">Yahoo Process</a>&nbsp;/&nbsp;</li>
      </ul>
    </div><!-- End breadcrumb -->

    <div id="footer">
      <!-- Start Footer -->

      <p>© 2007 <a href="mailto:invest@joaris.net">Alain Joaris</a>
      - © Data <a href="http://finance.yahoo.com"
         onclick="return popup(this,'finance.yahoo.com')"
         alt="finance.yahoo.com">Yahoo.com</a></p>
    </div><!-- End Footer -->
  </div><!-- End container -->
</body>
</html>
