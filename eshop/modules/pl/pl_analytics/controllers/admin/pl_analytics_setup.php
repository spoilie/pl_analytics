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

class pl_analytics_setup extends oxAdminDetails
{
    /**
     * Current class template name
     *
     * @var string
     */
    protected $_sThisTemplate = "pl_analytics_setup.tpl";

    /**
     * saves instance of pl_analytics
     * @var marm_piwik
     */
    protected $_oPlAnalytics = null;

    /**
     * returns pl_analytics object
     * @param bool $blReset forde create new object
     * @return pl_analytics
     */
    public function getPlAnalytics($blReset = false)
    {
        if ($this->_oPlAnalytics !== null && !$blReset) {
            return $this->_oPlAnalytics;
        }
        $this->_oPlAnalytics = oxNew('pl_analytics');

        return $this->_oPlAnalytics;
    }

    /**
     * returns pl_analytics full config array
     * @return array
     */
    public function getConfigValues()
    {
        $oPlAnalytics = $this->getPlAnalytics();
        return $oPlAnalytics->getConfig();
    }

    /**
     * passes given parameters from 'editval' to pl_analytics change config
     * @return void
     */
    public function save()
    {
        $aParams = oxRegistry::getConfig()->getRequestParameter( "editval" );
        $oPlAnalytics = $this->getPlAnalytics();
        $oPlAnalytics->changeConfig($aParams);
    }
}