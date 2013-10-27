<?php
/**
 * Google Analytics integration in OXID
 *
 * Copyright (c) 2013 Paul Lamp | paul-lamp.de
 * E-mail: pl@paul-lamp.de
 * http://www.paul-lamp.de
 * 
 * based on   
 * Piwik integration in OXID
 *
 * Copyright (c) 2011 Joscha Krug | marmalade.de
 * E-mail: mail@marmalade.de
 * http://www.marmalade.de
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

class pl_analytics_oxoutput extends pl_analytics_oxoutput_parent
{
    /**
     * appends Google Analytics javascript source before body tag
     * if user logged in is Administrator, no tracking will be enabled
     * TODO: Add config for tracking if Admin          
     * @param $sOutput
     * @return mixed
     */
    public function plReplaceBody( $sOutput )
    {
        $blAdminUser=false;
        $oUser=$this->getUser();
        if ($oUser) $blAdminUser=$oUser->inGroup("oxidadmin");
        if(!isAdmin()&&!$blAdminUser) {
            $oPlAnalytics = oxNew('pl_analytics');
            if($oPlAnalytics->getGaUaId() != "UA-XXXXXXXX-X") {
              $sGaCode = $oPlAnalytics->getPlAnalyticsCode();
              $sOutput = str_ireplace("</body>", "{$sGaCode}\n</body>", ltrim($sOutput));
            }
        }
        return $sOutput;
    }

    /**
     * returns $sValue filtered by parent and pl_analytics_oxoutput::plReplaceBody
     * @param $sValue
     * @param $sClassName
     * @return mixed
     */
    public function process($sValue, $sClassName)
    {
        $sValue = parent::process($sValue, $sClassName);
        $sValue = $this->plReplaceBody( $sValue);
        return $sValue;
    }
}