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

        <li><a href="data.php"
           rel="self"
           id="current">Data Management</a></li>

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

        <br />

        <form action="yahoo.php"
              method="post">
          <u>Update database</u>
          <br />
          <br />
          using Yahoo data from the lastest date in the database up to last quote available on-line
          available <input type="submit"
                value="Global Update"
                name="globalUpdateDB" />
          <br />
          <br />
          using Yahoo starting from yyyy/mm/dd up to the last quote available on-line:
          <input type="text"
                name="ymd"
                size="10"
                maxlength="10" /> <input type="submit"
                value="Reload all from date"
                name="globalUpdateDB" />
          <br />
          <br />
          Reload all quotes from Yahoo data for a given ticker: <input type="text"
                name="ticker"
                size="10"
                maxlength="10" /> <input type="submit"
                value="Reload"
                name="oneTickerReloadDB" />
          <br />
          <br />
          <u>Fundamental data</u>
          <br />
          <br />
          Load fundamentals data for the current week from Yahoo
          <input type="submit"
                value="Load fundamentals"
                name="loadFundamentals" />
          <br />
          <br />
          <u>Data checks and processing</u>
          <br />
         <br />
          Check integrity of data (missing or changed ticker in Yahoo)
          <input type="submit"
                value="Check Integrity"
                name="checkIntegrity" />
          <br />
          <br />
          <br />
          Recalculate all signals for the database
          <input type="submit"
                value="Recalculate Signals"
                name="recalculateSignals" />
        </form>
      </div><!-- End content -->
    </div><!-- End main content wrapper -->

    <div class="clearer"></div>

    <div id="breadcrumbcontainer">
      <!-- Start the breadcrumb wrapper -->

      <ul>
        <li><a href="../index.html">Home Page</a>&nbsp;/&nbsp;</li>

        <li><a href="data.php">Data
        Management</a>&nbsp;/&nbsp;</li>
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
