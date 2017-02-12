<?php

//
// Edit History:
//
//  $Author: munroe $
//  $Date: 2006/01/06 14:57:57 $
//
//  Dick Munroe (munroe@csworks.com) 04-Jan-2006
//      Initial version created.
//
//  Dick Munroe (munroe@csworks.com) 04-Jan-2006
//      Add random variable generation using the Box-Mueller transformations.
//
//  Dick Munroe (munroe@csworks.com) 05-Jan-2006
//      Add a mean, median, and mode functions.
//
//  Dick Munroe (munroe@csworks.com) 06-Jan-2006
//      Add standard deviation and variance functions.
//
//  Dick Munroe (munroe@csworks.com) 19-Jan-2006
//      Improve the numerical accuracy and performance of the variance and
//      standard deviation functions (suggested by Martin Weis).
//

/**
 * @author Dick Munroe <munroe@csworks.com>
 * @copyright copyright @ 2006 by Dick Munroe, Cottage Software Works, Inc.
 * @license http://www.csworks.com/publications/ModifiedNetBSD.html
 * @version 1.0.3
 * @package Numerical
 * @example ./example.php
 */

/*
 * Provide a number of numerical analysis and statistical functions.
 */
        
class Numerical
{
    /*
     * Caluclate the integral of a function over a specified range.
     * 
     * Uses the midpoint method of calculation for integral.
     * The function passed in must take one parameter and return the
     * value of the function for that parameter.
     *
     * @access public
     * @param function $theFunction the function to be integrated.
     * @param float $theLowLimit The lower limit of the integral
     * @param float $theHighLimit The high limit of the integral
     * @param integer $theNumberOfSteps The number of steps take to evaluate the integral, defaults to 100.
     * @return float The value of the integral for the specified range.
     */
    
    function integrate($theFunction, $theLowLimit, $theHighLimit, $theNumberOfSteps = 100)
    {
        if ($theLowLimit >= $theHighLimit)
        {
            die("The limits are out of order") ;
        }
        
        $theDelta = ($theHighLimit - $theLowLimit) / $theNumberOfSteps ;
        $theMidpointDelta = $theDelta / 2 ;
        
        $theArea = 0.0 ;

        for ($i = 0; $i < $theNumberOfSteps; $i++)
        {
            $theValue = $theLowLimit + (($i * $theDelta) + $theMidpointDelta) ;
            $theValue = $theFunction($theValue) ;
            $theArea += $theValue * $theDelta ;
        }
        
        return $theArea ;
    }
    
    /**
     * Generate a Gaussian normal distribution with a specified mean and standard
     * deviation.  The default distribution generated is the standard
     * normal distribution with a mean of 0.0 and a standard deviation of
     * 1.0.
     *
     * @desc Draw a value from a normal distribution.
     * @access public
     * @param float $x The parameter of the function.
     * @param float $theMean The mean of the distribution, by default 0.0.
     * @param float $theStandardDeviation The standard deviation of the distribution, by default, 1.0.
     * @return float the probability of the x occuring in the distribution.
     */
    
    function gaussian($x, $theMean = 0.0, $theStandardDeviation = 1.0)
    {
        $part1 = (1/($theStandardDeviation * sqrt(2 * M_PI))) ;
        $part2 = exp(- pow(($x - $theMean), 2) / (2 * pow($theStandardDeviation, 2))) ;
        return $part1 * $part2 ;
    }
    
    /*
     * @desc Calculate the mean of an array of samples.
     * @access public
     * @param array $theSamples An array of samples.
     * @return float the mean of the samples.
     */
    
    function mean($theSamples)
    {
        return array_sum($theSamples) / count($theSamples) ;
    }
    
    /*
     * @desc Determine the median of a sample.
     * @access public
     * @param array $theSamples An array of samples.
     * @return float the mean of the samples.
     */
    
    function median($theSamples)
    {
        sort($theSamples) ;
        
        if ((count($theSamples) % 2) == 0)
        {
            $i = count($theSamples) / 2 ;
            return ($theSamples[$i - 1] + $theSamples[$i]) / 2 ;
        }
        else
        {
            return $theSamples[(int) (count($theSamples) / 2)] ;
        }
    }
    
