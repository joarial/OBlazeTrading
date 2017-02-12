<?php
// Class Datagrid V 1.0
// Created by DejiTaru
// 20 september 2005
// email: dejitaru@gmail.com
// Email me please!!
//
class classDatagrid
{
	var $DG_query;
    var $DG_primary_key;
    var $DG_page_size;
    var $dataGrid;
    var $DG_tools;
    var $DG_pager;
    var $DG_custom_col_name;
    var $DG_custom_col_type;

    function classDatagrid()
    {
    	$this->dataGrid="";
        $this->DG_page_size=1000;
    }
    function set_query($pSQLquery,$pPrimaryKey)
    {
    	$this->DG_query=$pSQLquery;
        $this->DG_primary_key=$pPrimaryKey;
    }
    function set_col_name($dbField,$colName)
    {
    	$this->DG_custom_col_name[$dbField]=$colName;
    }
    function set_col_type($dbField,$colType)
    {
    	$this->DG_custom_col_type[$dbField]=$colType;
    }
    function set_tools($pTools)
    {
    }
    function set_page_size($pPageSize)
    {
    	$this->DG_page_size=$pPageSize;
    	$this->build_pager();
    }
    
    function build_pager()
    {
    	$this->DG_pager="";
        $totalRows=mysql_num_rows(mysql_query($this->DG_query));
        for($i=1;$i<=($totalRows/$this->DG_page_size)+1;$i++)
        	$this->DG_pager.="<a href='". $_SERVER['PHP_SELF']."?pg=$i' class='dgPager'>[".$i."]</a> ";
    }
    
    function display()
    {
    	if (!isset($_GET['pg']))
    		$SQLresult=mysql_query($this->DG_query." LIMIT 0,".$this->DG_page_size);
        else
        {
        	$a=($_GET['pg']-1)*$this->DG_page_size;
            $b=$this->DG_page_size;
            $SQLresult=mysql_query($this->DG_query." LIMIT $a,$b");
        }
        $this->dataGrid.="<table class='dgTable'><tr class='dgHeader'>";
        $this->dataGrid.="<td></td>";
        // Header
        for ($i=0;$i<mysql_num_fields($SQLresult);$i++)
        {
        	if (!isset($this->DG_custom_col_name[mysql_field_name($SQLresult,$i)]))
            	$this->dataGrid.="<td>".mysql_field_name($SQLresult,$i)."</td>";
            else
                $this->dataGrid.="<td>".$this->DG_custom_col_name[mysql_field_name($SQLresult,$i)]."</td>";
        }
        $this->dataGrid.="</tr>";
        $rowCount=0;
        // Data
        while($row=mysql_fetch_array($SQLresult))
        {
        	$rowClass=($rowCount % 2) ? "dgParRow" : "dgImparRow";$rowCount++;
            $this->dataGrid.="<tr class='$rowClass'>";
            $this->dataGrid.="<td><input name='checkbox' type='checkbox' value='checkbox'></td>";
            for ($i=0;$i<mysql_num_fields($SQLresult);$i++)
            {
            	if (!isset($this->DG_custom_col_type[mysql_field_name($SQLresult,$i)]))
                	$this->dataGrid.="<td>".$row[$i]."</td>";
                else
                    switch ($this->DG_custom_col_type[mysql_field_name($SQLresult,$i)])
                    {
                    	case "IMG":
                    		$this->dataGrid.="<td><img src='".$row[$i]."'></td>";
                            break;
                        case "BOOL":
                            break;
                        default:
                            $this->dataGrid.="<td>IMAGEN".$row[$i]."</td>";
                            break;
                    }
            }
            $this->dataGrid.="</tr>";
        }
        $this->dataGrid.="</table><tr>";
        echo $this->dataGrid.$this->DG_pager;
    }
}
?>