    /*
     * @desc Determine the mode[s] of a sample.
     * @access public
     * @param array $theSamples An array of samples.
     * @return mixed the mode[s] of the sample.
     */
    
    function mode($theSamples)
    {
        $theCounts = array() ;
        
        $theCount = count($theSamples) ;
        
        for ($i = 0; $i < $theCount; $i++)
        {
            $theCounts[(string)$theSamples[$i]]++ ;
        }
        
        arsort($theCounts) ;
        
        $theModes = array() ;
        
        foreach ($theCounts as $key => $count)
        {
            if (count($theModes) == 0)
            {
                $theModeCount = $count ;
            }

            if ($theModeCount == $count)
            {
                $theModes[] = $key ;
            }
            else
            {
                break ;
            }
        }
        
        return $theModes ;
    }
    
    /*
     * Note that this algorithm can be unstable for uniformly distributed random numbers
     * close to 0.0.
     * 
     * @desc Generate gaussian distributed random numbers using the Box-Mueller basic transform.
     * @param float $theMean The mean of the distribution.
     * @param float $theStandardDeviation The standard deviation of the distribution.
     * @return float A random number.
     */

    function randomGaussianBoxMuellerBasic($theMean = 0.0, $theStandardDeviation = 1.0)
    {
        static $useLast = FALSE ;
        static $y2 ;
        
        if ($useLast)
        {
            $useLast = FALSE ;
            $y1 = $y2 ;
        }
        else
        {
            /*
             * Generate a uniformly distributed random number in the range
             * (0, 1].
             */
            
            $theRange = 10000000 ;
            
            $x1 = mt_rand(1, $theRange) / $theRange ;
            $x2 = mt_rand(1, $theRange) / $theRange ;
            
            /*
             * Convert this to a pair of gaussian distributed random numbers.
             * The 2nd is returned on the next call.
             */
            
            $x = sqrt( -2 * log($x1) ) ;
            
            $y1 = $x * sin( 2 * M_PI * $x2 ) ;
            $y2 = $x * cos( 2 * M_PI * $x2 ) ;
            
            $useLast = TRUE ;
        }
        
        return $theMean + $y1 * $theStandardDeviation ;
    }
    
    /*
     * @desc Generate gaussian distributed random numbers using the Box-Mueller Polar transform.
     * @param float $theMean The mean of the distribution.
     * @param float $theStandardDeviation The standard deviation of the distribution.
     * @return float A random number.
     */

    function randomGaussianBoxMuellerPolar($theMean = 0.0, $theStandardDeviation = 1.0)
    {
        static $useLast = FALSE ;
        static $y2 ;
        
        if ($useLast)
        {
            $useLast = FALSE ;
            $y1 = $y2 ;
        }
        else
        {
            do
            {
                /*
                 * Generate a pair of uniformly distributed random numbers
                 * in the range [-1..1].
                 */
                
                $theRange = 10000000 ;
            
                $x1 = mt_rand(- $theRange, $theRange) / $theRange ;
                $x2 = mt_rand(- $theRange, $theRange) / $theRange ;
                
                $w = $x1 * $x1 + $x2 * $x2 ;
            } while ($w >= 1.0) ;
            
            $w = sqrt ( (-2 * log( $w ) ) / $w ) ;
            $y1 = $x1 * $w ;
            $y2 = $x2 * $w ;
            
            $useLast = TRUE ;
        }
        
        return $theMean + $y1 * $theStandardDeviation ;
    }
    
    /*
     * @desc Determine the standard deviation of a sample.
     * @access public
     * @param array $theSamples An array of samples.
     * @return float the standard deviation.
     */
    
    function standardDeviation($theSamples)
    {
        return sqrt(Numerical::variance($theSamples)) ;
    }
    
    /*
     * @desc Determine the variance of a sample.
     * @access public
     * @param array $theSamples An array of samples.
     * @return float the variance.
     */
    
    function variance($theSamples)
    {
        $theMean = Numerical::mean($theSamples) ;
        $theSum = 0.0 ;
        foreach ($theSamples as $theValue)
        {
            $theSum += pow(($theValue - $theMean), 2) ;
        }
        return @ ($theSum/(count($theSamples) - 1)) ;
     }
}

?>